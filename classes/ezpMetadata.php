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

require_once 'ODataProducer'.DIRECTORY_SEPARATOR.'Providers'.DIRECTORY_SEPARATOR.'Metadata'.DIRECTORY_SEPARATOR.'IDataServiceMetadataProvider.php';
use ODataProducer\Providers\Metadata\ServiceBaseMetadata;

//Begin Resource Classes
class ContentObject
{
    public $ContentObjectID;
    public $Name;
    public $Guid;
    public $Date;
    public $Nodes;

}

class Node
{
    public $NodeID;
    public $Name;
    public $Guid;
}

/**
 * Create eZ Publish metadata.
 * 
 * @category  Service
 * @package   WordPress
 * @author    Bibin Kurian <odataphpproducer_alias@microsoft.com>
 * @copyright 2011 Microsoft Corp. (http://www.microsoft.com)
 * @license   New BSD license, (http://www.opensource.org/licenses/bsd-license.php)
 * @version   Release: 1.0
 * @link      http://odataphpproducer.codeplex.com
 */
class ezpMetadata
{
    const REFSET_IDENTIFIER = 'RefSet';

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
        
        eval( 'class ODATAImage { public $xml = ""; }' );
        $ezimageComplexType = $metadata->addComplexType( new ReflectionClass( 'ODATAImage' ), 'ODATAImage', 'eZPublish', null );
        
        eval( 'class ODATAxrowMetaData { public $xml = ""; }' );
        $xrowmetadataComplexType = $metadata->addComplexType( new ReflectionClass( 'ODATAxrowMetaData' ), 'ODATAxrowMetaData', 'eZPublish', null );

        eval( 'class ODATAxrowArrayData { public $data = ""; }' );
        $xrowArrayDataComplexType = $metadata->addComplexType( new ReflectionClass( 'ODATAxrowArrayData' ), 'ODATAxrowArrayData', 'eZPublish', null );

        $listtofilter = eZContentClass::fetchList();
        $ini = eZINI::instance( "odata.ini" );
        $list = array();
        if( $ini->hasVariable( 'Settings', 'AvailableClassList' ) and $listtofilter and is_array( $ini->variable( 'Settings', 'AvailableClassList' ) ))
        {
            foreach( $listtofilter as $class )
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
            $classstr .= 'public $related_objects;';
            #$classstr .= 'public $Guid = "";';
            foreach ( $class->fetchAttributes() as $attribute )
            {
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
            #$metadata->addPrimitiveProperty($metaclasses[$class->attribute( 'identifier' )]['Type'], 'Guid', EdmPrimitiveType::GUID);

            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ContentObjectName', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ParentName', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'URLAlias', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'ClassIdentifier', EdmPrimitiveType::STRING );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'content_published', EdmPrimitiveType::DATETIME );
            $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'content_modified', EdmPrimitiveType::DATETIME );
            $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], 'related_objects', $xrowArrayDataComplexType );
            foreach ( $class->fetchAttributes() as $attribute )
            {
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
                    case 'xrowmetadata':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $xrowmetadataComplexType );
                    break;
                    case 'ezimage':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $ezimageComplexType );
                        break;
                    case 'ezxmltext':
                        $metadata->addComplexProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), $ezxmlComplexType );
                        #$metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::STRING );
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
                    case 'ezobjectrelation':
                        $metadata->addPrimitiveProperty( $metaclasses[$class->attribute( 'identifier' )]['Type'], $attribute->attribute( 'identifier' ), EdmPrimitiveType::INT16 );
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
        $classstr .= 'public $related_objects;';
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
        $metadata->addComplexProperty( $LatestNodesByListEntityType, 'related_objects', $xrowArrayDataComplexType );

        foreach ( $classnames as $classname )
        {
            $metadata->addResourceSetReferenceProperty( $LatestNodesByListEntityType, self::REFSET_IDENTIFIER . $classname, $metaclasses[$classname]['ResourceSet'] );
        }

        return $metadata;
    }
}