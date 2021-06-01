<?php

/**
 * SDK for Montonio Financing
 */
class MontonioFinancingSDK
{

    /**
     * The Montonio API URL for environment
     *
     * @var string
     */
    private $_apiUrl;

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
     * Root URL for the Montonio Financing Sandbox application
     */
    const MONTONIO_FINANCING_SANDBOX_APPLICATION_URL = 'https://sandbox-application.montonio.com';

    /**
     * Root URL for the Montonio Financing application
     */
    const MONTONIO_FINANCING_APPLICATION_URL = 'https://application.montonio.com';

    public function __construct($accessKey, $secretKey, $environment = 'production')
    {
        $this->_accessKey   = $accessKey;
        $this->_secretKey   = $secretKey;
        $this->_environment = $environment;
        $this->_apiUrl      = $this->_getApiUrlForEnvironment($environment);
    }

    /**
     * Get API URL for provided environment
     *
     * @param string 'production' or 'sandbox'
     *
     * @return string The API URL
     */
    protected function _getApiUrlForEnvironment($env)
    {
        switch ($env) {
            case 'production':return 'https://api.montonio.com';
            case 'sandbox':return 'https://sandbox-api.montonio.com';
            default:return '';
        }
    }

    /**
     * Generate a SHA-256 HMAC signature for financing data
     *
     * @param object $data The data to be scrambled
     *
     * @return string The signature
     */
    protected function _generateSignature($data)
    {
        return hash_hmac('sha256', $data, $this->_secretKey);
    }

    /**
     * Function for making API calls with file_get_contents
     *
     * @param array Context Options
     * @return array Array containing status and json_decode response
     */
    protected function _apiRequest($route, $options)
    {
        $url     = $this->_apiUrl . $route;
        $context = stream_context_create($options);
        $result  = @file_get_contents($url, false, $context);

        // error handling
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
     * Send the financing application draft to Montonio
     *
     * @param string The JSON-encoded draft data
     *
     * @return array The response object
     */
    public function post_montonio_application_draft($draftData)
    {
        $options = array(
            'http' => array(
                'header'  => "Content-Type: application/json\r\n" .
                "x-access-key: {$this->_accessKey}\r\n" .
                "x-signature: {$this->_generateSignature($draftData)}\r\n",
                'method'  => 'POST',
                'content' => $draftData,
            ),
        );
        return $this->_apiRequest("/application_drafts", $options);
    }

    /**
     * Get the financing application by order id
     *
     * @param string Your order reference
     *
     * @return array The response object
     */
    public function get_montonio_application($orderId)
    {
        $url     = "/applications?merchant_reference=" . $orderId;
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/json\r\n" .
                "x-access-key: {$this->_accessKey}\r\n" .
                "x-signature: {$this->_generateSignature($url)}\r\n",
                'method' => 'GET',
            ),
        );
        return $this->_apiRequest($url, $options);
    }

    /**
     * Get the application status by order id
     *
     * @param string Your order reference
     *
     * @return string The financing application status
     */
    public function get_application_status($orderId)
    {
        return $this->get_montonio_application($orderId)["data"][0]->status;
    }

    /**
     * Get the URL string where to redirect the customer to
     *
     * @return string
     */
    public function getPaymentUrl($token)
    {
        $base = ($this->_environment === 'sandbox')
        ? self::MONTONIO_FINANCING_SANDBOX_APPLICATION_URL
        : self::MONTONIO_FINANCING_APPLICATION_URL;

        return $base . '?access_token=' . $token;
    }
}
