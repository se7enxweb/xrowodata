<?php
$Module = array( 'name' => 'xrow OData' );

$ViewList = array();
$ViewList['redirect'] = array(
                   'functions' => array( 'read' ),
                   'script' => 'redirect.php',
                   'params' => array( ),U
                   );

$FunctionList = array();

//Users able to use the API
$FunctionList['read'] = array( );
$FunctionList['write'] = array( );