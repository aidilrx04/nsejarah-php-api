<?php

require_once 'conn.php';

if( Input::is_method( 'get' ) )
{
    Auth::use();
    $type = Auth::get_type();

    $id = Auth::get_data( $type === 'murid' ? 'm_id' : 'g_id' );
    $type = Input::get_data( 'type' );

    if( $type === 'verify' )
    {
        $result = [
            'success' => true,
            'message' => 'Verified',
            'data' => true,
            'code '=> 200
        ];
    }
    else
    {
        $data = $type === 'murid' ? get_murid( $id ) : get_guru( $id );

        $result = [
            'success' => true,
            'message' => 'Data berjaya dicapai',
            'data' => $data,
            'code' => 200
        ];
    }

    
}

echo json_encode( $result );