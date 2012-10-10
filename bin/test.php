<?php

$EntpointURI = 'http://admin:publish@trunk.example.com/api/odata/v1/ezpublish.svc';

$client = xrowODataClient::factory( $EntpointURI );

/* get the list of images */
$query = $client->image();
$response = $query->Execute();

foreach($response->Result as $image)
{
	echo "\nNodeID: " . $image->MainNodeID . "\tNodename: " . $image->ContentObjectName ;
}

/* get the list of images by url */
$response = $client->Execute("image");

