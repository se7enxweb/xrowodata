<?php
/**
* This file is a demonstration of the eZ PUblish REST API developer preview. It handles oAuth2
* authorization, and will return as-is the JSON content returned by the REST interface.
*
* It doesn't cover the whole thing, but almost, and should give you all you need to get started
* with it !
*
* It accepts a few URL parameters that just change which REST resource is used:
* - resource: the REST resource URI that should be queried: content/node/2,
*   content/object/1/field/title, etc
*   Default: content/node/2
* - output: either json to get raw json, or html to get a preformatted print_r (default)
*
* Setup:
* - place this script in a folder on some web server. The only constraint is that it should be
*   able to access your eZ Publish (the one that has REST installed) over HTTP
* - in your eZ Publish backoffice, go to oauthadmin/list. Create a new application. Enter any
*   name (it doesn't matter), and enter the URL where you placed this script (the whole thing,
*   from http to .php).
* - on the details view for your newly created app (it should be oauthadmin/view/1), you will
*   see a "client_id", an md5 sum. Set this value as $appClientId below. It is  used to identify
*   the application requesting access.
* - set $eZHost below to your eZ Publish host (front or backoffice, doesn't matter)
* - open this script in your browser, WITHOUT parameters, and follow the leader.
*   The first time you authenticate, you will be asked if you want to authorize this application.
*   Once this is done, you can add parameters, as documented above:
*   - output=json will send you direct json. You can find browser plugins in order to view these
*     directly.
*   - resource must be given the REST resource you want to query. A few you can try until the
*     full doc is released:
*     - resource=content/node/<id>
*     - resource=content/node/<id>/fields
*     - resource=content/node/<id>/field/<attribute_identifier>
*/
session_start();

$resource = isset( $_GET['resource'] ) ? $_GET['resource'] : 'content/node/2';
$output = isset( $_GET['output'] ) ? $_GET['output'] : 'html';

// Of course need to customize these
$eZHost = 'http://localhost';
$appClientId = '<CHANGEME>';

$ouAuthorize = "{$eZHost}/oauth/authorize";
$ouToken = "{$eZHost}/oauth/token";
$restUrl = "{$eZHost}/api/{$resource}";

if ( isset( $_GET['access_token'] ) )
{
    $_SESSION['token'] = $_GET['access_token'];
}
// we need a token to use the REST interface
elseif ( !isset( $_SESSION['token'] ) )
{
    $authParameters = array();
    $authParameters['redirect_uri'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $authParameters['client_id'] = $appClientId;
    $authParameters['response_type'] = 'token';
    array_walk( $authParameters, function( &$value, $key ) {
        $value = "{$key}=" . urlencode( $value );
    });
    $uri = $ouAuthorize . '?' . implode( '&', $authParameters );

    // This will redirect the browser to the authorization page on eZ Publish itself
    header( "Location: $uri" );
    exit;
}

// Create a stream context with the Authorization header required by oauth based security
$streamOptions = array(
    'http' => array(
        'method' => 'GET',
        'header' => "Authorization: OAuth {$_SESSION['token']}",
    )
);
$context = stream_context_create( $streamOptions );

$result = @file_get_contents( $restUrl, false, $context );
if ( $result === false )
{
    echo "An error occured. These are the response headers from the REST service:\n<br />";
    echo "<pre>";
    print_r( $http_response_header );
    echo "</pre>";
    exit( 1 );
}
if ( $output == 'json' )
{
    header('Content-Type: application/json' );
    echo $result;
}
else
{
    echo "<pre>" . print_r( json_decode( $result ), true ) . "</pre>";
}