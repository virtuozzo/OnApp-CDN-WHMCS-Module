<?php
/**
 * Manages CDN Resource Billling Statistics
 */
class OnAppCDNBillingStatistics extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     * Display statistics page
     *
     * @param string $errors error messages
     * @param string $messages messages
     */
    public function show( $errors = null, $messages = null ) {

        $whmcs_client_details  =  $this->getWhmcsClientDetails();

        $onapp = $this->getOnAppInstance();

        $resource_id = parent::get_value( 'resource_id' );

        $_resource  = $onapp->factory('CDNResource', true );

        $resource_id = parent::get_value( 'resource_id' );

        $resource   = $_resource->load( $resource_id );

        $onappusers    = $onapp->factory('User', true );
        $onappuser     = $onappusers->load( $resource->_user_id );

        $baseresource  = $onapp->factory('BillingPlan_BaseResource', true );
        $baseresources = $baseresource->getList( $onappuser->_billing_plan_id );

        foreach ( $baseresources as $_baseresource ) {
            if ( $_baseresource->_resource_name == 'edge_group') {
                $edge_group_info[$_baseresource->_target_id]['label'] = $_baseresource->_label;
                $edge_group_info[$_baseresource->_target_id]['price'] = $_baseresource->_prices[0]->_price;
            }
        }

        $page_number = parent::get_value( 'page_number' ) ? parent::get_value('page_number') : 1;

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $_statistics  = $onapp->factory('CDNResource_BillingStatistic', true );

        $url_args = array(
            'page' => $page_number,
        );

        $statistics = $_statistics->getList( $resource_id, $url_args  );

        foreach ( $statistics as $__statistics ) {
            $statistics_array[$__statistics->_id]['date']    = str_replace( array('T', 'Z'), ' ', $__statistics->_created_at);
            $statistics_array[$__statistics->_id]['label']  = $edge_group_info[$__statistics->_edge_group_id]['label'];
            $statistics_array[$__statistics->_id]['traffic'] = $__statistics->_data_cached + $__statistics->_data_non_cached;
            $statistics_array[$__statistics->_id]['cost']    = 
                $whmcs_client_details['currencyprefix'] .
                round ( ( ( ( $edge_group_info[$__statistics->_edge_group_id]['price'] *
                $statistics_array[$__statistics->_id]['traffic'] ) /
                1000000000 ) * $whmcs_client_details['currencyrate'] ), 2 ) . ' ' .
                $whmcs_client_details['currencycode'] ;
        }

        $this->show_template(
            'onappcdn/cdn_resources/billing_statistics',
            array(
                'id'                =>  parent::get_value('id'),
                'page_number'       =>  $page_number,
                'statistics'        =>  $statistics_array,
                'resource_id'       =>  $resource_id,
                'page_number'       =>  $page_number,
                'errors'            =>  implode( PHP_EOL, $errors ),
                'messages'          =>  implode( PHP_EOL, $messages ),
            )
        );
    }
}

