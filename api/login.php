<?php

require_once "conn.php";

if( Input::is_method( 'post' ) )
{
    $data = Input::get_data( 'data' );
    $data_login = login( $data['nokp'], $data['katalaluan'], $data['jenis'] );
    $login_success = !!$data_login;

    if( $login_success )
    {
        $jwt_token = Auth::encode( $data_login );
        $result = [
            'success' => true,
            'message' => 'Login Berjaya',
            'data' => [
                'data' => $data_login,
                'token' => $jwt_token
            ],
            'code' => 200
        ];
    }
    else
    {
        $result = [
            'success' => false,
            'message' => 'Login Gagal',
            'code' => 400
        ];
    }
}


echo json_encode( $result );

/**
 * Login func
 * @param string $nokp 12 aksara NoKp
 * @param string $katalaluan Katalaluan pengguna
 * @param string $jenis Jenis login(murid/guru)
 * @return array|boolean Data pengguna jika berjaya
 */
function login( $nokp, $katalaluan, $jenis = 'murid' )
{
    $login_success = false;

    if( $jenis === 'murid' )
    {
        $query = "SELECT * FROM murid WHERE m_nokp = ? AND m_katalaluan = ?";        
    }
    else 
    {
        $query =  "SELECT * FROM guru WHERE g_nokp = ? AND g_katalaluan = ?";
    }

    $result = single_get_query( $query, $nokp, $katalaluan );
    $login_success = !!$result;

    if( $login_success )
    {
        if( $jenis === 'murid' )
        {
            return get_murid( $result['m_id'] );
        }
        else
        {
            return get_guru( $result['g_id'] );
        }
    }

    return false;
}