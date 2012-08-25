<?php
/**
 * Represents CDN Advanced Details
 */
error_reporting( E_ALL );
ini_set( 'display_errors', 1 );
ini_set('html_errors', 1);
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
        global $_COUNTRIES;
        include dirname( dirname(__FILE__ ) ) .DS. 'includes' .DS. 'countries.php';
                
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }
        
        $onapp = $this->getOnAppInstance();

        $resource_id = parent::get_value( 'resource_id' );

        $advanced  = $onapp->factory('CDNResource_Advanced', true );

        $details = $advanced->getList( $resource_id );
        
        

        foreach( $details[0]->_countries as $tz ) {
            $countries[$tz] = $_COUNTRIES[$tz];
        }
        
        $this->show_template(
            'onappcdn/cdn_resources/advanced_details',
            array(
                'id'                =>  parent::get_value('id'),
                'details'           =>  $details[0],
                'selected_countries'=>  $countries,
                'resource_id'       =>  $resource_id,
                'errors'            =>  ( $errors ) ? implode( PHP_EOL, $errors ) : null,
                'messages'          =>  ( $messages ) ? implode( PHP_EOL, $messages ) : null,
            )
        );
    }
}