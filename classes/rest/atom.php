<?php

class odataRestAtomView extends ezcMvcView
{

    public function __construct( ezcMvcRequest $request, ezcMvcResult $result )
    {
        parent::__construct( $request, $result );
        $result->content = new ezcMvcResultContent();
        if ( isset( $_GET['debug'] ) )
        {
            $result->content->type = "text/html";
        }
        else
        {
            $result->content->type = "text/xml";
            # forces download in browser
            #$result->content->type = "application/atom+xml";
        }
        $result->cache = new ezcMvcResultCache();
        $result->cache->expire = new DateTime();
        $ini = eZINI::instance( 'odata.ini' );
        if ( isset( $GLOBALS['EZ_ODATA_CACHE_TIME'] ) )
        {
            $seconds = $GLOBALS['EZ_ODATA_CACHE_TIME'];
        }
        elseif ( $ini->hasVariable( 'Settings', 'CacheTTL' ) )
        {
            $seconds = (int)$ini->variable( 'Settings', 'CacheTTL' );
        }
        else
        {
            $seconds = 900;
        }
        $result->cache->expire->add( new DateInterval('PT' . $seconds . 'S') );
        $result->cache->controls = array( 'cache', 'max-age=' . $seconds );
        $result->cache->pragma = ' ';
        $result->content->charset = "utf-8";
        $result->content->language = "de-DE";
        $result->content->disposition = new ezcMvcResultContentDisposition( 'inline' );
    }

    public function createZones( $layout )
    {
        $zones = array();
        $zones[] = new odataViewHandler();
        return $zones;
    }
}
