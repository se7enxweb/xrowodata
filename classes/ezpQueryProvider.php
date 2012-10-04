<?php

use ODataProducer\UriProcessor\ResourcePathProcessor\SegmentParser\KeyDescriptor;
use ODataProducer\Providers\Metadata\ResourceSet;
use ODataProducer\Providers\Metadata\ResourceProperty;
use ODataProducer\Providers\Query\IDataServiceQueryProvider;

class ezpQueryProvider implements IDataServiceQueryProvider
{
    private $special_resources = array( 
        'LatestNodes' , 
        'Nodes' , 
        'ContentObjects' 
    );

    /**
     * Gets collection of entities belongs to an entity set
     * 
     * @param ResourceSet $resourceSet The entity set whose 
     * entities needs to be fetched
     * 
     * @return array(Object)
     */
    public function getResourceSet( ResourceSet $resourceSet )
    {
        eZDebug::writeDebug( "Enter", __METHOD__ . '()' );
        $resourceSetName = $resourceSet->getName();
        /* @var $class eZContentClass */
        $class = eZContentClass::fetchByIdentifier( $resourceSetName );
        
        if ( ! in_array( $resourceSetName, $this->special_resources ) and $class === null )
        {
            die( '(' . __METHOD__ . ') Unknown resource set ' . $resourceSetName );
        }
        
        $returnResult = array();
        if ( $class )
        {
            $params = array( 
                'ClassFilterType' => 'include' , 
                'ClassFilterArray' => array( 
                    $class->attribute( 'identifier' ) 
                ) 
            );
            self::addParams( $params );
            if ( ! isset( $params['SortBy'] ) )
            {
                $node = eZContentObjectTreeNode::fetch( 2 );
                $params['SortBy'] = $node->sortArray();
            }
            $list = eZContentObjectTreeNode::subTreeByNodeID( $params, 2 );
            $returnResult = $this->_serializeContentObjectTreeNodes( $list );
            return $returnResult;
        }
        return $returnResult;
    }

    /**
     * Gets an entity instance from an entity set identifed by a key
     * 
     * @param ResourceSet   $resourceSet   The entity set from which an entity 
     * needs to be fetched
     * @param KeyDescriptor $keyDescriptor The key to identify the entity 
     * to be fetched
     * 
     * @return Object/NULL Returns entity instance if found else null
     */
    public function getResourceFromResourceSet( ResourceSet $resourceSet, KeyDescriptor $keyDescriptor )
    {
        eZDebug::writeDebug( "Enter", __METHOD__ . '()' );
        $resourceSetName = $resourceSet->getName();
        
        /* @var $class eZContentClass */
        $class = eZContentClass::fetchByIdentifier( $resourceSetName );
        if ( ! in_array( $resourceSetName, $this->special_resources ) and eZContentClass::fetchByIdentifier( $resourceSetName ) === null )
        {
            die( '(' . __METHOD__ . ') Unknown resource set ' . $resourceSetName );
        }
        
        $namedKeyValues = $keyDescriptor->getValidatedNamedValues();
        
        if ( isset( $namedKeyValues['NodeID'][0] ) and $node = eZContentObjectTreeNode::fetch( (int) $namedKeyValues['NodeID'][0] ) )
        {
            if( $node instanceof eZContentObjectTreeNode )
            {            
                if ( in_array( $resourceSetName, $this->special_resources ) )
                {
                    $returnResult = $this->_serializeContentObjectTreeNode( $node, $resourceSetName );
                }
                else
                {
                    $returnResult = $this->_serializeContentObjectTreeNode( $node );
                }
            }
            else
            {
                eZDebug::writeError( array( 'node is not an instance of eZContentObjectTreeNode', $node ), __METHOD__ );
            }
            $ini = eZINI::instance( 'odata.ini' );
            if ( $ini->hasVariable( 'AfterModificationCacheTTL-' . $class->Identifier, 'AfterModificationCacheTTL' ) )
            {
                $change = time() - $returnResult->content_modified;
                $cachearray = $ini->variable( 'AfterModificationCacheTTL-' . $class->Identifier, 'AfterModificationCacheTTL' );
                $keys = array_reverse( sort( array_keys( $cachearray, SORT_NUMERIC ) ) );
                foreach ( $keys as $key )
                {
                    if ( $change < $key )
                    {
                        $GLOBALS['EZ_ODATA_CACHE_TIME'] = (int) $cachearray[$key];
                    }
                }
            }
            elseif ( $ini->hasVariable( 'AfterModificationCacheTTL-' . $class->Identifier, 'DefaultCacheTTL' ) )
            {
                $GLOBALS['EZ_ODATA_CACHE_TIME'] = (int) $ini->variable( 'AfterModificationCacheTTL-' . $class->Identifier, 'DefaultCacheTTL' );
            }
            
            return $returnResult;
        }
        
        return array();
    }

