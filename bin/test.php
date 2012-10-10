<?php

$EntpointURI = 'http://admin:publish@trunk.example.com/api/odata/v1/ezpublish.svc';

$client = xrowODataClient::factory( $EntpointURI );

/* get the list of images */
try
{
    $response = $client->image()->IncludeTotalCount()->Execute();
    echo "\nTotal number of images:" . $response->TotalCount();
    foreach ( $response->Result as $image )
    {
        echo "\nNodeID: " . $image->MainNodeID . "\tNodename: " . $image->ContentObjectName;
    }
}
catch ( DataServiceRequestException $exception )
{
    echo $exception->Response->getError();
}

