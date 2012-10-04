<?php

class xrowMobileEvent
{
    const HTTP_URL = 'HTTP_X_MOBILE';

    /**
     * request/input event listener
     *
     * @param eZURI $uri
     */
    static public function input( eZURI $uri )
    {
        if ( $_SERVER['REQUEST_METHOD'] === 'GET' and isset( $_SERVER[self::HTTP_URL] ) and $_SERVER[self::HTTP_URL] === '1' )
        {
            /* WURFL if needed.
            #$wurflConfig = new WURFL_Configuration_ArrayConfig( './extension/xrowodata/resources/array-wurfl-config.php' );
            #
            #$wurflManagerFactory = new WURFL_WURFLManagerFactory( $wurflConfig );
            # 
            #$wurflManager = $wurflManagerFactory->create();
            #
            #$device = $wurflManager->getDeviceForHttpRequest( $_SERVER );
            #
            #if ( $device->getCapability( "is_wireless_device" ) === 'false' )
            #{
            #    return null;
            #}
            */
            $uri = eZURI::instance( eZSys::requestURI() );
            $urlCfg = new ezcUrlConfiguration();
            $urlCfg->basedir = '';
            $urlCfg->script = 'index.php';
            $urlCfg->addOrderedParameter( 'classname' );
            $urlCfg->addOrderedParameter( 'class' );
            $urlCfg->addOrderedParameter( 'NodeIDname' );
            $urlCfg->addOrderedParameter( 'NodeID' );
            $urlCfg->addOrderedParameter( 'pathname' );
            $urlCfg->addOrderedParameter( 'path' );

            $ini = eZINI::instance( 'odata.ini' );
            $siteaccess = '';
            
            if ( $ini->hasVariable( 'Settings', 'MobileRedirectList' ) )
            {
                $MobileRedirectList = $ini->variable( 'Settings', 'MobileRedirectList' );
            }
            // do not strip out the siteaccess
            if( $ini->hasVariable( 'Settings', 'MobileDoNotStripOutSiteaccess' ) )
            {
                foreach ( $ini->variable( 'Settings', 'MobileDoNotStripOutSiteaccess' ) as $item )
                {
                    if ( strpos( eZSys::requestURI(), '/' . $item . '/' ) !== false )
                    {
                        $siteaccess = $item . ':';
                    }
                }
            }

            // for wildcards
            $wildcard = eZURLWildcard::fetchBySourceURL( $uri->OriginalURI, false );
            if( isset( $wildcard ) && array_key_exists( 'destination_url', $wildcard ) )
            {
                $wildcard_destination_url = $wildcard['destination_url'];
                $uri = eZURI::instance( $wildcard_destination_url );
            }
            
            if ( $ini->hasVariable( 'Settings', 'MobileExcludeList' ) )
            {
                foreach ( $ini->variable( 'Settings', 'MobileExcludeList' ) as $item )
                {
                    if ( strpos( $uri->uriString(), $item ) !== false )
                    {
                        return null;
                    }
                }
            }
            if ( $ini->hasVariable( 'Settings', 'MobileExcludeHostsList' ) )
            {
                $curent_url = ezcUrlTools::getCurrentUrl();
                $url = new ezcUrl( $curent_url );
                $host = $url->__get( 'host' );
                foreach ( $ini->variable( 'Settings', 'MobileExcludeHostsList' ) as $item )
                {
                    if ( strpos( $host, $item ) !== false )
                    {
                        return null;
                    }
                }
            }

            $translateResult = eZURLAliasML::translate( $uri );
            $node = false;
            if( is_string( $translateResult ) )
            {
                $site_ini = eZINI::instance( 'site.ini' );
                $path_prefix = $site_ini->variable( 'SiteAccessSettings', 'PathPrefix' );
                $node = eZContentObjectTreeNode::fetchByURLPath( $path_prefix. '/' . $translateResult );
                $node_id = $node->attribute( 'node_id' );
            }
            if ( isset( $uri->URIArray[0] ) )
            {
                if( ( isset( $node ) and $node instanceof eZContentObjectTreeNode )
                    or ($uri->URIArray[0] == 'content' and isset( $uri->URIArray[1] ) and $uri->URIArray[1] == 'view' and isset( $uri->URIArray[2] ) and isset( $uri->URIArray[3] ) and is_numeric( $uri->URIArray[3] ) ) )
                {
                    if( $node === false )
                    {
                        $node_id = $uri->URIArray[3];
                        $node = eZContentObjectTreeNode::fetch( $node_id );
                    }
                    if ( isset( $MobileRedirectList ) )
                    {
                        foreach ( $MobileRedirectList as $key => $item )
                        {
                            if( $key != '/' )
                            {
                                if ( strpos( $uri->OriginalURI, $key ) !== false )
                                {
                                    $urlAlias = str_replace( $key, $item, $uri->OriginalURI );
                                }
                            }
                        }
                    }
                    else
                    {
                        $urlAlias = $node->urlAlias();
                    }
                    $url = new ezcUrl( 'http://' . $_SERVER['SERVER_NAME'] . '/' . 'class' . '/' . $node->classIdentifier() . '/NodeID/' . $node_id . '/path/' . $siteaccess . str_replace( '/', ':', $urlAlias ), $urlCfg );
                }
                elseif( $uri->URIArray[0] == '' and isset( $uri->URI ) and $uri->URI == '' and isset( $uri->OriginalURI ) and $uri->OriginalURI == '' )
                {
                    if ( isset( $MobileRedirectList ) && array_key_exists( '/', $MobileRedirectList ) )
                    {
                        $home = $MobileRedirectList['/'];
                        $url = new ezcUrl( 'http://' . $_SERVER['SERVER_NAME'] . '/' . $home, $urlCfg );
                    }
                    else
                    {
                        $urlCfg = new ezcUrlConfiguration();
                        $urlCfg->basedir = '';
                        $urlCfg->script = 'index.php';
                        $url = new ezcUrl( 'http://' . $_SERVER['SERVER_NAME'], $urlCfg );
                    }
                }
                if ( $ini->hasVariable( 'Settings', 'MobileRedirectHost' ) )
                {
                    $url->host = $ini->variable( 'Settings', 'MobileRedirectHost' );
                }
                eZHTTPTool::redirect( (string) $url, array(), 301 );
                eZExecution::cleanExit();
            }
            if ( isset( $MobileRedirectList ) )
            {
                foreach ( $MobileRedirectList as $key => $item )
                {
                    if ( strpos( $uri->uriString(), $key ) !== false )
                    {
                        $url = new ezcUrl( 'http://' . $_SERVER['SERVER_NAME'] . '/' . $item , $urlCfg );
                        
                        if ( $ini->hasVariable( 'Settings', 'MobileRedirectHost' ) )
                        {
                            $url->host = $ini->variable( 'Settings', 'MobileRedirectHost' );
                        }
                        eZHTTPTool::redirect( (string) $url, array(), 301 );
                        eZExecution::cleanExit();
                    }
                }
            }
            return null;
        }
    }
}