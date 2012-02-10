<?php
/**
 * Manages CDN Resources
 * 
 */
class OnAppCDNResources extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     * Shows the list of CDN Resources
     *
     * @param array $errors Error Messages
     * @param array $messages Messages
     */
    public function show( $errors = null, $messages = null ) {
        $onapp = $this->getOnAppInstance();
        
        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $resource  = $onapp->factory('CDNResource', true );

        $resources = $resource->getList();

        $response  = $resource->getResponse();

        $resources_enabled = ( $response['info']['http_code'] == 302 ) ? false : true;

        $this->show_template(
            'onappcdn/cdn_resources',
            array(
                'id'                =>  parent::get_value('id'),
                'resources'         =>  $resources,
                'resources_enabled' =>  $resources_enabled,
                'errors'            =>  implode( PHP_EOL, $errors ),
                'messages'          =>  implode( PHP_EOL, $messages ),
            )
        );
    }

    /**
     * Shows CDN Resource Details
     *
     */
    protected function details () {
        $onapp = $this->getOnAppInstance();

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $_resource  = $onapp->factory('CDNResource', true );

        $resource_id = parent::get_value( 'resource_id' );
        
        $resource   = $_resource->load( $resource_id );

        $onappusers    = $onapp->factory('User', true );
        $onappuser     = $onappusers->load( $resource->_user_id );

        $baseresource  = $onapp->factory('BillingPlan_BaseResource', true );
        $baseresources = $baseresource->getList( $onappuser->_billing_plan_id );

//  TODO display edge_group Info
//        print('<pre>');
//        print_r($baseresources[0]->_prices);
//        die();

        $this->show_template(
            'onappcdn/cdn_resources/details',
            array(
                'id'                =>  parent::get_value('id'),
                'edge_groups'       =>  $edge_groups,
                'baseresources'     =>  $baseresources,
                'resource_id'       =>  $resource_id,
                'resource'          =>  $resource,
                'errors'            =>  implode( PHP_EOL, $errors ),
            )
        );
    }

    /**
     * Enables CDN Resource
     *
     */
    protected function enable( ) {
        
        $onapp = $this->getOnAppInstance();

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $resource  = $onapp->factory('CDNResource', true );

        $resource->enable();
        $this->show();

    }

    /**
     * Edits CDN Resource
     *
     */
    protected function edit () {
        echo __METHOD__;
    }

    /**
     * Deletes CDN Resource
     *
     * @global array $_LANG
     */
    protected function delete () {
        parent::loadcdn_language();
        global $_LANG;
        
        $onapp = $this->getOnAppInstance();

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $resource      = $onapp->factory('CDNResource', true );
        $resource->_id = parent::get_value('resource_id');
        $resource->delete();

        if ( $resource->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $resource->getErrorsAsArray() );

        if ( ! $errors )
            $messages[] = $_LANG['onappcdnresourcedeletesuccessfully'];

        $this->show( $errors, $messages );
    }

    /**
     * Manages creation of new CDN Resource
     *
     * @global array $_LANG language strings
     * @param array $errors Error Messages
     * @param array $messages Messages
     */
    protected function add ( $errors = null, $messages = null ) { 
        global $_LANG;

        if ( $errors || $messages ) {
            unset( $_POST['add'] );
        }

        parent::loadcdn_language();
        $onapp = $this->getOnAppInstance();
        
        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        if ( parent::get_value('add') != 1 ) {
            $whmcsuser = $this->get_user();

            $onappusers    = $onapp->factory('User', true );
            $onappuser     = $onappusers->load( $whmcsuser['onapp_user_id'] );

            $baseresource  = $onapp->factory('BillingPlan_BaseResource', true );
            $baseresources = $baseresource->getList( $onappuser->_billing_plan_id );

            $edge_group = $onapp->factory('CDNResource_AvailableEdgeGroup', true );

            $edge_groups = $edge_group->getList();

// todo Display Billing Info when Nahaylo add target_id to billing_resources

//            print('<pre>');
//            print_r( $baseresources[0] );
//            die();

            $this->show_template(
                'onappcdn/cdn_resources/add',
                array(
                    'id'                =>  parent::get_value('id'),
                    'new_resource'      =>  parent::get_value('new_resource'),
                    'edge_groups'       =>  $edge_groups,
                    'baseresources'     =>  $baseresources,
                    'errors'            =>  implode( PHP_EOL, $errors ),
                    'messages'          =>  implode( PHP_EOL, $messages ),
                )
            );
        }
        else {
            $new_resource = parent::get_value('new_resource');

            if ( is_null( $new_resource['advanced_settings'] ) ) {
                foreach( $new_resource as $key => $field ) {
                    if ( $key != 'cdn_hostname'       &&
                         $key != 'origin'             &&
                         $key != 'type'               &&
                         $key != 'edge_group_ids' 
                    ){
                        unset( $new_resource[$key] );
                    }
                }
            }
            else {
                if ( $new_resource['ip_access_policy'] == 'NONE' ) {
                    unset( $new_resource['ip_access_policy'] );
                    unset( $new_resource['ip_addresses'] );
                }
                if ( $new_resource['country_access_policy'] == 'NONE' ) {
                    unset( $new_resource['country_access_policy'] );
                    unset( $new_resource['countries'] );
                }
                if ( $new_resource['hotlink_policy'] == 'NONE' ) {
                    unset( $new_resource['hotlink_policy'] );
                    unset( $new_resource['domains'] );
                }
                if ( is_null( $new_resource['url_signing_on'] ) ) {
                    unset( $new_resource['url_signing_key'] );
                }

                if ( is_null( $new_resource['password_on'] ) ) {
                    unset( $new_resource['password_unauthorized_html'] );
                    unset( $new_resource['form_pass'] );
                }
            }

            $resource      = $onapp->factory('CDNResource', true );

            foreach( $new_resource as $key => $value ) {
                if ( $key != 'advanced_settings' ) {
                    $key = '_'.$key;
                    $resource->$key = $value;

                }
            }

            $resource->save();

            if ( $resource->getErrorsAsArray() )
                $errors[] = implode( PHP_EOL , $resource->getErrorsAsArray() );

            if ( ! $errors ) {
                $messages[] = $_LANG['onappcdnresourcecreatedsuccessfully'];
                $this->show( $errors, $messages );
            }
            else {   
                $this->add( $errors, $messages );
            }
        }
    }
}
