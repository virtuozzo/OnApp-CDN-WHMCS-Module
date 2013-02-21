<?php
/**
 * Manages CDN Resources
 * 
 */

//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );
//ini_set('html_errors', 1);
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
        
        $resource  = $onapp->factory('CDNResource', true );

        $resources = $resource->getList();
        
        foreach ( $resources as $key => $resource ) {
            if ( $resource->_user_id != $whmcs_client_details['onapp_user_id'] ) {
                unset( $resources[$key] );
            }
        }
        
        if ( isset ( $resources[0]->_user_id ) ) {
            $users = $onapp->factory('User');
            $user  = $users->load( $resources[0]->_user_id );
            
            $outstanding_amount = $whmcs_client_details['currencyprefix'] .
            round ( $user->_outstanding_amount  *  $whmcs_client_details['currencyrate'], 2)
                . ' ' . $whmcs_client_details['currencycode'] ;
        } else {
            $outstanding_amount = 0;
        }

        $__resources = array();
        foreach( $resources as $_resources ) {
            $__resources[ $_resources->_id ]['_last_24h_cost'] =
                $whmcs_client_details['currencyprefix'] .
                round( $_resources->_last_24h_cost * $whmcs_client_details['currencyrate'], 2 )
                . ' ' . $whmcs_client_details['currencycode'];
            $__resources[ $_resources->_id ]['_resource_type'] = $_resources->_resource_type;
            $__resources[ $_resources->_id ]['_cdn_hostname'] = $_resources->_cdn_hostname;
            $__resources[ $_resources->_id ]['_origins_for_api'] = '';

            foreach( $_resources->_origins as $origin ) {
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
                'errors'            =>  ( is_array( $errors )) ? implode( PHP_EOL, $errors ) : null,
                'messages'          =>  ( is_array( $messages )) ? implode( PHP_EOL, $messages ) : null,
                'outstanding_amount'=>  $outstanding_amount,
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
        
        $types = $this->getTypes();
        $type =  parent::get_value('type');
        $template = ( $type ) ? 
            'onappcdn/cdn_resources/edit_' . $types[$type]['template'] : 'onappcdn/cdn_resources/edit_http';        
        
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }
        
        global $_LANG;
        $resource_id = parent::get_value( 'resource_id' );
        
        if ( $errors || $messages ) {
            unset( $_POST['edit'] );
        }

        parent::loadcdn_language();
        $onapp = $this->getOnAppInstance();
     
        if ( parent::get_value('edit') != 1 ) {
            
        $data = $this->getResourceData( $onapp, $type, $types, 'edit' ); 

            $countries_ids = ( $data['advanced_details']->_countries ) ? $data['advanced_details']->_countries : array();
            
            $passwords_html = $this->generate_passwords_html( $data['advanced_details']->_passwords );

            if ( isset( $_SESSION['errors'] ) ) {
                $errors[] = $_SESSION['errors'];
                unset( $_SESSION['errors'] );
            }
            if ( isset( $_SESSION['successmessages'] ) ) {
                $messages[] = $_SESSION['successmessages'];
                unset( $_SESSION['successmessages'] );
            }

            $this->show_template(
                $template,
                array(
                    'edge_group_ids'            =>  $data['edge_group_ids'],
                    'passwords_html'            =>  $passwords_html,
                    'countries_ids'             =>  json_encode( $countries_ids ),
                    'resource'                  =>  $data['resource'],
                    'resource_id'               =>  $resource_id,
                    'advanced_details'          =>  $data['advanced_details'],
                    'id'                        =>  parent::get_value('id'),
                    'whmcs_client_details'      =>  $data['whmcs_client_details'],
                    'edge_group_baseresources'  =>  $data['edge_group_baseresources'],
                    'errors'                    =>  ( is_array( $errors )) ? implode( PHP_EOL, $errors ) : null,
                    'messages'                  =>  ( is_array( $messages )) ? implode( PHP_EOL, $messages ) : null,
                    'ssl_on'                    =>  ( boolean )strpos( $data['resource']->_cdn_hostname, 'worldssl' ),
                )
            );
        }
        else {
            $resource = $this->process_request( parent::get_value('resource') , 'edit' );
            
            $_resource      = $onapp->factory('CDNResource', true );

            foreach( $resource as $key => $value ) {
                if ( $key != 'advanced_settings' ) {
                    $key = '_'.$key;
                    $_resource->$key = $value;
                }
            }
            
// needed to disable WHMCS html auto escape in forms.            
            $_resource->_password_unauthorized_html = html_entity_decode( $_resource->_password_unauthorized_html );

            if(is_numeric(parent::get_value('resource_id')))
                $_resource->_id = parent::get_value('resource_id'); 

            $_resource->save();
            
            if ( $_resource->getErrorsAsArray() )
                $errors[] = '<b>Edit CDN Resource Error: </br >' . $_resource->getErrorsAsString();

            if ( ! $errors ) {
                $messages = $_LANG['onappcdnresourceupdatedsuccessfully'];
                $_SESSION['successmessages'] = $messages;
                $url = ONAPPCDN_FILE_NAME . '?page=details&id=' .parent::get_value( 'id' ).'&resource_id=' . $resource_id;
                $this->redirect($url);
            }
            else {
                $_SESSION['errors'] = implode( PHP_EOL, $errors );
                $this->redirect( ONAPPCDN_FILE_NAME . '?page=resources&action=edit&resource_id='. parent::get_value('resource_id') .'&id=' . parent::get_value('id').'&type=' . $type );
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
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }
        
        parent::loadcdn_language();
        global $_LANG;
        
        $onapp = $this->getOnAppInstance();

        $resource      = $onapp->factory('CDNResource', true );
        $resource->_id = parent::get_value('resource_id');
        $resource->delete();
        
        if ( $resource->getErrorsAsArray() )
            $errors[] = '<b>Delete CDN Resource Error: </b>' . $resource->getErrorsAsString();

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
        $types = $this->getTypes();
        $type = parent::get_value('type');
        parent::loadcdn_language();
        global $_LANG;
        
        $template = ( parent::get_value('type') ) ? 
            'onappcdn/cdn_resources/add_' . $types[$type]['template'] : 'onappcdn/cdn_resources/add_http';
        
        if ( $errors || $messages ) {
            unset( $_POST['add'] );
        }

        $onapp = $this->getOnAppInstance();
        
        if ( parent::get_value('add') != 1 ) {
            
            $data = $this->getResourceData( $onapp, $type, $types, 'add' );
            $errors = $data['errors'];
            
            if ( isset( $_SESSION['errors'] ) ) {
                $errors[] = $_SESSION['errors'];
                unset( $_SESSION['errors'] );
            }
            if ( isset( $_SESSION['resource'] ) ) {
                $session_resource = $_SESSION['resource'];
                unset( $_SESSION['resource'] );
            }

            $passwords_array = array();
            if( isset( $session_resource['form_pass']['user'] ) ){
                foreach( $session_resource['form_pass']['user'] as $key => $value ) {
                    $passwords_array[$value] = $session_resource['form_pass']['pass'][$key];
                }
            } 
            
            $passwords_html = $this->generate_passwords_html( $passwords_array );

            $countries   = ( ! isset ( $session_resource['countries'] ) ) ? '[]' : json_encode($session_resource['countries']);

            $this->show_template(
                $template,
                array(
                    'id'                        =>  parent::get_value('id'),
                    'resource'                  =>  parent::get_value('resource'),
                    'whmcs_client_details'      =>  $this->getWhmcsClientDetails(),
                    'edge_group_baseresources'  =>  $data['edge_group_baseresources'],
                    'edge_group_locations_ids'  =>  json_encode($data['edge_group_locations_ids']),
                    'errors'                    =>  ( is_array( $errors )) ? implode( PHP_EOL, $errors ) : null,
                    'messages'                  =>  ( is_array( $messages )) ? implode( PHP_EOL, $messages ) : null,
                    'session_resource'          =>  $session_resource,
                    'passwords_html'            =>  $passwords_html,
                    'countries'                 =>  $countries,
                )
            );
        }
        else {
            $resource = $this->process_request( parent::get_value('resource'), 'add' );
            
            $_resource      = $onapp->factory('CDNResource', true );
            
            foreach( $resource as $key => $value ) {
                if ( $key != 'advanced_settings' ) {
                    $key = '_'.$key;
                    $_resource->$key = $value;

                }
            }
 
            $_resource->save();

            if ( $_resource->getErrorsAsArray() )
                $errors[] = '<b>Create CDN Resource Error: </b>' . $_resource->getErrorsAsString();

            if ( ! $errors ) {
                $messages = $_LANG['onappcdnresourcecreatedsuccessfully'];
                $_SESSION['successmessages'] = $messages;
                $this->redirect( ONAPPCDN_FILE_NAME . '?page=resources&id=' . parent::get_value('id') );
            }
            else {
                $_SESSION['resource'] = $resource;
                $_SESSION['errors'] = implode( PHP_EOL, $errors );
                
                $this->redirect( ONAPPCDN_FILE_NAME . '?page=resources&type=' . $type . '&action=add&id=' . parent::get_value('id') );
            }
        }
    }

    /**
     * Show choose CDN resource type page
     * 
     * @global mixed $LANG 
     */
    protected function choose_resource_type(){
        $this->show_template(
            'onappcdn/cdn_resources/choose_resource_type',
            array(
                'resource_types' => $this->getTypes(),
                'id'             =>  parent::get_value('id'),
            )
        );
    }
    
    /**
     *
     * @global array $_LANG
     * @return type 
     */
    public function getTypes(){
        parent::loadcdn_language();
        global $_LANG;
        return array(
//            'STREAM_LIVE'     =>  array( 'template'     => 'live_streaming', 
//                                         'requirements' => 'streamSupported',
//                                         'description'  =>  $_LANG['onappcdnlivestreamingresource'],
//                                         'label'        =>  $_LANG['onappcdnlivestreaming'] ),
            'HTTP_PULL'       =>  array( 'template'     => 'http', 
                                         'requirements' => 'httpSupported',
                                         'description'  =>  $_LANG['onappcdnhttppullorpushresource'],
                                         'label'        =>  'HTTP' ),                
//            'HTTP_PUSH'       =>  array( 'template'     => 'http', 
//                                         'requirements' => 'httpSupported',
//                                         'description'  =>  $_LANG['onappcdnhttppullorpushresource'],
//                                         'label'        =>  'HTTP' ),                
//            'STREAM_VOD_PULL' =>  array( 'template'     => 'video_on_demand', 
//                                         'requirements' => 'streamSupported',
//                                         'description'  =>  $_LANG['onappcdnvideoondemandresource'],
//                                         'label'        =>  $_LANG['onappcdnvideoondemand'] ),                
        );        
    }
    
    public function process_request( $resource, $action ){
            if ( ! isset ( $resource['advanced_settings'] ) ) {
                foreach( $resource as $key => $field ) {
                    if ( $key != 'cdn_hostname'                      &&
                         $key != 'origin'                            &&
                         $key != 'resource_type'                     &&
                         $key != 'edge_group_ids'                    &&
                         $key != 'ftp_password'                      &&
                         $key != 'ssl_on'                            &&
                         $key != 'publishing_point'                  &&
                         $key != 'external_publishing_url'           &&
                         $key != 'internal_publishing_point'         && 
                         $key != 'failover_external_publishing_url'  &&
                         $key != 'failover_internal_publishing_point'         
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
                if ( $resource['anti_leech_on'] == 'NONE' ) {
                    unset( $resource['anti_leech_on'] );
                    unset( $resource['anti_leech_domains'] );
                }                
                
                if ( $resource['country_access_policy'] == 'NONE' ) {
//                    unset( $resource['country_access_policy'] );
//                    unset( $resource['countries'] );
                }
                if ( $resource['hotlink_policy'] == 'NONE' ) {
                    unset( $resource['hotlink_policy'] );
                    unset( $resource['domains'] );
                }
                if ( is_null( $resource['url_signing_on'] ) ) {
                    unset( $resource['url_signing_key'] );
                    
                }
                if ( is_null( $resource['secure_wowza_on'] ) ) {
                    unset( $resource['secure_wowza_token'] );
                    $resource['secure_wowza_on'] = '0';
                }                
                if ( is_null( $resource['password_on'] ) ) {
                    unset( $resource['password_unauthorized_html'] );
                    unset( $resource['form_pass'] );
                }
                
                if ( !isset( $resource['url_signing_on']) || is_null( $resource['url_signing_on'] ) ) {
                    $resource['url_signing_on'] = 0;
                }

                if ( !isset( $resource['mp4_pseudo_on'] ) || is_null( $resource['mp4_pseudo_on'] )  ){
                    $resource['mp4_pseudo_on'] = 0;
                }

                if ( !isset( $resource['flv_pseudo_on'] ) || is_null( $resource['flv_pseudo_on'] )  ){
                    $resource['flv_pseudo_on'] = 0;
                } 

                if ( !isset( $resource['ignore_set_cookie_on'] ) || is_null( $resource['ignore_set_cookie_on'] )  ){
                    $resource['ignore_set_cookie_on'] = 0;
                }                 

                if ( !isset ( $resource['countries']) ){
                    $resource['countries'] = array();
                }                
            }                
            
            if ( $resource['publishing_point'] == 'internal' ) {
                unset($resource['external_publishing_url']);
                unset($resource['failover_external_publishing_url']);
            } elseif ( $resource['publishing_point'] == 'external' ){
                unset($resource['internal_publishing_point']);
                unset($resource['failover_internal_publishing_point']);
            }  
            
            if ( $resource['ssl_on'] == '1' ) {
                $resource['cdn_hostname'] .= '.r.worldssl.net';
            }
            
            return $resource;
    }
    
    /**
     *
     * @return type 
     */
    private function getResourceData( $onapp, $type, $types, $action ){
        $errors = '';
        
        if ( $action == 'edit' ) {
            $_resource  = $onapp->factory('CDNResource', true );

            $resource   = $_resource->load( parent::get_value( 'resource_id' ) );
            
            $edge_group_ids = array();
            foreach( $resource->_edge_groups as $group ) {
                $edge_group_ids[] = $group->_id;
            }

            $advanced  = $onapp->factory('CDNResource_Advanced', true );
            $advanced_details = $advanced->getList( parent::get_value( 'resource_id' ) );
            
        }
        
        $whmcs_client_details  =  $this->getWhmcsClientDetails();
        $whmcsuser = $this->get_user();

        $onappusers    = $onapp->factory('User', true );
        $onappuser     = $onappusers->load( $whmcsuser['onapp_user_id'] );

        if ( $onappusers->getErrorsAsArray() )
            die( '<b>Getting User Error</b> - ' . $onappusers->getErrorsAsString() );            

        $baseresource  = $onapp->factory('BillingPlan_BaseResource', true );

        $baseresources = $baseresource->getList( $onappuser->_billing_plan_id );

        if ( $baseresource->getErrorsAsArray() )
            $errors[] = '<b>Getting Edge Groups Error</b> - '. $baseresource->getErrorsAsString();

        $available_edge_groups = $onapp->factory('CDNResource_AvailableEdgeGroup');

        $edge_group_baseresources = array();
        $edge_group_locations_ids = array();

        foreach ( $baseresources as $edge_group ) {
            if ( $edge_group->_resource_name == 'edge_group' )
            {
                $edge_group_baseresources[ $edge_group->_id ]['price']       = round( $edge_group->_prices->_price * $whmcs_client_details['currencyrate'], 2 );

                foreach ( $available_edge_groups->getList( $edge_group->_id ) as $group ) {
                    if ( $group->_id == $edge_group->_target_id ) {
                        foreach( $group->_edge_group_locations as $location ) {

                            $requirements =  '_'. $types[$type]['requirements'];
                            // if ( $location->$requirements ){ TODO uncomment as ticket is solved
                                $edge_group_baseresources[ $edge_group->_id ]['locations'][] = $location;
                                $edge_group_locations_ids[ $group->_id ][ $location->_aflexi_location_id ] = ucfirst($location->_city) . ', ' . $location->_country;
                            //}
                        }

                        $edge_group_baseresources[ $edge_group->_id ]['id']         = $group->_id;
                        $edge_group_baseresources[ $edge_group->_id ]['label']      = $group->_label;
                    }
                }
            }
        }

        return array(
            'edge_group_locations_ids'   => $edge_group_locations_ids,
            'edge_group_baseresources'   => $edge_group_baseresources,
            'whmcs_client_details'       => $whmcs_client_details,
            'errors'                     => $errors,
            'advanced_details'           => $advanced_details[0],
            'resource'                   => $resource,
            'edge_group_ids'             => $edge_group_ids,
        );
    }
    
    private function generate_passwords_html ( $passwords_array ) {
        
        $passwords_html = '';
        
        foreach ( $passwords_array as $user => $password) {
            $passwords_html .= '<tr>' . '\n' .
                    '<td>' . '\n' .
                    '<input class="username_input" value="' . $user . '" type="text" name="resource[form_pass][user][]" />' . '\n' .
                    '</td>' . '\n' .
                    '<td>' . '\n' .
                    '<input class="password_input" value="' . $password . '" type="text" name="resource[form_pass][pass][]" />' . '\n' .
                    '</td>' . '\n' .
                    '</tr>' . '\n';
        }
        return $passwords_html;
    }    
}

