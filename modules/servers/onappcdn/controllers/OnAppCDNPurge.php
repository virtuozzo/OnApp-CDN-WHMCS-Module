<?php
/**
 * 
 */
class OnAppCDNPurge extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }
    
    public function show() {
        echo __METHOD__;
    }

    /**
     *
     */
    private function purge () {
        echo __METHOD__;
    }

}

