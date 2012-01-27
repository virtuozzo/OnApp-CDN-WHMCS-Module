<?php
/**
 * 
 */
class OnAppCDNPrefetch extends OnAppCDN {

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
    private function prefetch () {
        echo __METHOD__;
    }

}

