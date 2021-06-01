<?php

/**
 * We use php-jwt for JWT creation
 */
require_once 'jwt/JWT.php';

/**
 * Montonio Financing SDK
 *
 * @version 2.0.0
 * @author Montonio Finance OÜ <developers@montonio.com>
 */
class MontonioFinancingSDK
{
    /**
     * Payment Data for Montonio payment_token generation
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
     * Loan type in the Montonio system
     *
     * @var string 'hire_purchase' for classical financing, 'slice' for the Slice product
     */
    protected $_loan_type = 'hire_purchase';

    /**
     * Get the root URL for the Montonio Financing application
     */
    protected function getApplicationUrl()
    {
        return ($this->_environment === 'sandbox')
        ? 'https://sandbox-application.montonio.com'
        : 'https://application.montonio.com';
    }

    /**
     * Constructor for MontonioFinancingSDK
     *
     * @param string $accessKey - your Montonio Access Key
     * @param string $secretKey - your Montonio Secret Key
     * @param string $environment - 'production' for live environment, 'sandbox' for testing environment
     */
    public function __construct($accessKey, $secretKey, $environment)
    {
        $this->_accessKey   = $accessKey;
        $this->_secretKey   = $secretKey;
        $this->_environment = $environment;
    }

    /**
     * Get the URL with payement token where to redirect the customer to
     *
     * @return string
     */
    public function getPaymentUrl()
    {
        $base = $this->getApplicationUrl();
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
         * Cast Payment Data to correct data types
         * and add additional data
         */
        $paymentData = array(
            'origin'                  => 'online',
            'loan_type'               => $this->_loan_type,
            'access_key'              => (string) $this->_accessKey,
            'currency'                => (string) $this->_paymentData['currency'],
            'merchant_name'           => (string) $this->_paymentData['merchant_name'],
            'merchant_reference'      => (string) $this->_paymentData['merchant_reference'],
            'merchant_return_url'     => (string) $this->_paymentData['merchant_return_url'],
            'checkout_email'          => (string) $this->_paymentData['checkout_email'],
            'checkout_first_name'     => (string) $this->_paymentData['checkout_first_name'],
            'checkout_last_name'      => (string) $this->_paymentData['checkout_last_name'],
            'checkout_phone_number'   => (string) $this->_paymentData['checkout_phone_number'],
            'checkout_city'           => (string) $this->_paymentData['checkout_city'],
            'checkout_address'        => (string) $this->_paymentData['checkout_address'],
            'checkout_postal_code'    => (string) $this->_paymentData['checkout_postal_code'],
            'preselected_loan_period' => (int) $this->_paymentData['preselected_loan_period'],
        );

        /**
         * Add array of products to Payment Data
         *
         * @example array(array(
         *   "product_name"  => 'Some product'
         *   "product_price" => (float) 19.99,
         *   "quantity"      => (int) 1,
         * ));
         */
        $paymentData['checkout_products'] = $this->_paymentData['checkout_products'];

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

    public static function decodePaymentToken($token, $secretKey)
    {
        // Add a bit of leeway to JWT decoding because some servers have their current time in the past
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

    /**
     * Set payment data
     *
     * @param array $paymentData
     * @return self
     */
    public function setPaymentData($paymentData)
    {
        $this->_paymentData = $paymentData;
        return $this;
    }

    /**
     * Get payment data
     *
     * @return array $this->_paymentData
     */
    public function getPaymentData()
    {
        return $this->_paymentData;
    }
}
