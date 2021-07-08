<?php

require_once 'conn.php';

if (Input::is_method('post')) {
    Auth::use(['murid']);
    $data = Input::get_data('data');

    if (Auth::get_data('m_kelas') === $data['kz_ting']) {
        $senarai_soalan = $data['soalan'];
        $murid = get_murid(Auth::get_data('m_id'));

        foreach ($senarai_soalan as $soalan) {
            $jawapan_murid = $soalan['jawapan_murid'] ?? NULL;

            if (!register_jawapan_murid($murid['m_id'], $soalan['s_id'], $jawapan_murid)) {
                $result['message'] = "Operation failed";
            }
        }

        $result = [
            'success' => true,
            'message' => "Jawapan murid berjaya dimuat naik",
            'code' => 200
        ];
    }
    else
    {
        $result = [
            'success' => false,
            'message' => "Tiada Akses",
            'code' => 403
        ];
    }
}

echo json_encode($result);

function register_jawapan_murid($id_murid, $id_soalan, $id_jawapan)
{
    $status = $id_jawapan === NULL ? NULL : $id_jawapan === get_jawapan_betul($id_soalan)['j_id'];

    $result = insert_query(
        "INSERT INTO jawapan_murid(jm_murid, jm_soalan, jm_jawapan, jm_status) VALUE(?,?,?,?)",
        $id_murid,
        $id_soalan,
        $id_jawapan,
        $status
    );

    if ($result) {
        return true;
    }
    return false;
}