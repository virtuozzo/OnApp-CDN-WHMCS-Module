<?php
/**
 * Manages CDN Resource Bandwidth Statistics
 */
class OnAppCDNBandwidthStatistics extends OnAppCDN {

    public function __construct() {
        require_once dirname(__FILE__) . '/../class_paginator.php';
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     * Display bandwidth statistics page
     *
     * @param string $errors error messages
     * @param string $messages messages
     */
    public function show($errors = null, $messages = null) {
        $resource_id = parent::get_value('resource_id');
        $hosting_id = parent::get_value('id');

        $where = "WHERE hosting_id=$hosting_id AND resource_id=$resource_id";

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

        while ( $row = mysql_fetch_assoc( $result ) ) {
            $rows[] = $row;
        }

        $this->show_template(
            'onappcdn/cdn_resources/bandwidth_statistics',
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

