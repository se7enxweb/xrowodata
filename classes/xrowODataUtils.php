<?php

class xrowODataUtils
{
    static public function ImageAliasList()
    {
        $return = array();
        $ini = eZINI::instance( "odata.ini" );
        if ( $ini->hasVariable( 'Settings', 'ImageAliasList' ) )
        {
            foreach ( $ini->variable( 'Settings', 'ImageAliasList' ) as $alias )
            {
                $return[] = $alias;
            }
        }
        else
        {
        	$return[] = 'original';
        }
        return $return;
    }
    static public function unsupportedDatatypes()
    {
        $return = array();
        $ini = eZINI::instance( "odata.ini" );
        if ( $ini->hasVariable( 'Settings', 'UnsupportedDatatypeList' ) )
        {
            foreach ( $ini->variable( 'Settings', 'UnsupportedDatatypeList' ) as $alias )
            {
                $return[] = $alias;
            }
        }
        return $return;
    }
}
