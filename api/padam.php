<?php

require_once 'conn.php';

expect( $data = Input::get_data( 'data' ), 'Tiada data untuk dipadam');
expect( $jenis = $data[0], 'Jenis tidak sah');


if( $jenis === 'kuiz' )
{
    Auth::use( ['admin', 'guru']);
    $final = padam( $data );
}
else
{
    Auth::use( ['admin'] );
    //authenticate here
    $final = padam( $data );
}


if( $final )
{
    $result['success'] = true;
    $result['message'] = 'Data berjaya dipadam';
    $result['code'] = 200;
}
else
{
    $result['message'] = 'Data gagal dipadam';
    $result['code'] = 400;
}

echo json_encode( $result );

function padam($data)
{
    global $conn;
    $query = "DELETE FROM {$data[0]} WHERE {$data[1]} = '{$data[2]}'";

    $res = $conn->query( $query );

    if( $res )
    {
        return true;
    }

    return false;
}