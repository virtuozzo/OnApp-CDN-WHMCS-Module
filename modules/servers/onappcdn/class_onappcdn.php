<?php

if( ! function_exists( 'emailtpl_template' ) ) {
	require_once realpath( dirname( __FILE__ ) . '/../../../includes/functions.php' );
}

class OnAppCDN {
	public  $error;
	private $salt = 'ec457d0a974c48d5685a7efa03d137dc8bbde7e3';
	private $hostname;
	private $serviceid;
	private $created;
	private $server;
	private $user;
	private $onapp;
	protected $cacheDir;

	function __construct( $serviceid = null ) {
		$this->hostname = $_SERVER[ 'HTTP_HOST' ];
		$this->cacheDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;

		if( is_null( $serviceid ) ) {
			$serviceid = self::getValue( 'id' );
		}
		if( ! is_numeric( $serviceid ) ) {
			die( 'Invalid token' );
		}

		$this->serviceid = $serviceid;
	}

	public function getUser() {
		if( ! is_null( $this->user ) ) {
			return $this->user;
		}

		$sql = 'SELECT
					*
				FROM
					tblonappcdnclients
				WHERE
					service_id = ' . $this->serviceid;
		$resource = full_query( $sql );
		$user = mysql_fetch_assoc( $resource );

		$this->user = $user;
		return $user;
	}

	protected function getWhmcsClientDetails() {
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
					ON cdnclients.service_id = $this->serviceid
				WHERE
					h.id = $this->serviceid";
		$result = full_query( $sql );
		$client_details = mysql_fetch_assoc( $result );

		return $client_details;
	}

	protected function getServer() {
		if( ! is_null( $this->server ) ) {
			return $this->server;
		}
		$sql = 'SELECT
                tblservers.*
            FROM
                tblhosting
                LEFT JOIN tblproducts ON tblproducts.id = packageid
                LEFT JOIN tblservers ON tblproducts.configoption1 = tblservers.id
            WHERE
                tblhosting.id = ' . $this->serviceid;
		$resource = full_query( $sql );
		$server = mysql_fetch_assoc( $resource );

		if( ! is_null( $server ) ) {
			$server[ 'address' ] = $server[ 'ipaddress' ] != '' ? $server[ 'ipaddress' ] : $server[ 'hostname' ];

			if( $server[ 'secure' ] == 'on' ) {
				$server[ 'address' ] = 'https://' . $server[ 'address' ];
			}

			$server[ 'password' ] = decrypt( $server[ 'password' ] );

			$this->server = $server;
			return $this->server;
		}
		else {
			exit( 'OnApp CDN server for service ' . $this->serviceid . ' not found.' );
		}
	}

	public function createUser() {
		$server = $this->getServer();
		$service_id = $this->serviceid;

		$onapp = new OnApp_Factory(
			$server[ 'address' ],
			$server[ 'username' ],
			$server[ 'password' ]
		);

		if( ! $onapp->_is_auth ) {
			return 'Can not login as "' . $server[ 'username' ] . '" on ' . $server[ 'address' ];
		}
		else {
			if( $onapp->getErrorsAsArray() ) {
				return 'Getting OnApp Version Error: ' . $onapp->getErrorsAsString( ', ' );
			}
			else {
				$sql = 'SELECT
							tblproducts.*
						FROM
							tblhosting
							LEFT JOIN tblproducts ON tblproducts.id = packageid
						WHERE
							tblhosting.id = ' . $this->serviceid;

				$resource = full_query( $sql );
				$hosting = mysql_fetch_assoc( $resource );

				if( ! $hosting ) {
					return "Can't execute SQL query \"$sql\"";
				}

				$hostname = $this->hostnameShort( $this->hostname ) . '.cdn';

				$email = 'cdnuser' . $this->serviceid . '@' . $hostname;

				$password = md5( $this->serviceid . $this->salt . date( 'h-i-s, j-m-y' ) );

				$user = $onapp->factory( 'User', true );

				$user->_email = $email;
				$user->_password = $password;
				$user->_login = $email;
				$user->_first_name = 'WHMCS CDN User';
				$user->_last_name = '#' . $this->serviceid;

				$user->_role_ids = explode( ',', $hosting[ 'configoption2' ] );
				$user->_user_group_id = $hosting[ 'configoption4' ];
				$user->_billing_plan_id = $hosting[ 'configoption3' ];
				$user->_time_zone = $hosting[ 'configoption5' ];

				$user->save();

				if( $user->getErrorsAsArray() ) {
					return $user->getErrorsAsString( ', ' );
				}
				else {
					$user_id = $user->_id;

					$sql = "REPLACE tblonappcdnclients SET
                    service_id    = '$service_id' ,
                    server_id     = '" . $server[ 'id' ] . "',
                    email         = '$email',
                    password      = '$password',
                    onapp_user_id = '$user_id'";

					if( ! full_query( $sql ) ) {
						return "Can't execute SQL query \"$sql\"";
					}
					else {
						$this->created = true;
						return 'success';
					}
				}
			}
		}

		return 'Something went wrong';
	}

