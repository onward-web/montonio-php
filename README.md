# montonio-php
PHP library for the Montonio API

Here you can find some code which hopefully makes integrating with [Montonio](https://montonio.com) a bit easier. For the full documentation on how all these blocks work together, have a look at our documentation, which can be found here:

[Montonio Payments](https://payments-docs.montonio.com)

[Montonio Financing](https://developer.montonio.com)

> If you are using a popular eCommerce platform such as WooCommerce, have a look at our [integrations](https://montonio.com/integrations) section to see if we already have a module ready for you.

## Montonio Payments

### Fetching the list of banks and credit card processors
```php
    require_once 'lib/MontonioPayments/MontonioPaymentsSDK.php';

    $accessKey = 'your_access_key';
    $secretKey = 'your_secret_key';
    $env = 'sandbox'; // or 'production'

    $sdk = new MontonioPaymentsSDK(
        $accessKey,
        $secretKey,
        $env
    );

    $banklistResponse = $sdk->fetchBankList();

    // Save banklist in DB ... 
    $banklist = json_encode($banklistResponse['data']);

?>
```

### Showing the list of banks at checkout
```html
<link type="text/css" rel="stylesheet" href="lib/MontonioPayments/assets/css/grid_logos.css">
<script src="lib/MontonioPayments/assets/js/montonio-payment-handle.js"></script>
```
```php
/**
 * Please have the $banklist fetched from 
 * MontonioPaymentsSDK::fetchBankList() beforehand and saved/cached to 
 * your database.
 * Please do not fetch the banklist every time you load the checkout.
 */
// *** @var $banklist ***

require_once 'lib/MontonioPayments/MontonioPaymentsCheckout.php';

$checkout = new MontonioPaymentsCheckout();
$checkout->set_description('Pay with your bank');
$checkout->set_preferred_country('EE');
$checkout->set_payment_handle_style('grid_logos');
$checkout->set_banklist($banklist);

$html = $checkout->get_description_html();

echo $html;
```

### Starting the payment
```php
require_once 'lib/MontonioPayments/MontonioPaymentsSDK.php';

$accessKey = 'your_access_key';
$secretKey = 'your_secret_key';
$env = 'sandbox'; // or 'production'

$sdk = new MontonioPaymentsSDK(
    $accessKey,
    $secretKey,
    $env
);

$paymentData = array(
    'amount'                    => 5.00, // Make sure this is a float
    'currency'                  => 'EUR', // Currently only EUR is supported
    'merchant_reference'        => 'my-order-id-1', // The order id in your system
    'merchant_name'             => 'Some Company OÜ',
    'checkout_email'            => 'test@montonio.com',
    'checkout_first_name'       => 'Montonio',
    'checkout_last_name'        => 'Test',
    'checkout_phone_number'     => '55555555',
    'merchant_notification_url' => 'https://my-store/notify', // We will send a webhook after the payment is complete
    'merchant_return_url'       => 'https://my-store/return', // Where to redirect the customer to after the payment
    'preselected_country'       => 'EE',
    'preselected_aspsp'         => 'LHVBEE22', // The preselected ASPSP identifier
    // For card payments:
    // 'preselected_aspsp'         => 'CARD'
    'preselected_locale'        => 'en' // See available locale options in the docs
);

$sdk->setPaymentData($paymentData);
$paymentUrl = $sdk->getPaymentUrl();

// The payment URL customer should be redirected to
echo $paymentUrl;
```

### Validating the payment
If the response does not have the ```payment_token``` query parameter, then the payment did not reach a verdict.
This can happen if the user simply closed the payment application and returned to cart.

```php
require_once 'lib/MontonioPayments/MontonioPaymentsSDK.php';

// original order ID passed to merchant_reference
$orderID = 'my-order-id-1';

// We send the payment_token query parameter upon successful payment
// This is both with merchant_notification_url and merchant_return_url
$token     = $_REQUEST['payment_token'];
$secretKey = 'your_secret_key';

$decoded = MontonioPaymentsSDK::decodePaymentToken($token, $secretKey);

if (
    $decoded->access_key === 'merchant_access_key' &&
    $decoded->merchant_reference === $orderID &&
    $decoded->status === 'finalized'
) {
    // Payment completed
} else {
    // Payment not completed
}
```

## Montonio Financing

### Starting the financing application
```php
require_once 'lib/MontonioFinancing/MontonioFinancingSDK.php';

$accessKey = 'your_access_key';
$secretKey = 'your_environment';
$env       = 'sandbox'; // or 'production'

$montonioFinancing = new MontonioFinancingSDK(
    $accessKey,
    $secretKey,
    $env
);

$paymentData = array(
    'origin'                    => 'online',
    'access_key'                => $accessKey,
    'currency'                  => 'EUR',
    'merchant_name'             => 'My Store OÜ',
    'merchant_reference'        => 'my-order-id-1',
    'checkout_first_name'       => 'Montonio',
    'checkout_last_name'        => 'Test',
    'checkout_email'            => 'test@montonio.com',
    'checkout_city'             => 'Tallinn',
    'checkout_address'          => 'Customer Address',
    'checkout_postal_code'      => '11111',
    'checkout_phone_number'     => '+37255555555',
    'merchant_return_url'       => 'https://my-store/return', // Where to redirect the checkout to after the payment
    'merchant_notification_url' => 'https://my-store/notify', // We will send a webhook after the payment is complete,
    'preselected_loan_period'   => 12, // Optional
    'products'                  => array(),
    'preselected_locale'        => 'et'
);

// Add products
$paymentData['checkout_products'][] = array(
    'quantity'      => (int) 1,
    'product_name'  => 'Some product name',
    'product_price' => 35.52,
);

$montonioFinancing->setPaymentData($paymentData);
$paymentUrl = $montonioFinancing->getPaymentUrl();

echo $paymentUrl;
```

### Validating the financing application

```php
require_once 'lib/MontonioPayments/MontonioFinancingSDK.php';

$secretKey = 'your_secret_key';

// original order ID passed to merchant_reference
$orderID = 'my-order-id-1';

// We send the payment_token query parameter upon successful payment
// This is both with merchant_notification_url and merchant_return_url
$token     = $_REQUEST['payment_token'];
$secretKey = 'your_secret_key';

$decoded = MontonioFinancingSDK::decodePaymentToken($token, $secretKey);

if (
    $decoded->access_key === 'merchant_access_key' &&
    $decoded->merchant_reference === $orderID &&
    $decoded->status === 'finalized'
) {
    // Payment completed
} else {
    // Payment not completed
}
```


## [DEPRECATED] Montonio Financing V1
Please see the section above to integrate with Montonio Financing. This code here is to preserve examples for pre-existing integrations.

### [Deprecated] Starting the loan application
```php
require_once 'lib/MontonioFinancing/MontonioFinancingSDK.php';

$accessKey = 'your_access_key';
$secretKey = 'your_secret_key';
$env       = 'sandbox'; // or 'production'

$montonioFinancing = new MontonioFinancingSDK(
    $accessKey,
    $secretKey,
    $env
);

// Prepare data for loan application
$data = array(
    'origin'               => 'online',
    'merchant_reference'   => 'my-order-id-2', // The Order Id in your system
    'customer_first_name'  => 'Montonio',
    'customer_last_name'   => 'Test',
    'customer_email'       => 'test@montonio.com',
    'customer_city'        => 'Tallinn',
    'customer_address'     => 'Customer Address',
    'customer_postal_code' => '11111',
    'products'             => array(),
    'notification_url'     => 'https://my-store/notify', // We will send a webhook after the payment is complete
    'callback_url'         => 'https://my-store/return', // Where to redirect the customer to after the payment
);

// Add products
$data['products'][] = array(
    'quantity'      => (int) 1,
    'product_name'  => 'Some product name',
    'product_price' => 35.52,
);

// Get application draft
$draftResult = $montonioFinancing->post_montonio_application_draft(json_encode($data));

// handle draft request response
$accessToken = ($draftResult['status'] === 'SUCCESS') ? $draftResult['data']->access_token : null;

// Redirect to Montonio Financing
$baseUrl = $env === 'sandbox' ? 'https://sandbox-application.montonio.com' : 'https://application.montonio.com';

echo $baseUrl . '?access_token=' . $accessToken;
```

### [Deprecated] Validating the loan application
```php
require_once 'lib/MontonioFinancing/MontonioFinancingSDK.php';

// original order ID passed to merchant_reference
$orderID = 'my-order-id-2';

$accessKey = 'your_access_key';
$secretKey = 'your_secret_key';
$env       = 'sandbox'; // or 'production'

$montonioFinancing = new MontonioFinancingSDK(
    $accessKey,
    $secretKey,
    $env
);

$request = $montonioFinancing->get_montonio_application($orderID);

if ($request['status'] === 'SUCCESS') {

    $response = $request['data'];

    if ($response->status === 'signed') {
        // Payment completed
    } else {
        // Payment not completed
    }
}
```
