<?php
/**
 * Displays CDN Resources details
 * 
 */
class OnAppCDNDetails extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     * Shows CDN Resources Details
     *
     * @param array $errors Error Messages
     * @param array $messages Messages
     */
    public function show( $errors = null, $messages = null ) {
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }        

        global $_LANG;
        $whmcs_client_details  =  $this->getWhmcsClientDetails();
        
        parent::loadcdn_language();

        $onapp = $this->getOnAppInstance();

        $_resource  = $onapp->factory('CDNResource', true );

        $resource_id = parent::get_value( 'resource_id' );

        $resource   = $_resource->load( $resource_id );

        $edge_group_ids = array();
        foreach( $resource->_edge_groups as $group ) {
            $edge_group_ids[] = $group->_id;
        }

        $onappusers    = $onapp->factory('User', true );
        $onappuser     = $onappusers->load( $resource->_user_id );

        $baseresource  = $onapp->factory('BillingPlan_BaseResource', true );
        $baseresources = $baseresource->getList( $onappuser->_billing_plan_id );

        $available_edge_groups = $onapp->factory('CDNResource_AvailableEdgeGroup');

        $edge_group_baseresources = array();
        foreach ( $baseresources as $edge_group ) {
            if ( $edge_group->_resource_name == 'edge_group' &&
                 in_array( $edge_group->_target_id, $edge_group_ids )
            ) {
                $edge_group_baseresources[ $edge_group->_id ][price] = round( $edge_group->_prices->_price * $whmcs_client_details['currencyrate'], 2 );

                foreach ( $available_edge_groups->getList( $edge_group->_id ) as $group ) {
                    if ( $group->_id == $edge_group->_target_id ) {
                        $edge_group_baseresources[ $edge_group->_id ][locations]  = $group->_edge_group_locations;
                        $edge_group_baseresources[ $edge_group->_id ][id]         = $group->_id;
                        $edge_group_baseresources[ $edge_group->_id ][label]      = $group->_label;
                    }
                }
            }
        }

        if ( isset( $_SESSION['successmessages'] ) ) {
            $messages[] = $_SESSION['successmessages'];
            unset( $_SESSION['successmessages'] );
        }

        if (isset($_SESSION['errors'])) {
            $errors[] = $_SESSION['errors'];
            unset($_SESSION['errors']);
        }
        
        $this->show_template(
            'onappcdn/cdn_resources/details',
            array(
                'whmcs_client_details'      =>  $this->getWhmcsClientDetails(),
                'id'                        =>  parent::get_value('id'),
                'edge_group_baseresources'  =>  $edge_group_baseresources,
                'resource_id'               =>  $resource_id,
                'resource'                  =>  $resource,
                'errors'                    =>  implode( PHP_EOL, $errors ),
                'messages'                  =>  implode( PHP_EOL, $messages ),
                'ssl_mode'                  =>  ( boolean )strpos( $resource->_cdn_hostname, 'worldssl' )
            )
        );
    }
}
