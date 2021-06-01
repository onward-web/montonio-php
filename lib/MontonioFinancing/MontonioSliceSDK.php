<?php

require_once 'MontonioFinancingSDK.php';

class MontonioSliceSDK extends MontonioFinancingSDK
{
    /**
     * Override: Loan type in the Montonio system
     *
     * @var string 'hire_purchase' for classical financing, 'slice' for the Slice product
     */
    protected $_loan_type = 'slice';

    /**
     * Override: Get the root URL for the Montonio Financing application
     */
    protected function getApplicationUrl() {
        return ($this->_environment === 'sandbox')
        ? 'https://sandbox-financing.montonio.com'
        : 'https://financing.montonio.com';
    }
}