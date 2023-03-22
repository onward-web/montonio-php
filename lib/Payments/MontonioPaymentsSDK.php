<?php

namespace Montonio\Payments;
/**
 * We use php-jwt for JWT creation
 */
use Firebase\JWT\JWT;


/**
 * SDK for Montonio Payments.
 * This class contains methods for starting and validating payments.
 */
class MontonioPaymentsSDK
{

    /**
     * Payment Data for Montonio Payment Token generation
     * @see https://payments-docs.montonio.com/#generating-the-payment-token
     *
     * @var array
     */
    protected $_paymentData;

    /**
     * Montonio Access Key
     *
     * @var string
     */
    protected $_accessKey;

    /**
     * Montonio Secret Key
     *
     * @var string
     */
    protected $_secretKey;

    /**
     * Montonio Environment (Use sandbox for testing purposes)
     *
     * @var string 'production' or 'sandbox'
     */
    protected $_environment;

    /**
     * Root URL for the Montonio Payments Sandbox application
     */
    const MONTONIO_PAYMENTS_SANDBOX_APPLICATION_URL = 'https://sandbox-payments.montonio.com';

    /**
     * Root URL for the Montonio Payments application
     */
    const MONTONIO_PAYMENTS_APPLICATION_URL = 'https://payments.montonio.com';

    public function __construct($accessKey, $secretKey, $environment)
    {
        $this->_accessKey   = $accessKey;
        $this->_secretKey   = $secretKey;
        $this->_environment = $environment;
    }

    /**
     * Get the URL string where to redirect the customer to
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        $base = ($this->_environment === 'sandbox')
        ? self::MONTONIO_PAYMENTS_SANDBOX_APPLICATION_URL
        : self::MONTONIO_PAYMENTS_APPLICATION_URL;

        return $base . '?payment_token=' . $this->_generatePaymentToken();
    }

    /**
     * Generate JWT from Payment Data
     *
     * @return string
     */
    protected function _generatePaymentToken()
    {
        /**
         * Parse Payment Data to correct data types
         * and add additional data
         */
        $paymentData = array(
            'amount'                => (float) $this->_paymentData['amount'],
            'access_key'            => (string) $this->_accessKey,
            'currency'              => (string) $this->_paymentData['currency'],
            'merchant_name'         => (string) $this->_paymentData['merchant_name'],
            'merchant_reference'    => (string) $this->_paymentData['merchant_reference'],
            'merchant_return_url'   => (string) $this->_paymentData['merchant_return_url'],
            'checkout_email'        => (string) $this->_paymentData['checkout_email'],
            'checkout_first_name'   => (string) $this->_paymentData['checkout_first_name'],
            'checkout_last_name'    => (string) $this->_paymentData['checkout_last_name'],
            'checkout_phone_number' => (string) $this->_paymentData['checkout_phone_number'],
        );

        if (isset($this->_paymentData['merchant_notification_url'])) {
            $paymentData['merchant_notification_url'] = (string) $this->_paymentData['merchant_notification_url'];
        }

        if (isset($this->_paymentData['preselected_aspsp'])) {
            $paymentData['preselected_aspsp'] = (string) $this->_paymentData['preselected_aspsp'];
        }

        if (isset($this->_paymentData['preselected_locale'])) {
            $paymentData['preselected_locale'] = (string) $this->_paymentData['preselected_locale'];
        }

        if (isset($this->_paymentData['preselected_country'])) {
            $paymentData['preselected_country'] = (string) $this->_paymentData['preselected_country'];
        }

        foreach ($paymentData as $key => $value) {
            if (empty($value)) {
                unset($paymentData[$key]);
            }
        }

        // add expiry to payment data for JWT validation
        $exp                = time() + (10 * 60);
        $paymentData['exp'] = $exp;

        return Firebase\JWT\JWT::encode($paymentData, $this->_secretKey);
    }

    /**
     * Set payment data
     *
     * @param array $paymentData
     * @return MontonioPaymentsSDK
     */
    public function setPaymentData($paymentData)
    {
        $this->_paymentData = $paymentData;
        return $this;
    }

    /**
     * Decode the Payment Token
     * This is used to validate the integrity of a callback when a payment was made via Montonio
     * @see https://payments-docs.montonio.com/#validating-the-returned-payment-token
     *
     * @param string $token - The Payment Token
     * @param string Your Secret Key for the environment
     * @return object The decoded Payment token
     */
    public static function decodePaymentToken($token, $secretKey)
    {
        Firebase\JWT\JWT::$leeway = 60 * 5; // 5 minutes
        return Firebase\JWT\JWT::decode($token, $secretKey, array('HS256'));
    }

    /**
     * Get the Bearer auth token for requests to Montonio
     *
     * @param string $accessKey - Your Access Key
     * @param string $secretKey - Your Secret Key
     * @return string
     */
    static function getBearerToken($accessKey, $secretKey)
    {
        $data = array(
            'access_key' => $accessKey,
        );

        return Firebase\JWT\JWT::encode($data, $secretKey);
    }

    /**
     * Function for making API calls with file_get_contents
     *
     * @param string URL
     * @param array Context Options
     * @return array Array containing status and json_decoded response
     */
    protected function _apiRequest($url, $options)
    {
        $context = stream_context_create($options);
        $result  = @file_get_contents($url, false, $context);

        if ($result === false) {
            return array(
                "status" => "ERROR",
                "data"   => $result,
            );
        } else {
            return array(
                "status" => "SUCCESS",
                "data"   => json_decode($result),
            );
        }
    }

    /**
     * Fetch info about banks and card processors that
     * can be shown to the customer at checkout.
     *
     * Banks have different identifiers for separate regions,
     * but the identifier for card payments is uppercase CARD
     * in all regions.
     * @see MontonioPaymentsCheckout::$bankList
     *
     * @return array Array containing the status of the request and the banklist
     */
    public function fetchBankList()
    {
        $url = $this->_environment === 'sandbox'
        ? 'https://api.sandbox-payments.montonio.com/pis/v2/merchants/aspsps'
        : 'https://api.payments.montonio.com/pis/v2/merchants/aspsps';

        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json\r\n" .
                "Authorization: Bearer " . MontonioPaymentsSDK::getBearerToken(
                    $this->_accessKey,
                    $this->_secretKey
                ) . "\r\n",
                'method' => 'GET',
            ),
        );
        return $this->_apiRequest($url, $options);
    }
}
