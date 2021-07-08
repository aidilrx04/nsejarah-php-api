<?php

require 'conn.php';

sleep( 2 );

if (Input::is_method('get')) {
    $keyword = Input::get_data('keyword');
    $keyword = $keyword === 'null' ? NULL : $keyword;
    $limit = Input::get_data('limit') ?? 10;
    $page = Input::get_data('page')  ?? 1;
    $order = Input::get_data('order') ?? "DESC";

    $keyword_query = $keyword ? " WHERE kz_nama LIKE '%{$keyword}%' " : '';


    if ($order === 'random') {
        $order = ' RAND() ';
    } else {
        $order = " kz_tarikh {$order}";
    }

    $query = "SELECT kz_id FROM kuiz {$keyword_query} ORDER BY {$order}";


    $data = list_query($query, $limit, $page);

    if ($data) {
        $data['data'] = array_map(function ($kuiz) {
            return get_kuiz($kuiz['kz_id']);
        }, $data['data']);
        $result = [
            'success' => true,
            'message' => 'Data dijumpai',
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