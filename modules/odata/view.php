<?php
set_include_path( get_include_path() . PATH_SEPARATOR . './extension/xrowodata/OData_Producer_for_PHP/library' );

require_once 'extension'.DIRECTORY_SEPARATOR.'xrowodata'.DIRECTORY_SEPARATOR.'OData_Producer_for_PHP'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'ODataProducer'.DIRECTORY_SEPARATOR.'Common'.DIRECTORY_SEPARATOR.'ClassAutoLoader.php';

use ODataProducer\Common\ClassAutoLoader;
ClassAutoLoader::register();

while ( @ob_end_clean() );

try
{
    $dispatcher = new ezpDispatcher();
    $dispatcher->dispatch();
}
catch ( Exception $e )
{
    eZDebug::writeError( $e->getMessage().$e->file().$e->line() );
    throw $e;
}

eZExecution::cleanExit();