    static function addSearchParams( &$params )
    {
        $params['AllowEmptySearch'] = false;
        $parameters['IgnoreVisibility'] = false;
        $parameters['Limitation'] = null;
        
        #$params['SortArray'] = array( array( 
        #    'relevance' , 
        #    'desc' 
        #) );
        $params['SortArray'] = array( array( 
            'published' , 
            'desc' 
        ) );
        
        if ( isset( $_GET['$top'] ) and (int) $_GET['$top'] <= 100 )
        {
            $params['SearchLimit'] = (int) $_GET['$top'];
        }
        else
        {
            $params['SearchLimit'] = 10;
        }
        if ( isset( $_GET['$skip'] ) )
        {
            $params['SearchOffset'] = (int) $_GET['$skip'];
        }
        if ( isset( $_GET['search'] ) )
        {
            $params['Depth'] = false;
            $params['DepthOperator'] = false;
        }
    }

    static function addParams( &$params )
    {
        if ( isset( $_GET['$orderby'] ) )
        {
            $params['SortBy'] = self::ezpOrderByQueryPart( $_GET['$orderby'] );
        }
        if ( isset( $_GET['$top'] ) and (int) $_GET['$top'] <= 100 )
        {
            $params['Limit'] = (int) $_GET['$top'];
        }
        else
        {
            $params['Limit'] = 10;
        }
        if ( isset( $_GET['exclude_parent'] ) )
        {
            
            $params['ExtendedAttributeFilter'] = array( 
                'id' => 'ExcludeNode' , 
                'params' => array( 
                    'NodeIDs' => explode( ',', $_GET['exclude_parent'] ) 
                ) 
            );
        }
        if ( isset( $_GET['$skip'] ) )
        {
            $params['Offset'] = (int) $_GET['$skip'];
        }
        if ( ! isset( $_GET['tree'] ) )
        {
            $params['Depth'] = '1';
            $params['DepthOperator'] = 'le';
        }
        else
        {
            $params['Depth'] = false;
            $params['DepthOperator'] = false;
        }
    }

    static function ezpOrderByQueryPart( $orderby )
    {
        list ( $attribute, $order ) = explode( ' ', $orderby );
        switch ( $order )
        {
            case 'desc':
                $order = false;
                break;
            case 'asc':
                $order = true;
                break;
            default:
                $order = true;
                break;
        }
        
        switch ( $attribute )
        {
            case 'content_published':
                $attribute = 'published';
                break;
            case 'content_modified':
                $attribute = 'modified';
                break;
            case 'content_state':
                $attribute = 'state';
                break;
            case 'section':
                $attribute = 'section';
                break;
            case 'priority':
                $attribute = 'priority';
                break;
            case 'path':
                $attribute = 'path';
                break;
            case 'owner':
                $attribute = 'owner';
                break;
            case 'name':
                $attribute = 'name';
                break;
            case 'class_name':
                $attribute = 'class_name';
                break;
            case 'class_identifier':
                $attribute = 'class_identifier';
                break;
            case 'depth':
                $attribute = 'depth';
                break;
            default:
                throw new Exception( "Wrong attribute name" );
                break;
        }

        return array( 
            $attribute , 
            $order 
        );
    
    }

