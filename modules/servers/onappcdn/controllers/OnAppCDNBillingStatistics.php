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
     * Display bandwidth statistics page
     *
     * @param string $errors error messages
     * @param string $messages messages
     */
    public function show($errors = null, $messages = null) {
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }
        
        $resource_id = parent::get_value('resource_id');
        $hosting_id = parent::get_value('id');

        $where = "WHERE hosting_id=$hosting_id AND cdn_resource_id=$resource_id";

        $quantity_query = "SELECT COUNT(*) as count FROM tblonappcdn_billing $where";

        $row = mysql_fetch_assoc( full_query( $quantity_query ) );

        $pages = new Paginator();

        $pages->items_total    = $row['count'];
        $pages->mid_range      = 5;
        $pages->paginate();

        $query = "
             SELECT
                *
             FROM
                tblonappcdn_billing
             $where
             ORDER BY
                stat_time
             DESC
             $pages->limit
         ";

        $result = full_query($query);

        $rows = array();

        while ( $row = mysql_fetch_assoc( $result ) ) {
            $row['formated_trafic'] = $this->byteFormat($row['traffic']);
            $rows[] = $row;
        }

        $this->show_template(
            'onappcdn/cdn_resources/billing_statistics',
            array(
                'id'                =>  parent::get_value('id'),
                'resource_id'       =>  $resource_id,
                'errors'            =>  implode( PHP_EOL, $errors ),
                'messages'          =>  implode( PHP_EOL, $messages ),
                'statistics'        =>  $rows,
                'pagination'        =>  $pages->display_pages(),
//                'jump_menu'         =>  $pages->display_jump_menu(),
                'items_per_page'    =>  $pages->display_items_per_page(),
            )
        );

    }
}

