<?php

class xrowExcludeNodeFilter
{

    function createSqlParts( $params )
    {
        $pieces = array();
        foreach ( $params['NodeIDs'] as $NodeID )
        {
            $pieces[] = ' ( ezcontentobject_tree.parent_node_id != ' . (int) $NodeID . ' AND ezcontentobject_tree.node_id != ' . (int) $NodeID . ' ) ';
        }
        if ( count( $pieces ) )
        {
            $sqlJoins = join( 'AND', $pieces );
            $sqlJoins .= ' AND ';
        }
        return array( 
            'tables' => '' , 
            'joins' => $sqlJoins , 
            'columns' => '' 
        );
    }
}