	private function hostnameShort( $hostname ) {
		$length = strlen( $hostname );

		if( ! strpos( $hostname, '.' ) ) {
			$hostname .= '.com';
		}

		if( $length < 24 ) {
			return $hostname;
		}

		if( substr_count( $hostname, '.' ) > 1 ) {
			$hostname = substr( $hostname, strpos( $hostname, '.' ) - 1 );
		}

		return ( $length < 24 ) ? $hostname : substr( $hostname, $length - 24 );
	}

	public function delete_user() {
		$server = $this->getServer();

		$onapp = new OnApp_Factory(
			$server[ 'address' ],
			$server[ 'username' ],
			$server[ 'password' ]
		);

		if( ! $onapp->_is_auth ) {
			return "Can not login as '" . $server[ 'username' ] . "' on '" . $server[ 'address' ];
		}
		else {
			if( $onapp->getErrorsAsArray() ) {
				return 'Getting OnApp Version Error: ' . $onapp->getErrorsAsString( ', ' );
			}
			else {
				$user = $this->getUser();

				$onapp_user = $onapp->factory( 'User', true );

				$onapp_user->_id = $user[ 'onapp_user_id' ];

				$onapp_user->delete( 1 );

				if( $onapp_user->getErrorsAsArray() ) {
					return $onapp_user->getErrorsAsString( ', ' );
				}
				else {
					$sql = "DELETE FROM tblonappcdnclients WHERE service_id = " . $this->serviceid;

					if( ! full_query( $sql ) ) {
						return "Can't execute SQL query \"$sql\"";
					}
					else {
						return 'success';
					}
				}
			}
		}

		return 'Something went wrong';
	}

	public function suspend_user() {
		$server = $this->getServer();

		$onapp = new OnApp_Factory(
			$server[ 'address' ],
			$server[ 'username' ],
			$server[ 'password' ]
		);

		if( ! $onapp->_is_auth ) {
			return "Can not login as '" . $server[ 'username' ] . "' on '" . $server[ 'address' ];
		}
		else {
			if( $onapp->getErrorsAsArray() ) {
				return 'Getting OnApp Version Error: ' . $onapp->getErrorsAsString( ', ' );
			}
			else {
				$user = $this->getUser();

				$onapp_user = $onapp->factory( 'User', true );

				$onapp_user->load( $user[ 'onapp_user_id' ] );

				if( $onapp_user->_obj->_status == 'suspended' ) {
					return 'User is already suspended';
				}

				$onapp_user->suspend();

				if( $onapp_user->getErrorsAsArray() ) {
					return $onapp_user->getErrorsAsString( ', ' );
				}
				else {
					return 'success';
				}
			}
		}
		return 'Something went wrong';
	}

	public function unsuspend_user() {
		$server = $this->getServer();

		$onapp = new OnApp_Factory(
			$server[ 'address' ],
			$server[ 'username' ],
			$server[ 'password' ]
		);

		if( ! $onapp->_is_auth ) {
			return "Can not login as '" . $server[ 'username' ] . "' on '" . $server[ 'address' ];
		}
		else {
			if( $onapp->getErrorsAsArray() ) {
				return 'Getting OnApp Version Error: ' . $onapp->getErrorsAsString( ', ' );
			}
			else {
				$user = $this->getUser();

				$onapp_user = $onapp->factory( 'User', true );

				$onapp_user->load( $user[ 'onapp_user_id' ] );

				if( $onapp_user->_obj->_status != 'suspended' ) {
					return 'User is not suspended';
				}

				$onapp_user->activate_user();

				if( $onapp_user->getErrorsAsArray() ) {
					return $onapp_user->getErrorsAsString( ', ' );
				}
				else {
					return 'success';
				}
			}
		}
		return 'Something went wrong';
	}

	/**
	 * Get GET or POST value
	 */
	public static function getValue( $value ) {
		return isset( $_GET[ $value ] ) ? $_GET[ $value ] : ( isset( $_POST[ $value ] ) ? $_POST[ $value ] : null );
	}

