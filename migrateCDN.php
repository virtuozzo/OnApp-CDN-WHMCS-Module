<?php

require_once __DIR__ . '/init.php';
require_once __DIR__ . '/configuration.php';
require_once __DIR__ . '/includes/onappWrapper/utility.php';

error_reporting( E_ALL ^ E_NOTICE );
ini_set( 'display_errors', 1 );

$configOptionsCache = null;
$dsn = 'mysql:dbname=' . $db_name . ';host=' . $db_host;
$db = new PDO( $dsn, $db_username, $db_password );

# get configurable options
getConfigOptions();

# get CDN clients
$sql = 'SELECT
			tblhosting.`userid` AS WHMCSUserID,
			tblonappcdnclients.`onapp_user_id` AS OnAppUserID,
			tblonappcdnclients.`email` AS OnAppLogin,
			tblonappcdnclients.`password` AS OnAppPassword,
			tblonappcdnclients.`service_id` AS WHMCSServiceID,
			tblproducts.`name` AS OldModName
		FROM
			`tblhosting`
		JOIN
			`tblonappcdnclients`
			ON `tblhosting`.`id` = tblonappcdnclients.`service_id`
		JOIN
			`tblproducts`
			ON tblproducts.`id` = tblhosting.`packageid`';

/**
 * @var User $row
 */
foreach( $db->query( $sql, PDO::FETCH_CLASS, 'User' ) as $row ) {
	# update service
	$sql = 'UPDATE
				tblhosting
			SET
				`packageid` = :pid,
				`username` = :username,
				`password` = :pass';
	$stm = $db->prepare( $sql );
	$stm->bindValue( ':pid', $pid = getNewProductID( $row->OldModName ) );
	$stm->bindValue( ':pass', encrypt( $row->OnAppPassword ) );
	$stm->bindParam( ':username', $row->OnAppLogin );
	$stm->execute();

	onapp_addCustomFieldValue( 'cdnuserid', $pid, $row->WHMCSServiceID, $row->OnAppUserID );

	# assign configurable option to service
	$sql = 'INSERT INTO
				`tblhostingconfigoptions`
			VALUES (
				NULL,
				:serviceID,
				:configID,
				:optionID,
				0
			)';
	$stm = $db->prepare( $sql );
	$stm->bindValue( ':serviceID', $row->WHMCSServiceID );
	$stm->bindValue( ':configID', $configOptionsCache[ $pid ]->configid );
	$stm->bindParam( ':optionID', $configOptionsCache[ $pid ]->id );
	$stm->execute();
}

function getNewProductID( $name ) {
	global $db;
	$sql = 'SELECT
				`id`
			FROM
				`tblproducts`
			WHERE
				`name` = BINARY :name
				AND `servertype` = BINARY "onappCDN"';
	$stm = $db->prepare( $sql );
	$stm->bindParam( ':name', $name );
	$stm->execute();
	return $stm->fetch( PDO::FETCH_COLUMN );
}

function getConfigOptions() {
	global $db;
	global $configOptionsCache;
	$sql = 'SELECT
				tblproductconfigoptionssub.`id`,
				tblproductconfigoptionssub.`configid`,
				tblproductconfiglinks.`pid`
				-- *
			FROM
				`tblproductconfiggroups`
			JOIN
				`tblproductconfigoptions`
				ON tblproductconfiggroups.`id` = tblproductconfigoptions.`gid`
			JOIN
				`tblproductconfigoptionssub`
				ON tblproductconfigoptionssub.`configid` = tblproductconfigoptions.`id`
			JOIN
				`tblproductconfiglinks`
				ON tblproductconfiggroups.`id` = tblproductconfiglinks.`gid`
			WHERE
				tblproductconfiggroups.`name` = "Configurable options for onAppCDN"
				AND tblproductconfigoptionssub.`optionname` = "20|20 Resources"';
	foreach( $db->query( $sql, PDO::FETCH_CLASS, 'stdClass' ) as $row ) {
		$configOptionsCache[ $row->pid ] = $row;
	}
}

class User extends stdClass {
	public $WHMCSUserID;
	public $OnAppUserID;
	public $OnAppLogin;
	public $OnAppPassword;
	public $WHMCSServiceID;
	public $OldModName;
}