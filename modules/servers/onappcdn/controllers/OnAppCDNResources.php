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

        $resources_enabled = ( $response['info']['http_code'] == 302 ) ? false : true;

        $this->show_template(
            'onappcdn/cdn_resources',
            array(
                'id'                =>  parent::get_value('id'),
                'resources'         =>  $resources,
                'resources_enabled' =>  $resources_enabled,
                'errors'            =>  implode( PHP_EOL, $errors ),
            )
        );
    }

   /**
     *
     */
    protected function details () {
        $onapp = $this->getOnAppInstance();

        if ( $onapp->getErrorsAsArray() )
            $errors[] = implode( PHP_EOL , $onapp->getErrorsAsArray() );

        $_resource  = $onapp->factory('CDNResource', true );
        
        $resource   = $_resource->load( parent::get_value( 'resource_id' ) );

        $this->show_template(
            'onappcdn/cdn_resources/details',
            array(
                'id'                =>  parent::get_value('id'),
                'resource'          =>  $resource,
                'errors'            =>  implode( PHP_EOL, $errors ),
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
        $this->show();

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