	/**
	 * Init OnApp PHP wrapper
	 */
	public static function init_wrapper() {
		global $customadminpath;

		if( ! defined( 'ONAPP_FILE_NAME' ) ) {
			define( 'ONAPP_FILE_NAME', 'onappcdn.php' );
		}

		if( ! defined( 'ONAPP_WRAPPER_INIT' ) ) {
			if( defined( 'ROOTDIR' ) ) {
				define( 'ONAPP_WRAPPER_INIT', ROOTDIR . '/includes/wrapper/OnAppInit.php' );
			}
			else {
				exit( 'ROOTDIR not defined' );
			}
		}
		//echo'<pre>',ONAPP_WRAPPER_INIT;	print_r(get_defined_constants(1));exit(' die at ' . __LINE__ );
		//echo'<pre>',ONAPP_WRAPPER_INIT;//exit(' die at ' . __LINE__ );

		if( file_exists( ONAPP_WRAPPER_INIT ) ) {
			require_once ONAPP_WRAPPER_INIT;
		}
		else {
			return false;
		}

		return true;
	}

	/**
	 * Load $_LANG from language file
	 */
	public static function loadCDNLanguage() {
		global $_LANG, $CONFIG;

		$currentDir = getcwd();
		chdir( dirname( __FILE__ ) . '/lang/' );
		$availableLangs = glob( '*.txt' );

		$language = isset( $_SESSION[ 'Language' ] ) ? $_SESSION[ 'Language' ] : $CONFIG[ 'Language' ];
		$language = ucfirst( $language ) . '.txt';

		if( ! in_array( $language, $availableLangs ) ) {
			$language = 'English.txt';
		}

		$templang = file_get_contents( dirname( __FILE__ ) . '/lang/' . $language );
		eval ( $templang );
		chdir( $currentDir );
	}

	/**
	 * Create base OnApp CDN module tables structure
	 */
	public static function createTables() {
		global $_LANG, $whmcsmysql;

		define ( "CREATE_TABLE_CDNCLIENTS",
		'CREATE TABLE IF NOT EXISTS `tblonappcdnclients` (
		`server_id` INT( 11 ) NOT NULL,
		`service_id` INT( 11 ) NOT NULL,
		`onapp_user_id` INT( 11 ) NOT NULL,
		`password` TEXT NOT NULL,
		`email` TEXT NOT NULL,
		PRIMARY KEY( `server_id`, `service_id` ),
		KEY `client_id` ( `service_id` )
		) ENGINE = InnoDB;' );

		if( ! full_query( CREATE_TABLE_CDNCLIENTS, $whmcsmysql ) ) {
			return array(
				"error" => sprintf( $_LANG[ "onappcdnerrtablecreate" ], 'onappcdnclients' )
			);
		}

		return;
	}

	/**
	 * Get list of onapp cdn servers
	 */
	public static function getservers() {
		$product_id = OnAppCDN::getValue( 'id' );

		$sql = "SELECT
					tblservers.*,
					tblservergroupsrel.groupid,
					tblproducts.servergroup
				FROM
					tblservers
					LEFT JOIN tblservergroupsrel ON
						tblservers.id = tblservergroupsrel.serverid
					LEFT JOIN tblproducts ON
						tblproducts.id = $product_id
				WHERE
					tblservers.type = 'onappcdn'";

		$sql_servers_result = full_query( $sql );

		$servers = array();
		while( $server = mysql_fetch_assoc( $sql_servers_result ) ) {
			if( is_null( $server[ 'groupid' ] ) ) {
				$server[ 'groupid' ] = 0;
			}
			$server[ 'password' ] = decrypt( $server[ 'password' ] );
			$servers[ $server[ 'id' ] ] = $server;
			$servers[ $server[ 'id' ] ][ 'address' ] = $server[ 'ipaddress' ] != '' ? $server[ 'ipaddress' ] : $server[ 'hostname' ];

			if( $server[ 'secure' ] == 'on' ) {
				$servers[ $server[ 'id' ] ][ 'address' ] = 'https://' . $servers[ $server[ 'id' ] ][ 'address' ];
			}
		}

		return $servers;
	}

	public function show() {
		exit( 'You need to define function show in class' . __CLASS__ );
	}

