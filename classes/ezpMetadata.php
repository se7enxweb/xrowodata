<?php
use ODataProducer\Providers\Metadata\ResourceStreamInfo;
use ODataProducer\Providers\Metadata\ResourceAssociationSetEnd;
use ODataProducer\Providers\Metadata\ResourceAssociationSet;
use ODataProducer\Common\NotImplementedException;
use ODataProducer\Providers\Metadata\Type\EdmPrimitiveType;
use ODataProducer\Providers\Metadata\ResourceSet;
use ODataProducer\Providers\Metadata\ResourcePropertyKind;
use ODataProducer\Providers\Metadata\ResourceProperty;
use ODataProducer\Providers\Metadata\ResourceTypeKind;
use ODataProducer\Providers\Metadata\ResourceType;
use ODataProducer\Common\InvalidOperationException;
use ODataProducer\Providers\Metadata\IDataServiceMetadataProvider;
use ODataProducer\Providers\Metadata\ServiceBaseMetadata;

class ezpMetadata
{
    const REFSET_IDENTIFIER = 'Related_';

    /**
     * create metadata
     * 
     * @throws InvalidOperationException
     * 
     * @return NorthWindMetadata
     */
    public static function create()
    {
        $metadata = new ServiceBaseMetadata( 'eZPublishEntities', 'eZPublish' );
        /*
        $nodesEntityType = $metadata->addEntityType(new ReflectionClass('Node'), 'Node', 'eZPublish');
        $metadata->addKeyProperty($nodesEntityType, 'NodeID', EdmPrimitiveType::INT32);
        $metadata->addPrimitiveProperty($nodesEntityType, 'Guid', EdmPrimitiveType::STRING);
        
        $metadata->addPrimitiveProperty($nodesEntityType, 'Name', EdmPrimitiveType::STRING);
        //Register the entity (resource) type 'Post'
        $objectsEntityType = $metadata->addEntityType(new ReflectionClass('ContentObject'), 'ContentObject', 'eZPublish');
        $metadata->addKeyProperty($objectsEntityType, 'ContentObjectID', EdmPrimitiveType::INT32);
        $metadata->addPrimitiveProperty($objectsEntityType, 'Name', EdmPrimitiveType::STRING);
        $metadata->addPrimitiveProperty($objectsEntityType, 'Date', EdmPrimitiveType::DATETIME);
        $metadata->addPrimitiveProperty($objectsEntityType, 'Guid', EdmPrimitiveType::STRING);

        $nodesResourceSet = $metadata->addResourceSet('Nodes', $nodesEntityType);
        $objectsResourceSet = $metadata->addResourceSet('ContentObjects', $objectsEntityType);

        
        $metadata->addResourceSetReferenceProperty($objectsEntityType, 'Nodes', $nodesResourceSet);
     */
        eval( 'class ODATAezauthorrelation { public $xml = ""; }' );
        $ezauthorrelationComplexType = $metadata->addComplexType( new ReflectionClass( 'ODATAezauthorrelation' ), 'ODATAezauthorrelation', 'eZPublish', null );
        
        eval( 'class ODATAeZXML { public $xml = ""; }' );
        $ezxmlComplexType = $metadata->addComplexType( new ReflectionClass( 'ODATAeZXML' ), 'ODATAeZXML', 'eZPublish', null );
        
        $ODATAImage = 'class ODATAImage { public $text = "";';
        
        foreach ( xrowODataUtils::ImageAliasList() as $alias )
        {
            $ODATAImage .= 'public $' . $alias . ' = "";';
        }
        $ODATAImage .= '}';
        eval( $ODATAImage );
        $ezimageComplexType = $metadata->addComplexType( new ReflectionClass( 'ODATAImage' ), 'ODATAImage', 'eZPublish', null );
        
        $metadata->addPrimitiveProperty( $ezimageComplexType, 'text', EdmPrimitiveType::STRING );
        
        foreach ( xrowODataUtils::ImageAliasList() as $alias )
        {
            $metadata->addPrimitiveProperty( $ezimageComplexType, $alias, EdmPrimitiveType::STRING );
        }
        
        $xrowgisComplexType = $metadata->addComplexType( new ReflectionClass( 'ODataGIS' ), 'ODataGIS', 'eZPublish', null );
        $metadata->addPrimitiveProperty( $xrowgisComplexType, 'latitude', EdmPrimitiveType::DOUBLE );
        $metadata->addPrimitiveProperty( $xrowgisComplexType, 'longitude', EdmPrimitiveType::DOUBLE );
        $metadata->addPrimitiveProperty( $xrowgisComplexType, 'street', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $xrowgisComplexType, 'zip', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $xrowgisComplexType, 'city', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $xrowgisComplexType, 'state', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $xrowgisComplexType, 'country', EdmPrimitiveType::STRING );
        
        $ezcontentobjectComplexType = $metadata->addComplexType( new ReflectionClass( 'ContentObject' ), 'ContentObject', 'eZPublish', null );
        $metadata->addPrimitiveProperty( $ezcontentobjectComplexType, 'ContentObjectID', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $ezcontentobjectComplexType, 'Name', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $ezcontentobjectComplexType, 'Guid', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $ezcontentobjectComplexType, 'MainNodeID', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $ezcontentobjectComplexType, 'ClassIdentifier', EdmPrimitiveType::STRING );
        
        $xrowmetadataComplexType = $metadata->addComplexType( new ReflectionClass( 'ODataMetaData' ), 'ODataMetaData', 'eZPublish', null );
        $metadata->addPrimitiveProperty( $xrowmetadataComplexType, 'title', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $xrowmetadataComplexType, 'keywords', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $xrowmetadataComplexType, 'description', EdmPrimitiveType::STRING );
        
        eval( 'class ODATAxrowArrayData { public $data = ""; }' );
        $xrowArrayDataComplexType = $metadata->addComplexType( new ReflectionClass( 'ODATAxrowArrayData' ), 'ODATAxrowArrayData', 'eZPublish', null );
        
        $listtofilter = eZContentClass::fetchList();
        $ini = eZINI::instance( "odata.ini" );
        $list = array();
        if ( $ini->hasVariable( 'Settings', 'AvailableClassList' ) and $listtofilter and is_array( $ini->variable( 'Settings', 'AvailableClassList' ) ) )
        {
            foreach ( $listtofilter as $class )
            {
                if ( in_array( $class->attribute( 'identifier' ), $ini->variable( 'Settings', 'AvailableClassList' ) ) )
                {
                    $list[] = $class;
                }
            }
        }
        else
        {
            $list = $listtofilter;
        }
        
        $metaclasses = array();
        
        $classnames = array_keys( $metaclasses );
        $GLOBALS['ODATACLASSMATRIX'] = array();
        /* @var $class eZContentClass */
        foreach ( $list as $class )
        {
            $classstr = "class " . $class->attribute( 'identifier' ) . " { ";
            $classstr .= 'public $NodeID;';
            $classstr .= 'public $MainNodeID;';
            $classstr .= 'public $ContentObjectID;';
            $classstr .= 'public $ContentObjectName;';
            $classstr .= 'public $ParentNodeID;';
            $classstr .= 'public $ParentName;';
            $classstr .= 'public $URLAlias;';
            $classstr .= 'public $ClassIdentifier;';
            $classstr .= 'public $content_published = 0;';
            $classstr .= 'public $content_modified = 0;';
            $classstr .= 'public $Guid = "";';
            /*
             * All Attributes that are availbale for filtering.
class_identifier,class_name,depth,modified,modified_subnode,name,owner,path,priority,published,section,state
             * */
            
            foreach ( $class->fetchAttributes() as $attribute )
            {
                if ( in_array( $attribute->DataTypeString, xrowODataUtils::unsupportedDatatypes() ) )
                {
                    continue;
                }
                $classstr .= "public $" . $attribute->attribute( 'identifier' ) . " = '';";
            }
            foreach ( $list as $class2 )
            {
                $classstr .= 'public $' . self::REFSET_IDENTIFIER . $class2->attribute( 'identifier' ) . '= "";';
            }
            
            $classstr .= " };";
            eval( $classstr );
            $metaclasses[$class->attribute( 'identifier' )]['Type'] = $metadata->addEntityType( new ReflectionClass( $class->attribute( 'identifier' ) ), $class->attribute( 'identifier' ), 'eZPublish' );
            $metadata->addKeyProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'NodeID', EdmPrimitiveType::INT32 );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'MainNodeID', EdmPrimitiveType::INT32 );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ContentObjectID', EdmPrimitiveType::INT32 );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ParentNodeID', EdmPrimitiveType::INT32 );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'Guid', EdmPrimitiveType::GUID );
            
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ContentObjectName', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ParentName', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'URLAlias', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ClassIdentifier', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'content_published', EdmPrimitiveType::DATETIME );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'content_modified', EdmPrimitiveType::DATETIME );
            
            foreach ( $class->fetchAttributes() as $attribute )
            {
                $GLOBALS['ODATACLASSMATRIX'][$class->attribute( 'identifier' )][$attribute->attribute( 'identifier' )] = $attribute->attribute( 'identifier' );
                
                if ( in_array( $attribute->DataTypeString, xrowODataUtils::unsupportedDatatypes() ) )
                {
                    continue;
                }
                
                switch ( $attribute->DataTypeString )
                {
                    case 'ezstring':
                    case 'ezemail':
                    case 'ezidentifier':
                    case 'ezcountry':
                    case 'ezisbn':
                    case 'eztext':
                    case 'ezurl':
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::STRING );
                        break;
                    case 'ezauthorrelation':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $ezauthorrelationComplexType );
                        break;
                    case 'xrowgis':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $xrowgisComplexType );
                        break;
                    case 'xrowmetadata':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $xrowmetadataComplexType );
                        break;
                    case 'ezimage':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $ezimageComplexType );
                        break;
                    case 'ezxmltext':
                        #$metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $ezxmlComplexType );
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::STRING );
                        break;
                    case 'ezboolean':
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::BOOLEAN );
                        break;
                    case 'ezdate':
                    case 'ezdatetime':
                    case 'time':
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::DATETIME );
                        break;
                    case 'ezinteger':
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::INT16 );
                        break;
                    case 'ezobjectrelation':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $ezcontentobjectComplexType );
                        break;
                    case 'ezfloat':
                    case 'ezprice':
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::DOUBLE );
                        break;
                    default:
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::STRING );
                        break;
                }
            }
        }
        
        //add resource to metadata
        foreach ( $metaclasses as $classname => $metaclass )
        {
            $metaclasses[$classname]['ResourceSet'] = $metadata->addResourceSet( $classname, $metaclasses[$classname]['Type'] );
        }
        $classnames = array_keys( $metaclasses );
        //add relations
        foreach ( $classnames as $classname )
        {
            foreach ( $classnames as $classname2 )
            {
                $metadata->addResourceSetReferenceProperty( $metaclasses[$classname]['Type'], self::REFSET_IDENTIFIER . $classname2, $metaclasses[$classname2]['ResourceSet'] );
            }
        }
        $classstr = "class " . 'LatestNodes' . " { ";
        $classstr .= 'public $NodeID;';
        $classstr .= 'public $MainNodeID;';
        $classstr .= 'public $ContentObjectID;';
        $classstr .= 'public $ContentObjectName;';
        $classstr .= 'public $ParentNodeID;';
        $classstr .= 'public $ParentName;';
        $classstr .= 'public $URLAlias;';
        $classstr .= 'public $ClassIdentifier;';
        $classstr .= 'public $content_published = 0;';
        $classstr .= 'public $content_modified = 0;';
        
        foreach ( $list as $class2 )
        {
            $classstr .= 'public $' . self::REFSET_IDENTIFIER . $class2->attribute( 'identifier' ) . '= "";';
        }
        $classstr .= " };";
        eval( $classstr );
        $LatestNodesByListEntityType = $metadata->addEntityType( new ReflectionClass( 'LatestNodes' ), 'LatestNodes', 'eZPublish' );
        
        $LatestNodesResourceSet = $metadata->addResourceSet( 'LatestNodes', $LatestNodesByListEntityType );
        
        $metadata->addKeyProperty( $LatestNodesByListEntityType, 'NodeID', EdmPrimitiveType::INT32 );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'MainNodeID', EdmPrimitiveType::INT32 );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'ContentObjectID', EdmPrimitiveType::INT32 );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'ParentNodeID', EdmPrimitiveType::INT32 );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'ContentObjectName', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'ParentName', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'URLAlias', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'ClassIdentifier', EdmPrimitiveType::STRING );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'content_published', EdmPrimitiveType::DATETIME );
        $metadata->addPrimitiveProperty( $LatestNodesByListEntityType, 'content_modified', EdmPrimitiveType::DATETIME );
        
        foreach ( $classnames as $classname )
        {
            $metadata->addResourceSetReferenceProperty( $LatestNodesByListEntityType, self::REFSET_IDENTIFIER . $classname, $metaclasses[$classname]['ResourceSet'] );
        }
        
        foreach ( xrowODataUtils::plugins() as $plugin )
        {
            if ( class_exists( $plugin ) )
            {
                $plugin::metadata( $metadata );
            }
        }
        return $metadata;
    }
}