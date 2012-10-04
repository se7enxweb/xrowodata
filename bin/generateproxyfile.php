<?php
set_include_path( get_include_path() . PATH_SEPARATOR . '.'.DIRECTORY_SEPARATOR.'extension'.DIRECTORY_SEPARATOR.'xrowodata'.DIRECTORY_SEPARATOR.'OData_PHP_SDK'.DIRECTORY_SEPARATOR.'framework' );

$GLOBALS['ODataphp_path'] = './extension/xrowodata/OData_PHP_SDK/framework';

$argv = array( 'extension\xrowodata\bin\generateproxyfile.php',
'/uri=http://www.test.wuv.de/api/odata/v1/ezpublish.svc',
'/out=extension/xrowodata/classes',
'/ups=no',
);
require_once 'PHPDataSvcUtil.php';

require_once 'extension'.DIRECTORY_SEPARATOR.'xrowodata'.DIRECTORY_SEPARATOR.'classes'.DIRECTORY_SEPARATOR.'eZPublishEntities.php';

$proxy = new eZPublishEntities();

$response = $proxy->Execute('/frontpage(NodeID=102)/RefSetnews?$top=2&$skip=1');
	
foreach($response->Result as $news) {

           var_dump(get_object_vars($news));
}


$response = $proxy->Execute('/LatestNodes(1)/RefSetnews?list=102,103,104');

if( $response->Result[0] )
{
	echo "Node " . $response->Result[0]->NodeID . "\n";
}

$response = $proxy->Execute("folder(1)");

if( $response->Result[0] )
{
	echo "Node " . $response->Result[0]->NodeID . "\n";
}