	/**
	 * Renders clientarea view
	 *
	 * @global <type> $_LANG
	 * @global <type> $breadcrumbnav
	 * @global <type> $smartyvalues
	 * @global <type> $CONFIG
	 *
	 * @param  <type> $templatefile
	 * @param  <type> $values
	 */
	protected function showTemplate( $templatefile, $values ) {
		global $_LANG, $smartyvalues, $CONFIG;
		self::loadCDNLanguage();

		$id = self::getValue( 'id' );
		$page = self::getValue( 'page' );

		$breadcrumbnav = ' <a href="index.php">' . $_LANG[ 'globalsystemname' ] . '</a>';
		$breadcrumbnav .= ' &gt; <a href="clientarea.php">' . $_LANG[ 'clientareatitle' ] . '</a>';
		$breadcrumbnav .= ' &gt; <a href="' . ONAPP_FILE_NAME . '?page=resources&id=' . $id . '">' . $_LANG[ 'onappcdnresources' ] . '</a>';
		if( $page != 'resources' ) {
			$breadcrumbnav .=
					' &gt; <a href="' . ONAPP_FILE_NAME . '?page=' . $page . '&id=' .
							$id . '&resource_id=' . self::getValue( 'resource_id' ) . '">'
							. $_LANG[ 'onappcdn' . str_replace( '_', '', $page ) ] . '</a>';
		}

		$pagetitle = $_LANG[ 'clientareatitle' ];
		$pageicon = 'images/support/clientarea.gif';
		$values[ '_LANG' ] = $_LANG;
		initialiseClientArea( $pagetitle, $pageicon, $breadcrumbnav );

		$smartyvalues = $values;

		if( $CONFIG[ 'SystemSSLURL' ] ) {
			$smartyvalues[ 'systemurl' ] = $CONFIG[ 'SystemSSLURL' ] . '/';
		}
		else {
			if( $CONFIG[ 'SystemURL' ] != 'http://www.yourdomain.com/whmcs' ) /* Do not change this URL!!! - Otherwise WHMCS Failed ! */ {
				$smartyvalues[ 'systemurl' ] = $CONFIG[ 'SystemURL' ] . '/';
			}
		}

		outputClientArea( $templatefile );
	}

	/**
	 * Gets Services Ids of currently loged in user
	 *
	 * @return array User's servises Ids
	 */
	public function getUserServisesIds() {
		$userid = $_SESSION[ 'uid' ];

		if( ! isset( $userid ) || ! is_numeric( $userid ) ) {
			return array();
		}

		$sql = "SELECT
					h.id
				FROM
					tblhosting AS h
				LEFT JOIN
					tblproducts AS p ON h.packageid = p.id
				WHERE
					h.userid = $userid AND
					p.servertype = 'onappcdn'";

		$result = full_query( $sql );

		if( ! $result && mysql_num_rows( $result ) < 1 ) {
			return array();
		}

		$servicesIds = array();
		while( $row = mysql_fetch_assoc( $result ) ) {
			$servicesIds[ ] = $row[ 'id' ];
		}

		return $servicesIds;
	}

	/**
	 * Launchs particular action
	 *
	 * @param string $action
	 */
	public function runAction( $action ) {
		$this->$action();
	}

	/**
	 * Gets OnApp class instance
	 *
	 * @return object OnApp Instance
	 */
	protected function getOnAppInstance() {
		$user = $this->getUser();
		$server = $this->getServer();

		$this->onapp = new OnApp_Factory( $server[ 'address' ], $user[ 'email' ], $user[ 'password' ] );

		if( $this->onapp->getErrorsAsArray() ) {
			exit( '<b>Get OnApp Version Permission Error: </b>' . PHP_EOL . $this->onapp->getErrorsAsString() );
		}

		return $this->onapp;
	}

	/**
	 *
	 */
	public function getServiceId() {
		return $this->serviceid;
	}

	/**
	 * Redirect to another page
	 *
	 * @param string $url redirection url
	 */
	public function redirect( $url ) {
		if( ! headers_sent() ) {
			header( 'Location: ' . $url );
			exit;
		}
		else {
			echo '<script type="text/javascript">';
			echo 'window.location.href="' . $url . '";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
			echo '</noscript>';
			exit;
		};
	}

	/**
	 *  Verify if user have access to particular cdn_resource
	 *
	 * @param integer $resource_id CDN resource id
	 */
	public function ifHaveAccessToResource( $resource_id ) {
		$onapp = $this->getOnAppInstance();

		$resources = $onapp->factory( 'CDNResource' );
		$list = $resources->getList();

		if( $resources->getErrorsAsArray() ) {
			exit( '<b>Getting Resource Error: </b>' . PHP_EOL . $resources->getErrorsAsString() );
		}

		$resource_ids = array();
		foreach( $list as $resource ) {
			$resource_ids[ ] = $resource->_id;
		}

		if( ! in_array( $resource_id, $resource_ids ) ) {
			die( 'Invalid Token ( code : 5 )' );
		}

		return true;
	}

	public function getCache( $name ) {
		$name = $this->cacheDir . $name;

		if( ! file_exists( $name ) ) {
			return false;
		}
		else {
			return unserialize( file_get_contents( $name ) );
		}
	}

	public function setCache( $name, $data = null ) {
		$name = $this->cacheDir . $name;

		$result = file_put_contents( $name, serialize( $data ) );
		if( $result === false ) {
			echo PHP_EOL, 'Can not set cache ', $name, PHP_EOL;
		}
	}
}