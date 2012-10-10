<?php

class xrowODataClient
{
	const DIR = 'var/cache/xrowodata';
    function factory( $uri )
    {
    	if (!is_dir(self::DIR))
    	{
    		eZDir::mkdir( self::DIR , false, true );
    	}
    	
        set_include_path( get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR . 'xrowodata' . DIRECTORY_SEPARATOR . 'OData_PHP_SDK' . DIRECTORY_SEPARATOR . 'framework' );
        $GLOBALS['ODataphp_path'] = './extension/xrowodata/OData_PHP_SDK/framework';
        $argv = array( 
            'extension\xrowodata\bin\test.php' , 
            '/uri=http://admin:publish@trunk.example.com/api/odata/v1/ezpublish.svc' , 
            '/out=var/cache/xrowodata' , 
            '/ups=no' 
        );
        require_once 'PHPDataSvcUtil.php';
        require_once self::DIR . '/eZPublishEntities.php';
        
        $proxy = new eZPublishEntities();
        return $proxy;
    }
}