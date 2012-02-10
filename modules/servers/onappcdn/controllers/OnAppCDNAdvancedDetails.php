<?php
/**
 * 
 */
class OnAppCDNAdvancedDetails extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    public function show( $errors = null, $messages = null ) {
        $onapp = $this->getOnAppInstance();

        $resource_id = parent::get_value( 'resource_id' );
        
        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

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

