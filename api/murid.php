<?php

require_once 'conn.php';

if( Input::is_method( 'get' ) )
{
    $id_murid = Input::get_data( 'id_murid');
    $id_ting = Input::get_data( 'id_ting');

    $limit = Input::get_data( 'limit') ?? 10;
    $page = Input::get_data( 'page') ?? 1;

    if( $id_murid )
    {
        $data = get_murid( $id_murid );
    }
    else if( $id_ting )
    {
        $data = get_ting_murid( $id_ting );
    }
    else
    {
        $data = get_list_murid( $limit, $page );
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


function get_list_murid($limit = 10, $page = 1)
{
    $list_murid = list_query( "SELECT m_id FROM murid ORDER BY m_id", $limit, $page);

    if( $list_murid )
    {
        $data = array_map( function ( $murid ) {
            return get_murid( $murid['m_id'] );
        }, $list_murid['data']);

        $list_murid['data'] = $data;

        return $list_murid;
    }
}