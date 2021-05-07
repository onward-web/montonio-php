<?php

/**
 * Usage example:
 * -------------------------------------------------------------------------
 * IMPORTANT! 
 * Please have the $banklist fetched from 
 * MontonioPaymentsSDK::fetchBankList() beforehand and saved/cached to 
 * your database.
 * Please do not fetch the banklist every time you load the checkout.
 * -------------------------------------------------------------------------
 * $banklist = ...
 * 
 * $checkout = new MontonioPaymentsCheckout();
 * $checkout->set_description('Pay with your bank');
 * $checkout->set_preferred_country('EE');
 * $checkout->set_payment_handle_style('grid_logos');
 * $checkout->set_banklist($banklist);
 * 
 * $html = $checkout->get_description_html();
 * // Render the HTML to your page
 * ...
 */ 

/**
 * This class controls the UI side of the Checkout page.
 * It has methods to configure and display banks and card processors at checkout
 */
class MontonioPaymentsCheckout
{
    /**
     * The payment handle style identifier which controls how to show the banks
     *
     * @var string 'description' | 'grid_logos' | 'list_logos'
     */
    protected $_payment_handle_style;

    /**
     * Dictionary for regions Montonio operates
     *
     * @var array
     */
    protected $regions = array(
        'EE' => 'Estonia',
        'LT' => 'Lithuania',
        'LV' => 'Latvia',
        'FI' => 'Finland'
    );

    /**
     * The country identifier (e.g "EE") that will be shown first in the banks list dropdown
     *
     * @var string
     */
    protected $_preferred_country;
    
    /**
     * The default description shown at checkout if the Payment Handle Style is not grid or list
     *
     * @var string
     */
    protected $_description;
    
    /**
     * Banks to show at checkout
     * @see MontonioPaymentsSDK::fetchBankList()
     *
     * @var object
     */
    protected $_bankList;
    
    /**
     * Get the HTMLstring for checkout by Payment Handle Style
     *
     * @return string The HTML for checkout
     */
    public function get_description_html()
    {
        if (is_object($this->_bankList) && count((array) $this->_bankList) > 0) {
            switch ($this->get_payment_handle_style()) {
                case 'list_logos':
                    return $this->get_html_list_logos($this->_bankList);
                case 'grid_logos':
                    return $this->get_html_grid_logos($this->_bankList);
                default:
                    return $this->get_default_description();
            }
        } else {
            return $this->get_default_description();
        }
    }

    /**
     * Get an HTMLstring for Payment Handle Style: grid_logos 
     *
     * @return string The HTML for checkout
     */
    protected function get_html_grid_logos($regions)
    {
        $preselectedAspsp = '<input type="hidden" name="montonio_payments_preselected_aspsp" id="montonio_payments_preselected_aspsp">';
        $description = $this->get_dropdown_html($regions);
    
        $description .= '<div id="montonio-payments-description" class="montonio-aspsp-grid montonio-aspsp-grid-logos">';
        foreach ($regions as $r => $list) {
            foreach ($list as $key => $value) {
                $description .= '<div class="aspsp-region-'. $r .' montonio-aspsp-grid-item montonio-aspsp '. ($r == $this->get_preferred_country() ? '' : 'montonio-hidden') .'" data-aspsp="' . $value->bic
                . '"><img class="montonio-aspsp-grid-item-img" src="' . $value->logo_url . '"></div>';
            }
        }
        $description .= '</div>';
            
        return $preselectedAspsp . $description;
    }

    /**
     * Get an HTMLstring for Payment Handle Style: list_logos 
     *
     * @return string The HTML for checkout
     */
    protected function get_html_list_logos($regions)
    {
        $preselectedAspsp = '<input type="hidden" name="montonio_payments_preselected_aspsp" id="montonio_payments_preselected_aspsp">';
        $description = $this->get_dropdown_html($regions);
        $description .= '<ul id="montonio-payments-description" class="montonio-aspsp-ul montonio-aspsp-list-logos">';
        
        foreach ($regions as $r => $list) {
            foreach ($list as $key => $value) {
                $description .= '<li data-aspsp="' . $value->bic . '" class="aspsp-region-'. $r .' montonio-aspsp-li montonio-aspsp '. ($r == $this->get_preferred_country() ? '' : 'montonio-hidden') .'"><img class="montonio-aspsp-li-img" src="' . $value->logo_url . '"></li>';
            }
        }
        $description .= '</ul>';
        return $preselectedAspsp . $description;
    }

    /**
     * Get an HTMLstring for Payment Handle Style: description 
     *
     * @return string The HTML for checkout
     */
    protected function get_default_description()
    {
        return $this->get_description();
    }

    /**
     * Get the country selector dropdown HTML
     *
     * @param array $regions The regions dictionary
     * 
     * @return string
     */
    protected function get_dropdown_html($regions) {
        $html = '<select class="montonio-payments-country-dropdown" name="country">';
        foreach ($regions as $r => $list) {
            $html .= '<option '. ($r == $this->get_preferred_country() ? 'selected="selected"' : '') .' value="'. $r .'">'. $this->regions[$r] .'</option>';
        }
        $html .= '</select>';

        return $html;
    }

    // =========================================================================
    // Getters and setters
    // =========================================================================
    
    /**
     * @return object
     */
    public function get_payment_handle_style()
    {
        return $this->_payment_handle_style;
    }
    
    /**
     * @param object $payment_handle_style
     */
    public function set_payment_handle_style($payment_handle_style)
    {
        $this->_payment_handle_style = $payment_handle_style;
    }
    
    /**
     * JSON-decode bankList
     *
     * @param object $payment_handle_style
     */
    public function set_banklist($banklistJson)
    {
        $this->_bankList = json_decode($banklistJson);
    }
    
    public function set_description($description) {
        $this->_description = $description;
    }
    
    public function get_description() {
        return $this->_description;
    }

    public function set_preferred_country($country) {
        $this->_preferred_country = $country;
    }

    public function get_preferred_country() {
        return $this->_preferred_country;
    }

    /**
     * Set the translations for country dropdown
     * 
     * @param array $regions
     * @example array(
     *      'EE' => 'Eesti',
     *      'FI' =>'Suomi',
     *      'LV' => 'Latvija',
     *      'LT' => 'Lietuva'
     * )
     * 
     * @return void
     */
    public function set_regions($regions) {
        $this->regions = $regions;
    }

    public function get_regions() {
        return $this->regions;
    }
}