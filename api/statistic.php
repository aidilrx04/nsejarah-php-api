<?php

require_once 'conn.php';

// select all data from all tables

if (Input::is_method('get')) {
    $queries = [
        'murid' => 'SELECT COUNT(m_id) AS total FROM murid',
        'guru' => 'SELECT COUNT(g_id) AS total FROM guru',
        'tingkatan' => 'SELECT COUNT(kt_id) AS total FROM kelas_tingkatan',
        'kelas' => 'SELECT COUNT(k_id) AS total FROM kelas',
        'kuiz' => 'SELECT COUNT(kz_id) AS total FROM kuiz'
    ];

    $output = [];

    foreach ($queries as $type => $query) {
        if ($res = single_get_query($query)) {
            $output[$type] = $res['total'];
        }
    }

    $result = [
        'success' => true,
        'data' => $output,
        'message' => 'Data berjaya dicapai',
        'code' => 200
    ];
}

echo json_encode($result);
