<?php
define( 'SERVICE_URL', 'http://trunk.example.com/odata/view/ezpublish.svc');

$svc = new eZPublishEntities(SERVICE_URL);
     
/* get the list of Customers in the USA, for each customer get the list of Orders */
$query = $svc->news()
 ->filter("Country eq 'USA'")
 ->Expand('Orders');
$customerResponse = $query->Execute();
var_dump($customerResponse);