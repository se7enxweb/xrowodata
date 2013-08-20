<?php

class xrowODataClient
{
    const DIR = 'var/cache/xrowodata';
    function factory( $uri,$entities )
    {
        if (!is_dir(self::DIR))
        {
            eZDir::mkdir( self::DIR , false, true );
        }
        
        set_include_path( get_include_path() . PATH_SEPARATOR . '.' . DIRECTORY_SEPARATOR . 'extension' . DIRECTORY_SEPARATOR . 'xrowodata' . DIRECTORY_SEPARATOR . 'src' .DIRECTORY_SEPARATOR . 'OData_PHP_SDK' . DIRECTORY_SEPARATOR . 'framework' );
        $GLOBALS['ODataphp_path'] = './extension/xrowodata/src/OData_PHP_SDK/framework';
        $argv = array( 
            '' , 
            '/uri='.$uri , 
            '/out=var/cache/xrowodata/'.$entities , 
            '/ups=no' 
        );
        require_once 'PHPDataSvcUtil.php';
        require_once self::DIR . '/' . $entities;
        
        $proxy = new eZPublishEntities();
        return $proxy;
    }
}