<?php

/** 
 * @author bjoern
 * 
 * 
 */
class xrowObject2XML
{
    var $xmlResult;

    function __construct( $rootNode )
    {
        $this->xmlResult = new SimpleXMLElement( "<$rootNode></$rootNode>" );
    }

    private function xml_join( $root, $append )
    {
        if ( $append )
        {
            if ( strlen( trim( (string) $append ) ) == 0 )
            {
                $xml = $root->addChild( $append->getName() );
                foreach ( $append->children() as $child )
                {
                    self::xml_join( $xml, $child );
                }
            }
            else
            {
                $xml = $root->addChild( $append->getName(), (string) $append );
            }
            foreach ( $append->attributes() as $n => $v )
            {
                $xml->addAttribute( $n, $v );
            }
        }
    }

    private function iteratechildren( $object, $xml )
    {
        if ( ! is_object( $object ) and ! is_array( $object ) )
        {
            return false;
        }
        foreach ( $object as $name => $value )
        {
            if ( is_string( $value ) || is_numeric( $value ) )
            {
                if ( is_string( $value ) )
                {
                    $value = str_replace( '<?xml version="1.0" encoding="utf-8"?>' . "\n", '', $value );
                    $value = str_replace( '<?xml version="1.0" encoding="UTF-8"?>' . "\n", '', $value );
                    $value = str_replace( '<?xml version="1.0"?>' . "\n", '', $value );
                }
                $xml->$name = $value;
            }
            elseif ( $value instanceof DOMDocument )
            {
                $cxml = $xml->addChild( $name );
				$xml_string = $value->saveXML();
				$xml_string = preg_replace( '/&/', '&amp;', $xml_string );
				$xml_tmp = DOMDocument::loadXML($xml_string);
                $xml_append = simplexml_import_dom( $xml_tmp );
                self::xml_join( $cxml, $xml_append );
            }
            else
            {
                if (is_numeric($name))
                {
                    $child = $xml->addChild( 'item' );
                    $this->iteratechildren( $value, $child );
                }
                else
                {
                    $xml->{$name} = null;
                    $this->iteratechildren( $value, $xml->{$name} );
                }
            }
        }
    }

    function toXml( $object )
    {
        $this->iteratechildren( $object, $this->xmlResult );
        return $this->xmlResult;
    }

    function toXMLString( $object )
    {
        $this->iteratechildren( $object, $this->xmlResult );
        $xml = $this->xmlResult->asXML();
        $xml = str_replace( '<?xml version="1.0" encoding="utf-8"?>' . "\n", '', $xml );
        $xml = str_replace( '<?xml version="1.0" encoding="UTF-8"?>' . "\n", '', $xml );
        $xml = str_replace( '<?xml version="1.0"?>' . "\n", '', $xml );
        return $xml;
    }
}