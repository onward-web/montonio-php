# montonio-php
PHP library for the Montonio API
> The official montonio-php package has a very outdated development approach. Therefore, we have created a fork that will help you use montonio in more modern php frameworks and cms


## Install

Add to composer.json

```json
	"repositories": [
		...
		{
            "type": "vcs",
            "url":  "https://github.com/onward-web/montonio-php.git"
        },
		...
	],
```
Then install the package
>composer require montonio/montonio-php "dev-onward"



## Montonio Payments

### Fetching the List of Banks and Credit Card Processors
```php
    use Montonio\Payments\MontonioPaymentsSDK;    

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

### Showing the List of Banks at Checkout
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
use Montonio\Payments\MontonioPaymentsCheckout;

$checkout = new MontonioPaymentsCheckout();
$checkout->set_description('Pay with your bank');
$checkout->set_preferred_country('EE');
$checkout->set_payment_handle_style('grid_logos');
$checkout->set_banklist($banklist);

$html = $checkout->get_description_html();

echo $html;
```

### Starting the Payment
```php
use Montonio\Payments\MontonioPaymentsSDK;

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

### Validating the Payment
If the response does not have the ```payment_token``` query parameter, then the payment did not reach a verdict.
This can happen if the user simply closed the payment application and returned to cart.

```php
use Montonio\Payments\MontonioPaymentsSDK;

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

## Montonio Split

### Starting the Split Application
```php
use Montonio\Split\MontonioSplitSDK;

$accessKey = 'your_access_key';
$secretKey = 'your_secret_key';
$env       = 'sandbox'; // or 'production'

$montonioSplit = new MontonioSplitSDK(
    $accessKey,
    $secretKey,
    $env
);

$paymentData = array(
    'origin'                    => 'online',
    'loan_type'                 => 'slice',
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
    'preselected_loan_period'   => 3, // Optional [values 1, 2 or 3]
    'checkout_products'         => array(),
    'preselected_locale'        => 'et'
);

// Add products
$paymentData['checkout_products'][] = array(
    'quantity'      => (int) 1,
    'product_name'  => 'Some product name',
    'product_price' => 35.52,
);

$montonioSplit->setPaymentData($paymentData);
$paymentUrl = $montonioSplit->getPaymentUrl();
```

### Validating the Split Application

```php
use Montonio\Split\MontonioSplitSDK;

// original order ID passed to merchant_reference
$orderID = 'my-order-id-1';

// We send the payment_token query parameter upon successful payment
// This is both with merchant_notification_url and merchant_return_url
$token     = $_REQUEST['payment_token'];
$secretKey = 'your_secret_key';

$decoded = MontonioSplitSDK::decodePaymentToken($token, $secretKey);

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

### Starting the Financing Application
```php
use Montonio\Financing\MontonioFinancingSDK;

$accessKey = 'your_access_key';
$secretKey = 'your_secret_key';
$env       = 'sandbox'; // or 'production'

$montonioFinancing = new MontonioFinancingSDK(
    $accessKey,
    $secretKey,
    $env
);

$paymentData = array(
    'origin'                    => 'online',
    'loan_type'                 => 'hire_purchase',
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

### Validating the Financing Application

```php
use Montonio\Financing\MontonioFinancingSDK;

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