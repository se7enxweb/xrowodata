<?php

class odataRestApiProvider implements ezpRestProviderInterface
{

    /**
     * Returns registered versioned routes for provider
     *
     * @return array Associative array. Key is the route name (beware of name collision!). Value is the versioned route.
     */
    public function getRoutes()
    {
        return array( 
            'foo' => new ezpRestVersionedRoute( new ezpMvcRegexpRoute( '@(.*)ezpublish.svc/(.*)$@', 'odataRestController', 'foo' ), 1 ) , 
            'foobar' => new ezpRestVersionedRoute( new ezpMvcRegexpRoute( '/ezpublish.svc/', 'odataRestController', 'fooBar' ), 2 ) 
        );
    }

    /**
     * Returns associated with provider view controller
     *
     * @return ezpRestViewController
     */
    public function getViewController()
    {
        return new odataRestApiViewController();
    }
}