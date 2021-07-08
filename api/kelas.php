<?php

require_once 'conn.php';
if( Input::is_method( 'get' ) )
{
    $id_kelas = Input::get_data('id_kelas') ?? NULL;

    if( $id_kelas )
    {
        $data = get_kelas( $id_kelas );
    }
    else
    {
        $limit = Input::get_data('limit') ?? 10;
        $page = Input::get_data('page') ?? 1;

        $data = get_list_kelas( $limit, $page );
    }
    

    if( $data )
    {
        $result = [
            'success' => true,
            'message' => 'Data berjaya dicapai',
            'code' => 200,
            'data' => $data
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

function get_list_kelas( $limit = 10, $page = 1)
{
    $list_kelas = list_query( "SELECT * FROM kelas ORDER BY k_id", $limit, $page );
    
    if( $list_kelas )
    {
        return $list_kelas;
    }

    return false;
}