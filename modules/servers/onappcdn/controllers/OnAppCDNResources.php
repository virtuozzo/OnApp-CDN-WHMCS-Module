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

        $whmcs_client_details  =  $this->getWhmcsClientDetails();
        
        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $resource  = $onapp->factory('CDNResource', true );

        $resources = $resource->getList();

        foreach( $resources as $_resources ) {
            $__resources[ $_resources->_id ]['_last_24h_cost'] =
                $whmcs_client_details['currencyprefix'] .
                round( $_resources->_last_24h_cost * $whmcs_client_details['currencyrate'], 2 )
                . ' ' . $whmcs_client_details['currencycode'];
            $__resources[ $_resources->_id ]['_resource_type'] = $_resources->_resource_type;
            $__resources[ $_resources->_id ]['_cdn_hostname'] = $_resources->_cdn_hostname;
            $__resources[ $_resources->_id ]['_origins_for_api'] = '';
            foreach( $_resources->_origins_for_api as $origin ) {
                $__resources[ $_resources->_id ]['_origins_for_api'] = $origin->_value . PHP_EOL;
            }
        }

        $response  = $resource->getResponse();

        $resources_enabled = ( $response['info']['http_code'] == 302 ) ? false : true;

        if ( isset( $_SESSION['successmessages'] ) ) {
            $messages[] = $_SESSION['successmessages'];
            unset( $_SESSION['successmessages'] );
        }

        $this->show_template(
            'onappcdn/cdn_resources',
            array(
                'id'                =>  parent::get_value('id'),
                'resources'         =>  $__resources,
                'resources_enabled' =>  $resources_enabled,
                'errors'            =>  implode( PHP_EOL, $errors ),
                'messages'          =>  implode( PHP_EOL, $messages ),
            )
        );
    }

    /**
     * Enables CDN Resource Panel For User
     *
     * @return void
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
     * Manage CDN Resource parameters update / edit
     *
     * @global array $_LANG language strings
     * @param array $errors Errors array
     * @param array $messages Messages array
     */
    protected function edit ( $errors = null, $messages = null ) {
        global $_LANG;
        $resource_id = parent::get_value( 'resource_id' );

        if ( $errors || $messages ) {
            unset( $_POST['edit'] );
        }

        parent::loadcdn_language();
        $onapp = $this->getOnAppInstance();

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        if ( parent::get_value('edit') != 1 ) {
            
            $onapp = $this->getOnAppInstance();

            if ( $onapp->getErrorsAsArray() )
                $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

            $_resource  = $onapp->factory('CDNResource', true );

            $resource   = $_resource->load( $resource_id );

            $edge_group_ids = array();
            foreach( $resource->_edge_groups as $group ) {
                $edge_group_ids[] = $group->_id;
            }

            $onappusers    = $onapp->factory('User', true );
            $onappuser     = $onappusers->load( $resource->_user_id );

            $baseresource  = $onapp->factory('BillingPlan_BaseResource', true );
            $baseresources = $baseresource->getList( $onappuser->_billing_plan_id );

            $advanced  = $onapp->factory('CDNResource_Advanced', true );
            $advanced_details = $advanced->getList( $resource_id );

            $available_edge_groups = $onapp->factory('CDNResource_AvailableEdgeGroup');

            foreach ( $baseresources as $edge_group ) {
                if ( $edge_group->_resource_name == 'edge_group' )
                {
                    $edge_group_baseresources[ $edge_group->_id ]['price']       = $edge_group->_prices[0]->_price;

                    foreach ( $available_edge_groups->getList( $edge_group->_id ) as $group ) {
                        if ( $group->_id == $edge_group->_target_id ) {
                            $edge_group_baseresources[ $edge_group->_id ]['locations']  = $group->_edge_group_locations;
                            $edge_group_baseresources[ $edge_group->_id ]['id']         = $group->_id;
                            $edge_group_baseresources[ $edge_group->_id ]['label']      = $group->_label;
                        }
                    }
                }
            }

            foreach( $advanced_details[0]->_countries as $country ) {
                $countries_ids[] = $country->_id;
            }

            $passwords_html = '';
            foreach ( $advanced_details[0]->_passwords as $user => $password  ) {
                $passwords_html .= '<tr>'                                                                                            .'\n'.
                   '<td>'                                                                                                            .'\n'.
                       '<input class="username_input" value="'.$user.'" type="text" name="resource[form_pass][user][]" />'           .'\n'.
                    '</td>'                                                                                                          .'\n'.
                    '<td>'                                                                                                           .'\n'.
                        '<input class="password_input" value="'.$password.'" type="text" name="resource[form_pass][pass][]" />'      .'\n'.
                    '</td>'                                                                                                          .'\n'.
                    '</tr>'                                                                                                          .'\n';
            }

            $this->show_template(
                'onappcdn/cdn_resources/edit',
                array(
                    'edge_group_ids'            =>  $edge_group_ids,
                    'passwords_html'            =>  $passwords_html,
                    'countries_ids'             =>  json_encode( $countries_ids ),
                    'resource'                  =>  $resource,
                    'resource_id'               =>  $resource_id,
                    'advanced_details'          =>  $advanced_details[0],
                    'id'                        =>  parent::get_value('id'),
                    'whmcs_client_details'      =>  $this->getWhmcsClientDetails(),
                    'edge_group_baseresources'  =>  $edge_group_baseresources,
                    'errors'                    =>  implode( PHP_EOL, $errors ),
                    'messages'                  =>  implode( PHP_EOL, $messages ),
                )
            );
        }
        else {
            $resource = parent::get_value('resource');
            
            if ( is_null( $resource['advanced_settings'] ) ) {
                foreach( $resource as $key => $field ) {
                    if ( $key != 'cdn_hostname'       &&
                         $key != 'origin'             &&
                         $key != 'type'               &&
                         $key != 'edge_group_ids'     &&
                         $key != 'id'
                    ){
                        unset( $resource[$key] );
                    }
                }
            }
            else {
                if ( $resource['ip_access_policy'] == 'NONE' ) {
                    unset( $resource['ip_access_policy'] );
                    unset( $resource['ip_addresses'] );
                }
                if ( $resource['country_access_policy'] == 'NONE' ) {
                    unset( $resource['country_access_policy'] );
                    unset( $resource['countries'] );
                }
                if ( $resource['hotlink_policy'] == 'NONE' ) {
                    unset( $resource['hotlink_policy'] );
                    unset( $resource['domains'] );
                }
                if ( is_null( $resource['url_signing_on'] ) ) {
                    unset( $resource['url_signing_key'] );
                }

                if ( is_null( $resource['password_on'] ) ) {
                    unset( $resource['password_unauthorized_html'] );
                    unset( $resource['form_pass'] );
                }
            }

            $_resource      = $onapp->factory('CDNResource', true );

            foreach( $resource as $key => $value ) {
                if ( $key != 'advanced_settings' ) {
                    $key = '_'.$key;
                    $_resource->$key = $value;
                }
            }

            $_resource->save();

            if ( $_resource->getErrorsAsArray() )
                $errors[] = implode( PHP_EOL , $_resource->getErrorsAsArray() );

            if ( ! $errors ) {
                $messages[] = $_LANG['onappcdnresourceupdatedsuccessfully'];
                $_SESSION['successmessages'] = $messages;
                $url = ONAPPCDN_FILE_NAME . '?page=details&id=' .parent::get_value( 'id' ).'&resource_id=' . $resource_id;
                $this->redirect($url);
            }
            else {
                $this->edit( $errors, $messages );
            }
            
        }
    }

    /**
     * Deletes CDN Resource
     *
     * @global array $_LANG
     * @return void
     * 
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

            $available_edge_groups = $onapp->factory('CDNResource_AvailableEdgeGroup');

            foreach ( $baseresources as $edge_group ) {
                if ( $edge_group->_resource_name == 'edge_group' )
                {
                    $edge_group_baseresources[ $edge_group->_id ]['price']       = $edge_group->_prices[0]->_price;

                    foreach ( $available_edge_groups->getList( $edge_group->_id ) as $group ) {
                        if ( $group->_id == $edge_group->_target_id ) {
                            $edge_group_baseresources[ $edge_group->_id ]['locations']  = $group->_edge_group_locations;
                            $edge_group_baseresources[ $edge_group->_id ]['id']         = $group->_id;
                            $edge_group_baseresources[ $edge_group->_id ]['label']      = $group->_label;
                        }
                    }
                }
            }
            
            $this->show_template(
                'onappcdn/cdn_resources/add',
                array(
                    'id'                        =>  parent::get_value('id'),
                    'new_resource'              =>  parent::get_value('new_resource'),
                    'whmcs_client_details'      =>  $this->getWhmcsClientDetails(),
                    'edge_group_baseresources'  =>  $edge_group_baseresources,
                    'errors'                    =>  implode( PHP_EOL, $errors ),
                    'messages'                  =>  implode( PHP_EOL, $messages ),
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
