<?php

/**
 * We use php-jwt for JWT creation
 */
require_once 'jwt/JWT.php';

/**
 * Process order data after place order clicked
 */
class MontonioSplitSDK
{

    /**
     * Payment Data for Montonio Payment Token generation
     *
     * @var array
     */
    protected $_paymentData;

    protected $_accessKey;
    protected $_secretKey;
    protected $_environment;

    const MONTONIO_SPLIT_SANDBOX_APPLICATION_URL = 'https://sandbox-financing.montonio.com';

    const MONTONIO_SPLIT_APPLICATION_URL = 'https://financing.montonio.com';

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
        ? self::MONTONIO_SPLIT_SANDBOX_APPLICATION_URL
        : self::MONTONIO_SPLIT_APPLICATION_URL;

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
            'origin'                  => 'online',
            'access_key'              => (string) $this->_accessKey,
            'loan_type'               => 'slice',
            'currency'                => (string) $this->_paymentData['currency'],
            'merchant_name'           => (string) $this->_paymentData['merchant_name'],
            'merchant_reference'      => (string) $this->_paymentData['merchant_reference'],
            'merchant_return_url'     => (string) $this->_paymentData['merchant_return_url'],
            'checkout_email'          => (string) $this->_paymentData['checkout_email'],
            'checkout_first_name'     => (string) $this->_paymentData['checkout_first_name'],
            'checkout_last_name'      => (string) $this->_paymentData['checkout_last_name'],
            'checkout_phone_number'   => (string) $this->_paymentData['checkout_phone_number'],
            "checkout_city"           => (string) $this->_paymentData['checkout_city'],
            "checkout_address"        => (string) $this->_paymentData['checkout_address'],
            "checkout_postal_code"    => (string) $this->_paymentData['checkout_postal_code'],
            "preselected_loan_period" => (string) $this->_paymentData['preselected_loan_period'],
            'checkout_products'       => $this->_paymentData['checkout_products'],
        );

        if (isset($this->_paymentData['merchant_notification_url'])) {
            $paymentData['merchant_notification_url'] = (string) $this->_paymentData['merchant_notification_url'];
        }

        if (isset($this->_paymentData['preselected_locale'])) {
            $paymentData['preselected_locale'] = (string) $this->_paymentData['preselected_locale'];
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

    public static function decodePaymentToken($token, $secretKey)
    {
        Firebase\JWT\JWT::$leeway = 60 * 5; // 5 minutes
        return Firebase\JWT\JWT::decode($token, $secretKey, array('HS256'));
    }

    static function getBearerToken($accessKey, $secretKey)
    {
        $data = array(
            'access_key' => $accessKey,
        );

        return Firebase\JWT\JWT::encode($data, $secretKey);
    }
}