    /**
     * Get related resource set for a resource
     * 
     * @param ResourceSet      $sourceResourceSet    The source resource set
     * @param mixed            $sourceEntityInstance The resource
     * @param ResourceSet      $targetResourceSet    The resource set of 
     * the navigation property
     * @param ResourceProperty $targetProperty       The navigation property to be 
     * retrieved
     * 
     * @return array(Objects)/array() Array of related resource if exists, if no 
     * related resources found returns empty array
     */
    public function getRelatedResourceSet( ResourceSet $sourceResourceSet, $sourceEntityInstance, ResourceSet $targetResourceSet, ResourceProperty $targetProperty )
    {
        eZDebug::writeDebug( "Enter", __METHOD__ . '()' );
        
        $srcClass = get_class( $sourceEntityInstance );
        $srcClass = str_replace( ezpMetadata::REFSET_IDENTIFIER, '', $srcClass );
        
        $navigationPropName = $targetProperty->getName();
        $navigationPropName = str_replace( ezpMetadata::REFSET_IDENTIFIER, '', $navigationPropName );
        
        $result = array();
        if ( isset( $_GET['list'] ) )
        {
            $NodeIDs = explode( ',', $_GET['list'] );
            $list = array();
            foreach ( $NodeIDs as $NodeID )
            {
                $params = array( 
                    'ClassFilterType' => 'include' , 
                    'ClassFilterArray' => array( 
                        $navigationPropName 
                    ) 
                );
                $params['SortBy'] = self::ezpOrderByQueryPart( 'content_published desc' );
                $params['Limit'] = 1;
                $params['Depth'] = false;
                $params['DepthOperator'] = false;
                
                $list2 = eZContentObjectTreeNode::subTreeByNodeID( $params, (int) $NodeID );
                if ( is_array( $list2 ) )
                {
                    $list = array_merge( $list, $list2 );
                }
            }
        }
        elseif ( isset( $_GET['search'] ) )
        {
            $searchArray = eZSearch::buildSearchArray();
            $params = array();
            $class = eZContentClass::fetchByIdentifier( $navigationPropName );
            
            $params['SearchContentClassID'] = $class->ID;
            
            self::addSearchParams( $params );
            $searchResult = eZSearch::search( $_GET['search'], $params );
            if ( $searchResult === false )
            {
                throw new Exception( "Search return invalid result" );
            }
            
            $list = $searchResult['SearchResult'];
        }
        else
        {
            $params = array( 
                'ClassFilterType' => 'include' , 
                'ClassFilterArray' => array( 
                    $navigationPropName 
                ) 
            );
            self::addParams( $params );
            if ( ! isset( $params['SortBy'] ) )
            {
                $node = eZContentObjectTreeNode::fetch( $sourceEntityInstance->NodeID );
                $params['SortBy'] = $node->sortArray();
            }
            $list = eZContentObjectTreeNode::subTreeByNodeID( $params, (int) $sourceEntityInstance->NodeID );
        }
        $result = $this->_serializeContentObjectTreeNodes( $list );
        return $result;
    }

    /**
     * Gets a related entity instance from an entity set identifed by a key
     * 
     * @param ResourceSet      $sourceResourceSet    The entity set related to
     * the entity to be fetched.
     * @param object           $sourceEntityInstance The related entity instance.
     * @param ResourceSet      $targetResourceSet    The entity set from which
     * entity needs to be fetched.
     * @param ResourceProperty $targetProperty       The metadata of the target 
     * property.
     * @param KeyDescriptor    $keyDescriptor        The key to identify the entity 
     * to be fetched.
     * 
     * @return Object/NULL Returns entity instance if found else null
     */
    public function getResourceFromRelatedResourceSet( ResourceSet $sourceResourceSet, $sourceEntityInstance, ResourceSet $targetResourceSet, ResourceProperty $targetProperty, KeyDescriptor $keyDescriptor )
    {
        eZDebug::writeDebug( "Enter", __METHOD__ . '()' );
        $result = array();
        throw new Exception( __METHOD__ . '() not implemented' );
        return empty( $result ) ? null : $result[0];
    }

    /**
     * Get related resource for a resource
     * 
     * @param ResourceSet      $sourceResourceSet    The source resource set
     * @param mixed            $sourceEntityInstance The source resource
     * @param ResourceSet      $targetResourceSet    The resource set of 
     * the navigation property
     * @param ResourceProperty $targetProperty       The navigation property to be 
     * retrieved
     * 
     * @return Object/null The related resource if exists else null
     */
    public function getRelatedResourceReference( ResourceSet $sourceResourceSet, $sourceEntityInstance, ResourceSet $targetResourceSet, ResourceProperty $targetProperty )
    {
        eZDebug::writeDebug( "Enter", __METHOD__ . '()' );
        $result = null;
        throw new Exception( __METHOD__ . '() not implemented' );
        return $result;
    }

