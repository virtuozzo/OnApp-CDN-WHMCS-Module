<?php

$cacheDir = dirname( $_SERVER[ 'argv' ][ 0 ] ) . DIRECTORY_SEPARATOR;
$rootDir = dirname( dirname( dirname( dirname( dirname( dirname( $_SERVER[ 'argv' ][ 0 ] ) ) ) ) ) ) . DIRECTORY_SEPARATOR;

require_once $rootDir . 'dbconnect.php';
require_once $rootDir . 'includes/functions.php';
require_once $rootDir . 'includes/wrapper/OnAppInit.php';

$sql = 'SELECT
			h.id
		FROM
			tblhosting AS h
		LEFT JOIN
			tblproducts AS p ON h.packageid = p.id
		WHERE
			p.servertype = "onappcdn"';

$result = full_query( $sql );
$servicesIds = array();
if( $result && mysql_num_rows( $result ) ) {
	while( $row = mysql_fetch_assoc( $result ) ) {
		$servicesIds[ ] = $row[ 'id' ];
	}
}

foreach( $servicesIds as $service ) {
	$sql = "SELECT
				h.userid AS clientid,
				c.currency,
				currencies.prefix AS currencyprefix,
				currencies.code AS currencycode,
				currencies.rate AS currencyrate,
				currencies.default AS ifdefaultcurrency,
				cdnclients.onapp_user_id,
				h.server AS server_id
			FROM
				tblhosting AS h
			LEFT JOIN
				tblclients AS c
				ON h.userid = c.id
			LEFT JOIN
				tblcurrencies AS currencies
				ON currencies.id = c.currency
			LEFT JOIN
				tblonappcdnclients AS cdnclients
				ON cdnclients.service_id = $service
			WHERE
				h.id = $service";
	$result = full_query( $sql );
	$clientDetails = mysql_fetch_assoc( $result );

	if( ! $onapp = getOnAppInstance( $service ) ) {
		continue;
	}

	$onappUser = $onapp->factory( 'User' );
	$onappUser = $onappUser->load( $clientDetails[ 'onapp_user_id' ] );

	$baseresource = $onapp->factory( 'BillingPlan_BaseResource', true );
	$baseresources = $baseresource->getList( $onappUser->_billing_plan_id );

	foreach( $baseresources as $resource ) {
		if( $resource->_resource_name !== 'edge_group' ) {
			continue;
		}

		$cache = $cacheDir . 'edgegroup-' . $clientDetails[ 'server_id' ] . '-' . $resource->_target_id;

		$eg = $onapp->factory( 'EdgeGroup' );
		$g = $eg->load( $resource->_target_id );
		if( ! empty( $g->_assigned_locations ) ) {
			file_put_contents( $cache, serialize( $g ) );
		}
	}
}
die( PHP_EOL . PHP_EOL . 'DIE AT ' . __LINE__ );

// functions
function getOnAppInstance( $service ) {
	$user = getUser( $service );
	$server = getServer( $service );

	$onapp = new OnApp_Factory(
		$server[ 'address' ],
		$user[ 'email' ],
		$user[ 'password' ]
	);

	if( $onapp->getErrorsAsArray() ) {
		echo PHP_EOL . PHP_EOL . 'Get OnApp Version Permission Error:' . PHP_EOL . $onapp->getErrorsAsString();
		return false;
	}

	return $onapp;
}

function getUser( $service ) {
	$sql = 'SELECT
				*
			FROM
				tblonappcdnclients
			WHERE
				service_id = ' . $service;
	$resource = full_query( $sql );
	$user = mysql_fetch_assoc( $resource );
	return $user;
}

function getCDNServers() {
	$sql = 'SELECT
				*
			FROM
				tblservers
			WHERE
				type = "onappcdn"';
}

function getServer( $service ) {
	$sql = 'SELECT
				tblservers.*
			FROM
				tblhosting
			LEFT JOIN tblproducts ON
				tblproducts.id = packageid
			LEFT JOIN tblservers ON
				tblproducts.configoption1 = tblservers.id
			WHERE
				tblhosting.id = ' . $service;
	$resource = full_query( $sql );
	$server = mysql_fetch_assoc( $resource );

	if( ! is_null( $server ) ) {
		$server[ 'address' ] = $server[ 'ipaddress' ] != '' ? $server[ 'ipaddress' ] : $server[ 'hostname' ];

		if( $server[ 'secure' ] == 'on' ) {
			$server[ 'address' ] = 'https://' . $server[ 'address' ];
		}

		$server[ 'password' ] = decrypt( $server[ 'password' ] );
		return $server;
	}
	else {
		exit( 'OnApp CDN server for service ' . $service . ' not found.' );
	}
}