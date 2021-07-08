<?php

require_once 'conn.php';

if (Input::is_method('get')) {
    expect($type = Input::get_data('type'), 'Invalid Request');
    expect($id = Input::get_data('id'), 'Invalid Request');

    switch ($type) {
        case 'jawapan':
            $data = get_jawapan($id);
            break;
        case 'soalan':
            $data = get_soalan($id);
            break;

        default:
            $data = false;
    }

    if($data )
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

echo json_encode($result);