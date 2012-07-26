<?php
/**
 *  Manages CDN Prefetch
 */
class OnAppCDNPrefetch extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     * Displays prefetch form
     *
     * @param string $errors Errors
     * @param string $messages Messages
     * @return void
     */
    public function show( $errors = null, $messages = null ) {
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }
        
        $prefetch = array();

        if ( isset( $_SESSION['successmessages'] ) ) {
            $messages[] = $_SESSION['successmessages'];
            unset( $_SESSION['successmessages'] );
        }

        if (isset($_SESSION['errors'])) {
            $errors[] = $_SESSION['errors'];
            unset($_SESSION['errors']);
        }

        if (isset($_SESSION['prefetch'])) {
            $prefetch = $_SESSION['prefetch'];
            unset($_SESSION['prefetch']);
        }

        $this->show_template(
            'onappcdn/cdn_resources/prefetch',
            array(
                'id'                =>  parent::get_value('id'),
                'resource_id'       =>  parent::get_value('resource_id'),
                'prefetch'          =>  $prefetch,
                'errors'            =>  implode( PHP_EOL, $errors ),
                'messages'          =>  implode( PHP_EOL, $messages ),
            )
        );
    }

    /**
     * Allows HTTP Pull content to be pre-populated to the CDN.
     * Recommended only if files especially large.
     *
     * @return void
     */
    protected function prefetch () {
        if ( ! parent::get_value('resource_id') ) {
            die('resource_id should be specified');
        }
        
        parent::loadcdn_language();
        global $_LANG;
        $errors = array();

        $onapp    = $this->getOnAppInstance();
        $resource_id       = parent::get_value('resource_id');
        $prefetch = parent::get_value('prefetch');

        $cdn_resource  = $onapp->factory('CDNResource', true );

        $prefetch_paths = trim( $prefetch['prefetch_paths'] );

        $cdn_resource->prefetch( $resource_id, $prefetch_paths );

        if ( $cdn_resource->getErrorsAsArray() )
            $errors[] = '<b>Prefetch Error: </b>' . $cdn_resource->getErrorsAsString();

        $url = ONAPPCDN_FILE_NAME . '?page=prefetch&id=' . parent::get_value('id') . '&resource_id=' . $resource_id;
        
        if ( ! $errors ) {
            $messages = $_LANG['onappcdnprefetchsuccessfully'];
            $_SESSION['successmessages'] = $messages;
            $this->redirect($url);
        } else {
            $_SESSION['prefetch'] = $_POST['prefetch'];
            $_SESSION['errors'] = implode(PHP_EOL, $errors);
            $this->redirect($url);
        }
    }
}