    /**
     * Serialize the mysql result array into Category objects
     * 
     * @param array(array) $result result of the mysql query
     * 
     * @return array(Object)
     */
    private function _serializeContentObjects( $result )
    {
        $cats = array();
        while ( $record = mysql_fetch_array( $result, MYSQL_ASSOC ) )
        {
            $cats[] = $this->_serializeContentObject( $record );
        }
        
        return $cats;
    }

    /**
     * Serialize the mysql row into Category object
     * 
     * @param array $record each category row
     * 
     * @return Object
     */
    private function _serializeContentObject( $record )
    {
        $cat = new ContentObject();
        $cat->ContentObjectID = $record['id'];
        $cat->Name = $record['name'];
        if ( ! is_null( $record['published'] ) )
        {
            $dateTime = new DateTime( '@' . $record['published'] );
            $post->Date = $dateTime->format( 'Y-m-d\TH:i:s' );
        }
        else
        {
            $post->Date = null;
        }
        return $cat;
    }

    /**
     * Serialize the mysql result array into Category objects
     * 
     * @param array(array) $result result of the mysql query
     * 
     * @return array(Object)
     */
    private function _serializeContentObjectTreeNodes( $list = array() )
    {
        $GLOBALS['ODATARecursionCounter'] = 0;
        $result = array();
        
        foreach ( $list as $item )
        {
            if( $item instanceof eZContentObjectTreeNode )
            {
                $result[] = $this->_serializeContentObjectTreeNode( $item );
            }
            else
            {
                eZDebug::writeError( array( 'item is not an instance of eZContentObjectTreeNode', $item ), __METHOD__ );
            }
        }
        return $result;
    }

