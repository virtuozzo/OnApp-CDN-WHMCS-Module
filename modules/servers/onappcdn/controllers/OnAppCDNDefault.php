<?php
/**
 * 
 */
class OnAppCDNDefault extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }
    
    protected function create() {
        echo __METHOD__;
    }

}

