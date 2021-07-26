<?php

// my task is only to upload images

require_once 'conn.php';

if( Input::is_method('post'))
{
    Auth::use(['admin']);

    $gambar_list = $_FILES;
    $target_dir = '../image/';
    $output = [];

    foreach( $gambar_list as $id_soalan=>$gambar)
    {
        $check = getimagesize($gambar['tmp_name']);
        if( $check === false)
        {
            // file not an image
            $output[$id_soalan] = false;
            continue;
        }

        $extension = strtolower(pathinfo($gambar['name'], PATHINFO_EXTENSION));
        // randomize file name;
        $gambar['name'] = uniqid('i-') . '.'. $extension;
        $target_file = $target_dir.basename($gambar['name']);

        // upload file
        if( move_uploaded_file($gambar['tmp_name'], $target_file))
        {
            // save image name in db
            $query = "UPDATE soalan SET s_gambar = '{$gambar['name']}' WHERE s_id = '{$id_soalan}'";
            $res = $conn->query( $query );
            if( $res )
            {
                $output[$id_soalan] = true;
            }
            else
            {
                $output[$id_soalan] = false;
            }
        }
    }

    $result = [
        'success' => true,
        'message' => 'Gambar berjaya dimuatnaik',
        'data' => $output,
        'code' => 200
    ];
}

echo json_encode( $result);