<?php
$urlCfg = new ezcUrlConfiguration();
$urlCfg->basedir = '';
$urlCfg->script = 'index.php';
if ( isset( $_GET['mobile'] ) )
{
	$_SERVER[xrowMobileEvent::HTTP_URL]='1';
}
$fullurl = $_SERVER['REQUEST_URI'];

$url = new ezcUrl( $fullurl, $urlCfg );

# extract "odata/redirect"
$url->params = array_slice( $url->getParams(), 2 );

$query = $url->getQuery();

$uri = eZURI::instance( $url->buildUrl() );

xrowMobileEvent::input( $uri );

if ( isset( $_GET['mobile'] ) )
{
	throw new Exception( "No redirect." );
}
throw new Exception( "No redirect, if you don't have a mobile device. You can`t use this module. Use ?mobile=1 to force redirect." );

