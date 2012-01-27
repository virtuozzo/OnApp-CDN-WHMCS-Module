<?php
/**
 * 
 */
class OnAppCDNResources extends OnAppCDN {

    public function __construct () {
        parent::__construct();
        parent::init_wrapper();
    }
    
    public function show( ) {
        $onapp = $this->getOnAppInstance();
        
        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $resource  = $onapp->factory('CDNResource', true );

        $resources = $resource->getList();

        $response  = $resource->getResponse();

        if ( $response['info']['http_code'] == 302 ) {
            $resources = NULL;
        }

        $this->show_template(
            'onappcdn/cdn_resources',
            array(
                'id'         =>  parent::get_value('id'),
                'resources'  =>  $resources,
                'server'     =>  $server,
                'user'       =>  $user,
                'test_value' =>  'onappcdn/resources.tpl',
                'errors'     =>  implode( PHP_EOL, $errors ),
            )
        );
        
    }

    /**
     *
     */
    protected function enable( ) {
        
        $onapp = $this->getOnAppInstance();

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $resource  = $onapp->factory('CDNResource', true );

        $resource->enable();

        print('<pre>');
        print_r($resource);
        die();

    }

    /**
     *
     */
    protected function edit () {
        echo __METHOD__;
    }

    /**
     *
     */
    protected function delete () {
        echo __METHOD__;
    }

    /**
     * 
     */
    protected function add () {
        echo __METHOD__;
    }
    
}

