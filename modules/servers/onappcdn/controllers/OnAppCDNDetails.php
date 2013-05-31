<?php
//error_reporting( E_ALL ^ E_NOTICE );
//error_reporting( E_ALL );
//ini_set( 'display_errors', 1 );
//ini_set( 'html_errors', 1 );
/**
 * Displays CDN Resources details
 */
class OnAppCDNDetails extends OnAppCDN {
	public function __construct() {
		parent::__construct();
		parent::init_wrapper();
	}

	/**
	 * Shows CDN Resources Details
	 *
	 * @param array $errors   Error Messages
	 * @param array $messages Messages
	 */
	public function show( $errors = null, $messages = null ) {
		global $_LANG;
		parent::loadCDNLanguage();

		if( ! parent::getValue( 'resource_id' ) ) {
			die( 'resource_id should be specified' );
		}

		$whmcsClientDetails = $this->getWhmcsClientDetails();

		$onapp = $this->getOnAppInstance();

		$resource_id = parent::getValue( 'resource_id' );
		$_resource = $onapp->factory( 'CDNResource', true );
		$resource = $_resource->load( $resource_id );

		$edge_group_ids = array();
		foreach( $resource->_edge_groups as $group ) {
			$edge_group_ids[ ] = $group->_id;
		}

		$onappusers = $onapp->factory( 'User', true );
		$onappuser = $onappusers->load( $resource->_user_id );

		$baseresource = $onapp->factory( 'BillingPlan_BaseResource', true );
		$baseresources = $baseresource->getList( $onappuser->_billing_plan_id );

		$eg = $onapp->factory( 'EdgeGroup' );
		$edge_group_baseresources = array();
		foreach( $baseresources as $edge_group ) {
			if( $edge_group->_resource_name == 'edge_group' && in_array( $edge_group->_target_id, $edge_group_ids ) ) {
				$edge_group_baseresources[ $edge_group->_id ][ 'price' ] = round( $edge_group->_prices->_price * $whmcsClientDetails[ 'currencyrate' ], 2 );

				foreach( $edge_group_ids as $gid ) {
					if( $gid == $edge_group->_target_id ) {
						$cache = 'edgegroup-' . $whmcsClientDetails[ 'server_id' ] . '-' . $gid;
						$group = $this->getCache( $cache );

						$edge_group_baseresources[ $edge_group->_id ][ 'locations' ] = $group->_assigned_locations;
						$edge_group_baseresources[ $edge_group->_id ][ 'id' ] = $group->_id;
						$edge_group_baseresources[ $edge_group->_id ][ 'label' ] = $group->_label;
					}
				}
			}
		}

		if( isset( $_SESSION[ 'successmessages' ] ) ) {
			$messages[ ] = $_SESSION[ 'successmessages' ];
			unset( $_SESSION[ 'successmessages' ] );
		}

		if( isset( $_SESSION[ 'errors' ] ) ) {
			$errors[ ] = $_SESSION[ 'errors' ];
			unset( $_SESSION[ 'errors' ] );
		}

		if( ! isset( $resource->_aflexi_resource_id ) ) {
			$resource->_aflexi_resource_id = (int)$resource->cname;
		}

		if( ! isset( $resource->_origins_for_api ) ) {
			$resource->_origins_for_api = $resource->_origins;
		}

		$this->showTemplate(
			'onappcdn/cdn_resources/details',
			array(
				 'whmcs_client_details'     => $this->getWhmcsClientDetails(),
				 'id'                       => parent::getValue( 'id' ),
				 'edge_group_baseresources' => $edge_group_baseresources,
				 'resource_id'              => $resource_id,
				 'resource'                 => $resource,
				 'errors'                   => @implode( PHP_EOL, $errors ),
				 'messages'                 => @implode( PHP_EOL, $messages ),
				 'ssl_mode'                 => ( boolean )strpos( $resource->_cdn_hostname, 'worldssl' )
			)
		);
	}
}