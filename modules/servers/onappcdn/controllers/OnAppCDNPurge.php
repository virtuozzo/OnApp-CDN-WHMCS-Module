<?php
/**
 * Manages CDN Resource Purge
 *
 */
class OnAppCDNPurge extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     *
     * @param string $errors Errors
     * @param string $messages Messages
     */
    public function show( $errors = null, $messages = null ) {
        $purge = array();

        if ( isset( $_SESSION['successmessages'] ) ) {
            $messages[] = $_SESSION['successmessages'];
            unset( $_SESSION['successmessages'] );
        }

        if (isset($_SESSION['errors'])) {
            $errors[] = $_SESSION['errors'];
            unset($_SESSION['errors']);
        }

        if (isset($_SESSION['purge'])) {
            $purge = $_SESSION['purge'];
            unset($_SESSION['purge']);
        }

        $this->show_template(
            'onappcdn/cdn_resources/purge',
            array(
                'id'                =>  parent::get_value('id'),
                'resource_id'       =>  parent::get_value('resource_id'),
                'purge'             =>  $purge,
                'errors'            =>  implode( PHP_EOL, $errors ),
                'messages'          =>  implode( PHP_EOL, $messages ),
            )
        );
    }

    /**
     * This tool allows instant removal of HTTP Pull cache content in the CDN,
     * if newly updated content have not been reflected.
     *
     * @return void
     */
    protected function purge () {

        parent::loadcdn_language();
        global $_LANG;
        $errors = array();

        $onapp       = $this->getOnAppInstance();
        $resource_id = parent::get_value('resource_id');
        $purge       = parent::get_value('purge');

        if ( $onapp->getErrorsAsArray() )
            $errors[] = '<b>Getting OnApp Version Error: </b>' . implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $cdn_resource  = $onapp->factory('CDNResource', true );

        $purge_paths = trim( $purge['purge_paths'] );

        $cdn_resource->purge( $resource_id, $purge_paths );

        if ( $cdn_resource->getErrorsAsArray() )
            $errors[] = '<b>Getting OnApp Version Error: </b>' . implode( PHP_EOL , $cdn_resource->getErrorsAsArray() );

        $url = ONAPPCDN_FILE_NAME . '?page=purge&id=' . parent::get_value('id') . '&resource_id=' . $resource_id;

        if ( ! $errors ) {
            $messages = $_LANG['onappcdnpurgesuccessfully'];
            $_SESSION['successmessages'] = $messages;
            $this->redirect($url);
        } else {
            $_SESSION['purge'] = $_POST['purge'];
            $_SESSION['errors'] = implode(PHP_EOL, $errors);
            $this->redirect($url);
        }
    }
}