<?php
/**
 * 
 */
class OnAppCDNAdvancedDetails extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    public function show() {
        echo __METHOD__;
    }

    protected function enable() {
        echo __METHOD__;
    }


}

