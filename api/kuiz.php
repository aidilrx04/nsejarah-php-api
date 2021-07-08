<?php

require 'conn.php';

if (Input::is_method('get')) {
    $id_kuiz = Input::get_data('id_kuiz') ?? NULL;
    $id_guru = Input::get_data('id_guru') ?? NULL;
    $id_ting = Input::get_data('id_ting') ?? NULL;
    $id_kuiz = Input::get_data('id_kuiz') ?? NULL;

    $limit = Input::get_data('limit') ?? 10;
    $page = Input::get_data('page') ?? 1;

    if ($id_kuiz) {
        $data = get_kuiz($id_kuiz);
    } else if ($id_guru) {
        $data = get_kuiz_guru($id_guru, $limit, $page);
    } else if ($id_ting) {
        $data = get_kuiz_ting($id_ting, $limit, $page);
    } else {
        $data = get_list_kuiz($limit, $page);
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

function get_list_kuiz($limit = 10, $page = 1)
{
    $list_kuiz = list_query("SELECT kz_id FROM kuiz ORDER BY kz_id", $limit, $page);

    if ($list_kuiz) {
        $data = array_map(function ($kuiz) {
            return get_kuiz($kuiz['kz_id']);
        }, $list_kuiz['data']);

        $list_kuiz['data'] = $data;

        return $list_kuiz;
    }

    return false;
}

function get_kuiz_guru($id_guru, $limit = 10, $page = 1)
{
    $list_kuiz_guru = list_query(
        "SELECT kz_id FROM kuiz WHERE kz_guru = '$id_guru'",
        $limit,
        $page
    );

    if ($list_kuiz_guru) {
        $list_kuiz_guru['data'] = array_map(function ($kuiz) {
            return get_kuiz($kuiz['kz_id']);
        }, $list_kuiz_guru['data']);

        return $list_kuiz_guru;
    }

    return false;
}

function get_kuiz_ting($id_ting, $limit = 10, $page = 1)
{
    $list_kuiz = list_query(
        "SELECT kz_id FROM kuiz WHERE kz_ting = '$id_ting'",
        $limit,
        $page
    );

    if ($list_kuiz) {
        $list_kuiz['data'] = array_map(function ($kuiz) {
            return get_kuiz($kuiz['kz_id']);
        }, $list_kuiz['data']);

        return $list_kuiz;
    }

    return false;
}