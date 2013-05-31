<?php
/**
 * Manages CDN Resource Purge
 */
class OnAppCDNPurge extends OnAppCDN {
	public function __construct() {
		parent::__construct();
		parent::init_wrapper();
	}

	/**
	 *
	 * @param string $errors   Errors
	 * @param string $messages Messages
	 */
	public function show( $errors = null, $messages = null ) {
		if( ! parent::getValue( 'resource_id' ) ) {
			die( 'resource_id should be specified' );
		}

		$purge = array();

		if( isset( $_SESSION[ 'successmessages' ] ) ) {
			$messages[ ] = $_SESSION[ 'successmessages' ];
			unset( $_SESSION[ 'successmessages' ] );
		}

		if( isset( $_SESSION[ 'errors' ] ) ) {
			$errors[ ] = $_SESSION[ 'errors' ];
			unset( $_SESSION[ 'errors' ] );
		}

		if( isset( $_SESSION[ 'purge' ] ) ) {
			$purge = $_SESSION[ 'purge' ];
			unset( $_SESSION[ 'purge' ] );
		}

		$this->showTemplate(
			'onappcdn/cdn_resources/purge',
			array(
				 'id'          => parent::getValue( 'id' ),
				 'resource_id' => parent::getValue( 'resource_id' ),
				 'purge'       => $purge,
				 'errors'      => implode( PHP_EOL, $errors ),
				 'messages'    => implode( PHP_EOL, $messages ),
			)
		);
	}

	/**
	 * This tool allows instant removal of HTTP Pull cache content in the CDN,
	 * if newly updated content have not been reflected.
	 *
	 * @return void
	 */
	protected function purge() {
		parent::loadCDNLanguage();
		global $_LANG;
		$errors = array();

		$onapp = $this->getOnAppInstance();
		$resource_id = parent::getValue( 'resource_id' );
		$purge = parent::getValue( 'purge' );

		$cdn_resource = $onapp->factory( 'CDNResource', true );

		$purge_paths = trim( $purge[ 'purge_paths' ] );

		$cdn_resource->purge( $resource_id, $purge_paths );

		if( $cdn_resource->getErrorsAsArray() ) {
			$errors[ ] = '<b>Purge Error: </b>' . $cdn_resource->getErrorsAsString();
		}

		$url = ONAPPCDN_FILE_NAME . '?page=purge&id=' . parent::getValue( 'id' ) . '&resource_id=' . $resource_id;

		if( ! $errors ) {
			$messages = $_LANG[ 'onappcdnpurgesuccessfully' ];
			$_SESSION[ 'successmessages' ] = $messages;
			$this->redirect( $url );
		}
		else {
			$_SESSION[ 'purge' ] = $_POST[ 'purge' ];
			$_SESSION[ 'errors' ] = implode( PHP_EOL, $errors );
			$this->redirect( $url );
		}
	}
}