<?php
/**
 * Manages CDN Resource Errors
 *
 */
class OnAppCDNError extends OnAppCDN {

    public function __construct () {
        parent::__construct();
    }

    /**
     * Shows errors
     * 
     */
    public function show(  ) {

        if ( isset( $_SESSION['failerrors'] ) ) {
            $errors = $_SESSION['failerrors'];
            unset( $_SESSION['failerrors'] );
        }
        
        $this->show_template(
            'onappcdn/error',
            array(
                'id'                =>  parent::get_value('id'),
                'errors'            =>  implode( PHP_EOL, $errors ),
            )
        );
    }
}