    private function _serializeContentObjectTreeNode( eZContentObjectTreeNode $node, $ClassSpecial = false )
    {
        $GLOBALS['ODATARecursionCounter']++;
        $co = $node->attribute( 'object' );
        if ( ! $ClassSpecial )
        {
            $class = $node->attribute( 'class_identifier' );
            if ( class_exists( $class ) )
            {
                $object = new $class();
            }
            else
            {
                throw new Exception( "Class $class not serialisable" );
            }
        }
        else
        {
            $object = new $ClassSpecial();
        }
        $object->NodeID = $node->NodeID;
        $object->MainNodeID = $node->MainNodeID;
        $object->ContentObjectID = $node->ContentObjectID;
        $object->ContentObjectName = $node->attribute( 'name' );
        $parent = $node->attribute( 'parent' );
        $object->ParentNodeID = $parent->NodeID;
        $object->ParentName = $parent->attribute( 'name' );
        $object->URLAlias = $node->attribute( 'url_alias' );
        $object->ClassIdentifier = $node->attribute( 'class_identifier' );
        $object->content_published = $co->attribute( 'published' );
        $object->content_modified = $co->attribute( 'modified' );
        self::transformRelatedContentObjectsToRemoteLinks( $co->attribute( 'related_contentobject_array' ), $object );
        $dm = $node->attribute( 'data_map' );

        /* @var $attribute eZContentObjectAttribute */
        foreach ( $dm as $key => $attribute )
        {
            if ( $attribute )
            {
                switch ( $attribute->DataTypeString )
                {
                    case 'ezimage':
                        $doc = new DOMDocument( '1.0', 'utf-8' );
                        
                        if ( $attribute->hasContent() )
                        {
                            $content = $attribute->content();
                            $alternativeText = $content->attribute( 'alternative_text' );

                            $doc->formatOutput = true;
                            $root = $doc->createElement( 'ezimage' );
                            
                            $root = $doc->appendChild( $root );
                            
                            if ( $alternativeText )
                            {
                                $textNode = $doc->createElement( 'text' );
                                $textNode = $root->appendChild( $textNode );
                                
                                $text = $doc->createTextNode( $alternativeText );
                                $text = $textNode->appendChild( $text );
                            }
                            foreach ( $content->attributes() as $alias )
                            {
                                $ini = eZINI::instance( "odata.ini" );
                                if ( $ini->hasVariable( 'Settings', 'ImageAliasList' ) and ! in_array( $alias, $ini->variable( 'Settings', 'ImageAliasList' ) ) )
                                {
                                    continue;
                                }
                                switch ( $alias )
                                {
                                    case 'alternative_text':
                                    case 'original_filename':
                                    case 'is_valid':
                                    break;
                                    default:
                                        $data = $content->attribute( $alias );
                                        if ( $data['url'] )
                                        {
                                            $urlNode = $doc->createElement( 'alias' );
                                            $urlNode->setAttribute( 'type', $alias );
                                            $urlNode = $root->appendChild( $urlNode );
                                            
                                            $url = $doc->createTextNode( $data['url'] );
                                            $url = $urlNode->appendChild( $url );
                                        }
                                    break;
                                }
                            }
                        }
                        $object->{$key} = $doc;
                        break;

                    case 'ezauthorrelation':
                        $text = "<author>";
                        if ( trim( $attribute->attribute( 'data_text' ) ) == '' )
                        {
                            $obj = eZContentObject::fetch( $co->attribute('owner_id') );
                        }
                        else
                        {
                            $obj = eZContentObject::fetch( $attribute->attribute( 'data_int' ) );
                        }
                        if ( $obj )
                        {
                            $dm = $obj->dataMap();
                            if( $dm['code']->hasContent() )
                            {
                                $text .= "<short>" . $dm['code']->attribute( 'data_text' ) . "</short>";
                            }
                            $text .= "<name>" . $obj->attribute( 'name' ) . "</name>";
                            
                            foreach ( $dm as $attribute2 )
                            {
                                if ( $attribute2->DataTypeString == 'ezuser' and $attribute2->hasContent() )
                                {
                                    if( $attribute2->content()->attribute( 'is_enabled' ) )
									{
									    $text .= "<email>" . $attribute2->content()->attribute( 'email' ) . "</email>";
									}
                                }
                                elseif ( $attribute2->DataTypeString == 'ezimage' and $attribute2->hasContent() )
                                {
                                    $content = $attribute2->content();
                                    foreach ( $content->attributes() as $alias )
                                    {
                                        $ini = eZINI::instance( "odata.ini" );
                                        if ( $ini->hasVariable( 'Settings', 'ImageAliasList' ) and ! in_array( $alias, $ini->variable( 'Settings', 'ImageAliasList' ) ) )
                                        {
                                            continue;
                                        }
                                        switch ( $alias )
                                        {
                                            case 'alternative_text':
                                            case 'original_filename':
                                            case 'is_valid':
                                                break;
                                            default:
                                                $data = $content->attribute( $alias );
                                                if ( $data['url'] )
                                                {
                                                    $text .= "<alias type=\"$alias\">" . $data['url'] . "</alias>";
                                                }
                                                break;
                                        }
                                    }
                                }
                            }
                        }
                        $text .= "</author>";
                        $object->{$key} = $text;
                        break;

                    case 'ezboolean':
                        if( $attribute->content() )
                        {
                            $object->{$key} = true;
                        }
                        else
                        {
                            $object->{$key} = false;
                        }
                        break;

                    case 'xrowmetadata':
                        $xmlString = $attribute->attribute( 'data_text' );
                        $doc = new DOMDocument( '1.0', 'utf-8' );
                        $doc->loadXML( $xmlString );
                        $object->{$key} = $doc;
                        break;

                    case 'ezxmltext':
                        $xmlString = $attribute->attribute( 'data_text' );
                        $doc = new DOMDocument( '1.0', 'utf-8' );
                        if ( $xmlString != '' and $doc->loadXML( $xmlString ) )
                        {
                            $links = $doc->getElementsByTagName( 'link' );
                            $embeds = $doc->getElementsByTagName( 'embed' );
                            $objects = $doc->getElementsByTagName( 'object' );
                            $embedsInline = $doc->getElementsByTagName( 'embed-inline' );
                            
                            self::transformLinksToRemoteLinks( $links, $object );
                            self::transformLinksToRemoteLinks( $embeds, $object );
                            self::transformLinksToRemoteLinks( $objects, $object );
                            self::transformLinksToRemoteLinks( $embedsInline, $object );
                        }

                        $object->{$key} = $doc;
                        break;

                    default:
                        $object->{$key} = $attribute->toString();
                        break;
                }
                if ( $object->{$key} === null )
                {
                    $object->{$key} = '';
                }
            }
        }
        $GLOBALS['ODATARecursionCounter']--;
        return $object;
    }

