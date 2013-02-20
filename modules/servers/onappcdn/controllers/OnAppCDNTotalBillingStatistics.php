<?php
/**
 * Manages CDN Resource Bandwidth Statistics
 */
class OnAppCDNTotalBillingStatistics extends OnAppCDN {

    public function __construct() {
        require_once dirname(__FILE__) . '/../class_paginator.php';
        parent::__construct();
        parent::init_wrapper();
    }

    private function byteFormat($bytes, $unit = "", $decimals = 3) {
        $units = array(
            'B' => 0,
            'KB' => 1,
            'MB' => 2,
            'GB' => 3,
            'TB' => 4,
            'PB' => 5,
            'EB' => 6,
            'ZB' => 7,
            'YB' => 8
        );

        $value = 0;
        if ($bytes > 0) {

            // Generate automatic prefix by bytes 
            // If wrong prefix given
            if (!array_key_exists($unit, $units)) {
                $pow = floor(log($bytes)/log(1000));
                $unit = array_search($pow, $units);
            }

            // Calculate byte value by prefix
            $value = ($bytes/pow(1000,floor($units[$unit])));
        }

        // If decimals is not numeric or decimals is less than 0 
        // then set default value
        if (!is_numeric($decimals) || $decimals < 0) {
            $decimals = 2;
        }

         // Format output
         return sprintf('%.' . $decimals . 'f '.$unit, $value);
    }


    /**
     * Display billing statistics page
     *
     * @param string $errors error messages
     * @param string $messages messages
     */
    public function show($errors = null, $messages = null) {
        
        $resources = array();
        
        $whmcs_client_details = $this->getWhmcsClientDetails();
        
        $onapp = $this->getOnAppInstance();
        $resource = $onapp->factory('CDNResource');
        
        foreach( $resource->getList() as $_resource ){
            if ( $whmcs_client_details['onapp_user_id'] == $_resource->_user_id ) {
                $resources[ $_resource->_id ] = $_resource;
            }
        }
        
        $hosting_id = parent::get_value('id');

        $where = "WHERE hosting_id=$hosting_id";
        
        $invoices_query =
            "SELECT
                SUM( i.subtotal ) AS amount,
                status
            FROM
                tblinvoices as i
            WHERE
                i.userid = $whmcs_client_details[clientid]
                AND i.notes = $hosting_id
            GROUP BY      
                i.notes, status
            ";
        
        $invoices_result = full_query( $invoices_query );
        
        $invoices_data = array();
        $invoices_data['paid']= 0;
        $invoices_data['unpaid'] = 0;
        
        while( $invoices = mysql_fetch_assoc( $invoices_result ) ) {
            if ( $invoices['status'] == 'Paid'){
                $invoices_data['paid'] = $invoices['amount'];
            } else {
               $invoices_data['unpaid'] = $invoices['amount']; 
            }
        }

        $total_amount_query= "
            SELECT 
                SUM( cost * currency_rate ) as total 
            FROM 
                tblonappcdn_billing 
            $where 
        ";
        
        $total_row = mysql_fetch_assoc( full_query( $total_amount_query ) );
        
        $quantity_query = "SELECT COUNT( DISTINCT cdn_resource_id ) as count FROM tblonappcdn_billing $where";

        $row = mysql_fetch_assoc( full_query( $quantity_query ) );

        $pages = new Paginator();

        $pages->items_total    = $row['count'];
        $pages->mid_range      = 5;
        $pages->paginate();

        $query = "
             SELECT
                SUM( cost * currency_rate ) as price,
                SUM( traffic )              as traffic,
                cdn_resource_id
             FROM
                tblonappcdn_billing
             $where
             GROUP BY
                cdn_resource_id
             $pages->limit
         ";

        $result = full_query($query);

        $rows = array();

        while ( $row = mysql_fetch_assoc( $result ) ) {
            $rows[$row['cdn_resource_id']] = $row;
            $rows[$row['cdn_resource_id']]['formated_traffic'] = $this->byteFormat($rows[$row['cdn_resource_id']]['traffic']);
        }

        $not_invoiced = round( $total_row['total'] - $invoices_data['paid'] - $invoices_data['unpaid'], 2);
        
        $this->show_template(
            'onappcdn/cdn_resources/total_billing_statistics',
            array(
                'id'                   =>  parent::get_value('id'),
                'resources'            =>  $resources, 
                'errors'               =>  implode( PHP_EOL, $errors ),
                'messages'             =>  implode( PHP_EOL, $messages ),
                'statistics'           =>  $rows,
                'pagination'           =>  $pages->display_pages(),
//                'jump_menu'         =>  $pages->display_jump_menu(),
                'items_per_page'       =>  $pages->display_items_per_page(),
                'total'                =>  round( $total_row['total'], 2),
                'whmcs_client_details' =>  $this->getWhmcsClientDetails(), 
                'invoices_data'        =>  $invoices_data,
                'not_invoiced_amount'  =>  $not_invoiced,
            )
        );
    }
}

