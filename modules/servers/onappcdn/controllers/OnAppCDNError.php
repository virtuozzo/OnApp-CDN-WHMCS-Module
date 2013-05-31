<?php
/**
 * Manages CDN Resource Errors
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

        $this->showTemplate(
            'onappcdn/error',
            array(
                'id'                =>  parent::getValue('id'),
                'errors'            =>  implode( PHP_EOL, $errors ),
            )
        );
    }
}