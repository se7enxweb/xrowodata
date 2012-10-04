<?php
$Module = array( 'name' => 'xrow OData' );

$ViewList = array();

$ViewList['view'] = array(
                   'functions' => array( 'read' ),
                   'script' => 'view.php',
                   'params' => array( ),
                   );
$ViewList['redirect'] = array(
                   'functions' => array( 'read' ),
                   'script' => 'redirect.php',
                   'params' => array( ),
                   );

$FunctionList = array();

$FunctionList['read'] = array( );

?>
