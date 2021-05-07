# montonio-php
PHP library for the Montonio API

Here you can find some code which hopefully makes integrating with [Montonio](https://montonio.com) a bit easier. For the full documentation on how all these blocks work together, have a look at our documentation, which can be found here:

[Montonio Payments](https://payments-docs.montonio.com)

[Montonio Financing](https://developer.montonio.com)

> If you are using a popular eCommerce platform such as WooCommerce, have a look at our [integrations](https://montonio.com/integrations) section to see if we already have a module ready for you.

## Montonio Payments

### Fetching the list of banks and credit card processors
```php
    $accessKey = 'your_access_key';
    $secretKey = 'your_secret_key';
    $env = 'sandbox'; // or 'production'

    require_once 'lib/MontonioPayments/MontonioPaymentsSDK.php';
    require_once 'lib/MontonioPayments/MontonioPaymentsCheckout.php';

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
    'currency'                  => 'EUR', // currently only EUR is supported
    'merchant_reference'        => 'my-order-id-1', // the id you can identify the order with
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
    'preselected_locale'        => 'en' // see available locale options in the docs
);

$sdk->setPaymentData($paymentData);
$paymentUrl = $sdk->getPaymentUrl();

// The payment URL customer should be redirected to
echo $paymentUrl;
```
