<?php
/**
 * Manages CDN Resource
 *
 */
class OnAppCDNDefault extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }

    /**
     *
     * 
     */
    protected function create () {
        global $_LANG;
        parent::loadcdn_language();
        
        $result = $this->create_user();

        if (  $result == 'success' ) {
            $_SESSION['successmessages'] = $_LANG['onappcdnsuccessusercreate'];
            $this->redirect( ONAPPCDN_FILE_NAME . '?page=resources&id=' . parent::get_value('id') );
        }
        else {
            $_SESSION['failerrors'] = array( $_LANG['onappcdnfailedusercreate'] ,  $result );
            $this->redirect( ONAPPCDN_FILE_NAME . '?page=error&id=' . parent::get_value('id') );
        }
    }
}