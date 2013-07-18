<?php

$EntpointURI = 'http://admin:publish@trunk.example.com/api/odata/v1/ezpublish.svc';

$client = xrowODataClient::factory( $EntpointURI );

/* get the list of article */
try
{
    //List all Article's ID and Name with '123456' as ParentNodeId
    $response = $client->article()->filter('ParentNodeId eq 123456')->IncludeTotalCount()->Execute();
    echo "\nTotal number of articles:" . $response->TotalCount();
    foreach ( $response->Result as $article )
    {
        echo "\nNodeID: " . $article->MainNodeID . "\tNodename: " . $article->ContentObjectName;
    }
}
catch ( DataServiceRequestException $exception )
{
    echo $exception->Response->getError();
}