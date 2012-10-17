<?php

use ODataProducer\Providers\Metadata\Type\EdmPrimitiveType;

class xrowODataCity extends xrowODataPlugin
{
    const NAME = "City";

    static function metadata( ODataProducer\Providers\Metadata\ServiceBaseMetadata $metadata )
    {
        $classstr = "class " . 'City' . " { ";
        $classstr .= 'public $Name;';
        $classstr .= " };";
        eval( $classstr );
        $type = $metadata->addEntityType( new ReflectionClass( "City" ), "City", 'eZPublish' );
        $metadata->addKeyProperty( $type, 'Name', EdmPrimitiveType::STRING );
        $metadata->addResourceSet( "City", $type );
    }

    static function getResourceSet( ResourceSet $resourceSet, $filter = null, $select = null, $orderby = null, $top = null, $skiptoken = null )
    {
        $return = array();
        $params = array();
        
        ezpQueryProvider::addLimitOffset( $params, $top, $skiptoken );
        $node = eZContentObjectTreeNode::fetch( 363959 );
        
        foreach ( xrowGISTools::citiesBySubtree( $node, $params ) as $city )
        {
            $ODatacity = new xrowODataCity();
            if ( is_array( $city ) )
            {
                $ODatacity->Name = $city['city'];
            }
            else
            {
                $ODatacity->Name = $city;
            }
            $return[] = $ODatacity;
        }
        return $return;
    }
}