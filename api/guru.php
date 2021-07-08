<?php

require_once 'conn.php';

if( Input::is_method( 'get' ) )
{
    expect( $id_guru = Input::get_data( 'id_guru'), 'no id guru found', false);

    if( $id_guru )
    {
        $data = get_guru( $id_guru );
    }
    else
    {
        $limit = Input::get_data( 'limit' );
        $page = Input::get_data( 'page' );

        $limit = $limit ? $limit : 10;
        $page = $page ? $page : 1;

        $data = get_list_guru( $limit, $page );
    }

    if( $data )
    {
        $result = [
            'success' => true,
            'message' => 'Data berjaya dicapai',
            'data' => $data,
            'code' => 200
        ];
    }
    else
    {
        $result = [
            'success' => false,
            'message' => 'Tiada data dijumpai',
            'code' => 404
        ];
    }
}

echo json_encode( $result );


function get_list_guru( $limit = 10, $page = 1 )
{
    $list_guru = list_query( "SELECT g_id FROM guru ORDER BY g_id", $limit, $page );

    if( $list_guru )
    {
        $data = array_map( function ( $guru ) {
            return get_guru( $guru['g_id'] );
        }, $list_guru['data']);

        $list_guru['data'] = $data;

        return $list_guru;
    }
    return false;
}