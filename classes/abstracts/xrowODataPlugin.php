<?php

abstract class xrowODataPlugin
{
    abstract static function metadata( ODataProducer\Providers\Metadata\ServiceBaseMetadata $metadata );
    abstract static function getResourceSet( ResourceSet $resourceSet, $filter = null, $select = null, $orderby = null, $top = null, $skiptoken = null );
}