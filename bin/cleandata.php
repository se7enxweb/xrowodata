<?php
$db = eZDB::instance();
$offset = 0;
$length = 100;
$cond = array( 
    'data_type_string' => eZXMLTextType::DATA_TYPE_STRING 
);
$count = eZPersistentObject::count( eZContentObjectAttribute::definition(), $cond );
echo "There are $count attributes to reset.\n";
$output = new ezcConsoleOutput();
$bar = new ezcConsoleProgressbar( $output, $count / $length );

$limit = array( 
    'offset' => $offset , 
    'length' => $length 
);
$list = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(), null, $cond, null, $limit );

while ( ! empty( $list ) )
{
    $db->begin();
    /* var eZContentObjectAttribute */
    foreach ( $list as $attribute )
    {
        $text = $attribute->attribute( 'data_text' );
        $replace = preg_replace( "~<literal class=\"html\">(\s|.)*" . preg_quote( 'wuvPlayer.getEmbedPlayer("') . '([A-Z0-9]+)' . preg_quote( '");' ) . "(\s|.)*</literal>~Um", '<custom name="stream5" custom:title="stream5" custom:VideoID="${2}"/>', $text, -1, $count);
        if ( $count > 0 )
        {
            $attribute->setAttribute( 'data_text', $replace );
            $attribute->store();
        }
    }
    $db->commit();
    
    $bar->advance();
    $offset = $offset + $length;
    $limit = array( 
        'offset' => $offset , 
        'length' => $length 
    );
    $list = eZPersistentObject::fetchObjectList( eZContentObjectAttribute::definition(), null, $cond, null, $limit );
}