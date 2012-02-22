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

        $this->show_template(
            'onappcdn/cdn_resources/purge',
            array(
                'id'                =>  parent::get_value('id'),
                'resource_id'       =>  parent::get_value('resource_id'),
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

        $onapp    = $this->getOnAppInstance();
        $id       = parent::get_value('resource_id');
        $purge    = parent::get_value('purge');

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $cdn_resource  = $onapp->factory('CDNResource', true );

        $purge_paths = trim( $purge['purge_paths'] );

        $cdn_resource->purge( $id, $purge_paths );

        if ( $cdn_resource->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $cdn_resource->getErrorsAsArray() );

        if ( ! $errors )
            $messages[] = $_LANG['onappcdnpurgesuccessfully'];

        $this->show( $errors, $messages );
    }
}