    static function transformLinksToRemoteLinks( DOMNodeList $nodeList, &$objectstore )
    {
        foreach ( $nodeList as $node )
        {
            $linkID = $node->getAttribute( 'url_id' );
            $isObject = ( $node->localName == 'object' );
            $objectID = $isObject ? $node->getAttribute( 'id' ) : $node->getAttribute( 'object_id' );
            $nodeID = $node->getAttribute( 'node_id' );
            
            if ( $linkID )
            {
                $urlObj = eZURL::fetch( $linkID );
                if ( ! $urlObj ) // an error occured
                {
                    continue;
                }
                $url = $urlObj->attribute( 'url' );
                $node->setAttribute( 'href', $url );
                $node->removeAttribute( 'url_id' );
            }
            elseif ( $objectID )
            {
                $object = eZContentObject::fetch( $objectID, true );
                
                if ( $object instanceof eZContentObject )
                {
                    $node->setAttribute( 'object_remote_id', $object->attribute( 'remote_id' ) );
                    $node->setAttribute( 'node_id', $object->attribute( 'main_node_id' ) );
                    $node->setAttribute( 'contentclass', $object->contentClassIdentifier() );
                    if ( $GLOBALS['ODATARecursionCounter'] === 1 && class_exists( $object->contentClassIdentifier() ) )
                    {
                        $objMainNode = $object->mainNode();
                        if( $objMainNode instanceof eZContentObjectTreeNode )
                        {
                            $objecttmp = self::_serializeContentObjectTreeNode( $objMainNode );
                            $objectstore->related_objects[] = $objecttmp;
                        }
                        else
                        {
                            eZDebug::writeError( array( 'objMainNode is not an instance of eZContentObjectTreeNode', $object ), __METHOD__ );
                        }
                    }
                }

                if ( $isObject )
                {
                    $node->removeAttribute( 'id' );
                }
                else
                {
                    $node->removeAttribute( 'object_id' );
                }
            }
            elseif ( $nodeID )
            {
                
                $nodeData = eZContentObjectTreeNode::fetch( $nodeID );
                $node->removeAttribute( 'node_id' );
                if ( $nodeData instanceof eZContentObjectTreeNode )
                {
                    $node->setAttribute( 'node_remote_id', $nodeData->attribute( 'remote_id' ) );
                    $node->setAttribute( 'node_id', $nodeData->attribute( 'node_id' ) );
                    $node->setAttribute( 'contentclass', $nodeData->classIdentifier() );
                    if ( $GLOBALS['ODATARecursionCounter'] === 1 && class_exists( $nodeData->classIdentifier() ) )
                    {
                        if( $nodeData instanceof eZContentObjectTreeNode )
                        {
                            $objecttmp = self::_serializeContentObjectTreeNode( $nodeData );
                            $objectstore->related_objects[] = $objecttmp;
                        }
                        else
                        {
                            eZDebug::writeError( array( 'nodeData is not an instance of eZContentObjectTreeNode', $nodeData ), __METHOD__ );
                        }
                        
                    }
                }
            }
        }
    }

    static function transformRelatedContentObjectsToRemoteLinks( $objectList, &$objectstore )
    {
        foreach ( $objectList as $object )
        {
            if( $object instanceof eZContentObject )
            {
                $objectID = $object->attribute( 'id' );
                if ( $GLOBALS['ODATARecursionCounter'] === 1 && class_exists( $object->contentClassIdentifier() ) )
                {
                    $objMainNode = $object->mainNode();
                    if( $objMainNode instanceof eZContentObjectTreeNode )
                    {
                        $objecttmp = self::_serializeContentObjectTreeNode( $objMainNode );
                        $objectstore->related_objects[] = $objecttmp;
                    }
                    else
                    {
                        eZDebug::writeError( array( 'objMainNode is not an instance of eZContentObjectTreeNode', $object ), __METHOD__ );
                    }
               }
            }
        }
    }

    /**
     * Serialize the mysql result array into Category objects
     * 
     * @param array(array) $result result of the mysql query
     * 
     * @return array(Object)
     */
    private function _serializeNodes( $result )
    {
        $cats = array();
        while ( $record = mysql_fetch_array( $result, MYSQL_ASSOC ) )
        {
            $cats[] = $this->_serializeNode( $record );
        }
        
        return $cats;
    }

    /**
     * Serialize the mysql row into Category object
     * 
     * @param array $record each category row
     * 
     * @return Object
     */
    private function _serializeNode( $record )
    {
        $cat = new Node();
        $cat->NodeID = $record['node_id'];
        $cat->Name = $record['path_string'];
        
        return $cat;
    }
}