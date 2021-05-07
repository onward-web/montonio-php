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

    // ...
?>
```

### Showing the list of banks at checkout
```html
<link type="text/css" rel="stylesheet" href="lib/MontonioPayments/assets/css/grid_logos.css">
<script src="lib/MontonioPayments/assets/js/montonio-payment-handle.js"></script>
```
```php
// *** @var $banklist ***
$checkout = new MontonioPaymentsCheckout();
$checkout->set_description('Pay with your bank');
$checkout->set_preferred_country('EE');
$checkout->set_payment_handle_style('grid_logos');
$checkout->set_banklist($banklist);

$html = $checkout->get_description_html();

echo $html;
```
