<?php

require_once 'conn.php';

if (Input::is_method('get')) {
    $id_guru = Input::get_data('id_guru');
    $id_ting = Input::get_data('id_ting');

    $limit = Input::get_data('limit') ?? 10;
    $page = Input::get_data('page') ?? 1;


    if ($id_guru) {
        $data = get_ting_guru($id_guru, $limit, $page);
    } else if ($id_ting) {
        $data = get_ting($id_ting);
    } else {
        $data = get_list_ting($limit, $page);
    }

    if ($data) {
        $result = [
            'success' => true,
            'message' => 'Data berjaya dicapai',
            'data' => $data,
            'code' => 200
        ];
    } else {
        $result = [
            'success' => false,
            'message' => 'Tiada data dijumpai',
            'code' => 404
        ];
    }
}

echo json_encode($result);


function get_ting_guru($id_guru, $limit = 10, $page = 1)
{
    $list_ting = list_query("SELECT kt_id FROM kelas_tingkatan WHERE kt_guru = '{$id_guru}'", $limit, $page);

    if ($list_ting) {
        $list_ting['data'] = array_map(function ($ting) {
            return get_ting($ting['kt_id']);
        }, $list_ting['data']);
        
        return $list_ting;
    }

    return false;
}


function get_list_ting($limit = 10, $page = 1)
{
    $list_ting = list_query("SELECT kt_id FROM kelas_tingkatan ORDER BY kt_id", $limit, $page);

    if ($list_ting) {
        $data = array_map(function ($ting) {
            return get_ting($ting['kt_id']);
        }, $list_ting['data']);

        $list_ting['data'] = $data;

        return $list_ting;
    }

    return false;
}