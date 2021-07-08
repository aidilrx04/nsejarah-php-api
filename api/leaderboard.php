<?php

require_once './conn.php';

if (Input::is_method('get')) {
    expect($id_kuiz = Input::get_data('id_kuiz'), 'ID Kuiz diperlukan');
    expect($kuiz = get_kuiz($id_kuiz), 'Kuiz Tidak sah');
    $id_murid = Input::get_data('id_murid');

    if ($id_murid) {
        $data = get_jawapan_murid($id_kuiz, $id_murid);
    } else {
        $senarai_murid_jawab = get_list_jawapan_murid($id_kuiz, 100000);
        $id_murid_jawab = [];

        if( $senarai_murid_jawab )
        {
            $id_murid_jawab = array_map(function ($jawapan_murid) {
                return $jawapan_murid['jawapan_murid'][0]['jm_murid'];
            }, $senarai_murid_jawab['data']);
        }

        // $murid_tidak_jawab = array_filter( get_ting_murid($kuiz['kz_ting']), function ( $murid ) {
        //     global $id_murid_jawab;
        //     return !in_array( $murid['m_id'], $id_murid_jawab);
        // });

        $murid_tidak_jawab = filter_null(array_map(function ($murid) {
            global $id_murid_jawab;
            if (!in_array($murid['m_id'], $id_murid_jawab)) {
                return $murid;
            }
        }, get_ting_murid($kuiz['kz_ting'])));

        $data = [
            'murid_jawab' => $senarai_murid_jawab['data'] ?? [],
            'murid_tidak_jawab' => $murid_tidak_jawab
        ];
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
            'message' => 'Data tidak dijumpai',
            'code' => 404
        ];
    }
}

echo json_encode($result);


function get_jawapan_murid($id_kuiz, $id_murid)
{
    $query = "SELECT * FROM jawapan_murid WHERE jm_soalan IN (SELECT s_id FROM soalan WHERE s_kuiz = '$id_kuiz') AND jm_murid = '$id_murid'";
    $jawapan_murid = list_query($query, 100000, 1);

    if ($jawapan_murid && count($jawapan_murid['data']) > 0) {
        $jawapan_murid = $jawapan_murid['data'];

        $murid = get_murid($id_murid);
        $jumlah_soalan = single_get_query("SELECT COUNT(*) AS jumlah_soalan FROM soalan WHERE s_kuiz = ?", $id_kuiz)['jumlah_soalan'];
        $betul = 0;

        foreach ($jawapan_murid as $jawapan) {
            $id_jawapan = $jawapan['jm_jawapan'];
            $id_jawapan_betul = get_jawapan_betul($jawapan['jm_soalan'])['j_id'];

            if ($id_jawapan === $id_jawapan_betul) {
                $betul++;
            }
        }

        // $jawapan_murid['murid'] = $murid;
        // $jawapan_murid['jumlah'] = $jumlah_soalan;
        // $jawapan_murid['skor'] = round((float) ($betul / $jumlah_soalan) * 100, 2);

        return [
            'jawapan_murid' => $jawapan_murid,
            'murid' => $murid,
            'jumlah' => $jumlah_soalan,
            'jumlah_betul' => $betul,
            'skor' => round((float) ($betul / $jumlah_soalan) * 100, 2)
        ];
    }
    return false;
}

function get_list_jawapan_murid($id_kuiz, $limit = 25, $page = 1)
{
    $soalan_kuiz = get_soalan_kuiz($id_kuiz);
    // var_dump($soalan_kuiz);
    if (count($soalan_kuiz) > 0) {
        $id_soalan = get_soalan_kuiz($id_kuiz)[0]['s_id'];
        $list_jawapan = list_query("SELECT jm_murid FROM jawapan_murid WHERE jm_soalan = '$id_soalan'", $limit, $page);
        if ($list_jawapan) {
            $id_kuiz = $id_kuiz;
            $jawapan_murid = [];

            foreach ($list_jawapan['data'] as $jawapan) {
                array_push($jawapan_murid, get_jawapan_murid($id_kuiz, $jawapan['jm_murid']));
            }
            $list_jawapan['data'] = $jawapan_murid;

            return $list_jawapan;
        }
    }

    return false;
}


function check_kuiz_valid($id_kuiz)
{
    $kuiz = get_kuiz($id_kuiz);

    return $kuiz === false ? false : true;
}

function filter_null($arr)
{
    $no_null = [];
    foreach ($arr as $a) {
        if ($a !== NULL) array_push($no_null, $a);
    }

    return $no_null;
}
