<?php
/**
 * Manages CDN Resource Bandwidth Statistics
 */
class OnAppCDNBillingStatistics extends OnAppCDN {

    public function __construct() {
        require_once dirname(__FILE__) . '/../class_paginator.php';
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     * Display billing statistics page
     *
     * @param string $errors error messages
     * @param string $messages messages
     */
    public function show($errors = null, $messages = null) {
        
        $whmcs_client_details = $this->getWhmcsClientDetails();
        
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
        while( $invoices = mysql_fetch_assoc( $invoices_result ) ) {
            if ( $invoices['status'] == 'Paid'){
                $invoices_data['paid'] = $invoices['amount'];
            } else {
               $invoices_data['unpaid'] = $invoices['amount']; 
            }
        }

        $total_amount_query= "
            SELECT 
                SUM( ( price * currency_rate )  * ( cached + non_cached ) ) as total 
            FROM 
                tblonappcdn_bandwidth 
            $where 
            GROUP BY 
                hosting_id";
        
        $total_row = mysql_fetch_assoc( full_query( $total_amount_query ) );
        
        $quantity_query = "SELECT COUNT(*) as count FROM tblonappcdn_bandwidth $where";

        $row = mysql_fetch_assoc( full_query( $quantity_query ) );

        $pages = new Paginator();

        $pages->items_total    = $row['count'];
        $pages->mid_range      = 5;
        $pages->paginate();

        $query = "
             SELECT
                *
             FROM
                tblonappcdn_bandwidth
             $where
             ORDER BY
                created_at
             DESC
             $pages->limit
         ";

        $result = full_query($query);

        $rows = array();

        $i = 0;
        while ( $row = mysql_fetch_assoc( $result ) ) {
            $rows[$i]                 = $row;
            $rows[$i]['traffic']      = round( $rows[$i]['cached']  + $rows[$i]['non_cached'], 4);
            $rows[$i]['localprice']   = $rows[$i]['price']   * $rows[$i]['currency_rate']; 
            $rows[$i]['cost']         = round( $rows[$i]['traffic'] * $rows[$i]['localprice'], 4 );
            $i++;
        }
        
        $not_invoiced = round( $total_row['total'] - $invoices_data['paid'] - $invoices_data['unpaid'], 2);
        
        $this->show_template(
            'onappcdn/cdn_resources/billing_statistics',
            array(
                'id'                   =>  parent::get_value('id'),
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

