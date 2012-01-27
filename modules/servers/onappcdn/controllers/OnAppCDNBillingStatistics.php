<?php
/**
 * 
 */
class OnAppCDNBillingStatistics extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }
    
    public function show() {
        echo __METHOD__;
    }

}

