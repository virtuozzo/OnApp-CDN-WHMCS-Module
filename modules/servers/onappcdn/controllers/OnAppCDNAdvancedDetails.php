<?php
/**
 * Represents CDN Advanced Details
 */
class OnAppCDNAdvancedDetails extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     * Displays CDN Advanced Details
     * 
     * @param string $errors
     * @param string $messages 
     */
    public function show( $errors = null, $messages = null ) {
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }
        
        $onapp = $this->getOnAppInstance();

        $resource_id = parent::get_value( 'resource_id' );

        $advanced  = $onapp->factory('CDNResource_Advanced', true );

        $details = $advanced->getList( $resource_id );

        $this->show_template(
            'onappcdn/cdn_resources/advanced_details',
            array(
                'id'                =>  parent::get_value('id'),
                'details'           =>  $details[0],
                'resource_id'       =>  $resource_id,
                'errors'            =>  implode( PHP_EOL, $errors ),
                'messages'          =>  implode( PHP_EOL, $messages ),
            )
        );

    }

}

