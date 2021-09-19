<?php

require_once 'conn.php';

if (Input::is_method('post')) {
    expect($jenis = Input::get_data('jenis'), 'Invalid Request');
    expect($data = Input::get_data('data'), 'No data');
    $fail_message = null;
    $output = null;

    switch ($jenis) {
        case 'guru':
            Auth::use(['admin']);
            $guru = single_get_query("SELECT g_id FROM guru WHERE g_nokp =? ", $data['g_nokp']);

            if (!$guru) {
                $output = register_guru($data);
            } else {
                $fail_message = "Guru dengan NOKP: {$data['g_nokp']} sudah wujud";
            }
            break;
        case 'murid':
            Auth::use(['admin']);
            $murid = single_get_query("SELECT m_id FROM murid WHERE m_nokp = ?", $data['m_nokp']);

            if (!$murid) {
                $output = register_murid($data);
            } else {
                $fail_message = "Murid dengan NOKP: {$data['m_nokp']} sudah wujud";
            }
            break;
        case 'kelas':
            Auth::use(['admin']);
            $kelas = single_get_query("SELECT * FROM kelas WHERE k_nama = ?", $data['k_nama']);
            if (!$kelas) {
                $output = register_kelas($data);
            } else {
                $fail_message = 'Kelas sudah wujud';
            }
            break;
        case 'tingkatan':
            Auth::use(['admin']);

            // check if any similar tingkatan existed
            $ting = single_get_query(
                "SELECT * FROM kelas_tingkatan WHERE kt_ting = ? AND kt_kelas = ?",
                $data['kt_ting'],
                $data['kt_kelas']
            );

            // var_dump( $ting );

            if (is_bool($ting)) {
                $output = register_tingkatan($data);
            } else {
                $fail_message = 'Tingkatan sudah wujud';
            }
            break;
        case 'kuiz': {
                Auth::use(['guru', 'admin']);

                $id_kuiz = register_kuiz($data);

                //register soalan if exist
                $senarai_soalan = $data['soalan'] ?? [];
                $constructed_id = [];
                foreach ($senarai_soalan as $soalan) {
                    if ($id_soalan = register_soalan($id_kuiz, $soalan)) {
                        $constructed_id[$id_soalan] =  $soalan['s_id'];
                        $senarai_jawapan = $soalan['jawapan'] ?? [];
                        $jawapan_betul = $soalan['jawapan_betul'] ?? [];

                        if (!empty($jawapan_betul)) {
                            foreach ($senarai_jawapan as $jawapan) {
                                if ($id_jawapan = register_jawapan($id_soalan, $jawapan)) {
                                    if ($jawapan['j_id'] === $jawapan_betul['j_id']) {
                                        register_jawapan_betul($id_soalan, $id_jawapan);
                                    }
                                }
                            }
                        }
                    }
                }

                $output = get_kuiz($id_kuiz);
                // return old id with new one
                $keys = array_keys($constructed_id);
                foreach ($output['soalan'] as $i => $soalan) {

                    if (in_array($soalan['s_id'], $keys)) {

                        $output['soalan'][$i]['old_id'] = $constructed_id[$soalan['s_id']];
                    }
                }
            }
            break;

        default:
            $result['message'] = 'Invalid Request';
    }

    if ($output) {
        $result = [
            'success' => true,
            'message' => $jenis . ' berjaya ditambah',
            'data' => $output,
            'code' => 200
        ];
    } else {
        $result = [
            'success' => false,
            'message' => $fail_message ?? $jenis . ' gagal ditambah',
            'code' => 400
        ];
    }
}


echo json_encode($result);

function register_guru($data)
{
    $id_guru = insert_query(
        "INSERT INTO guru( g_nokp, g_nama, g_katalaluan, g_jenis ) VALUE (?,?,?,?)",
        $data['g_nokp'],
        $data['g_nama'],
        $data['g_katalaluan'],
        $data['g_jenis']
    );

    if ($id_guru) {
        return get_guru($id_guru);
    }

    return false;
}

function register_murid($data)
{
    $id_murid = insert_query(
        "INSERT INTO murid( m_nokp, m_nama, m_katalaluan, m_kelas ) VALUE (?,?,?,?)",
        $data['m_nokp'],
        $data['m_nama'],
        $data['m_katalaluan'],
        $data['m_kelas']
    );

    if ($id_murid) {
        return get_murid($id_murid);
    }

    return false;
}

function register_kelas($data)
{
    $id_kelas = insert_query("INSERT INTO kelas(k_nama) VALUE (?)", $data['k_nama']);

    if ($id_kelas) {
        return get_kelas($id_kelas);
    }

    return false;
}

function register_tingkatan($data)
{
    // allow tingkatan registration without a teacher?
    $data['kt_guru'] = $data['kt_guru'] === '' ? null : $data['kt_guru'];

    $id_ting = insert_query(
        "INSERT INTO kelas_tingkatan(kt_ting, kt_kelas, kt_guru) VALUE (?,?,?)",
        $data['kt_ting'],
        $data['kt_kelas'],
        $data['kt_guru']
    );

    if ($id_ting) {
        return get_ting($id_ting);
    }

    return false;
}

function register_kuiz($data)
{

    $id_kuiz = insert_query(
        "INSERT INTO kuiz(kz_nama, kz_guru, kz_ting, kz_tarikh, kz_jenis, kz_masa) VALUE (?,?,?,?,?,?)",
        $data['kz_nama'],
        $data['kz_guru'],
        $data['kz_ting'],
        $data['kz_tarikh'],
        $data['kz_jenis'],
        $data['kz_masa']
    );

    if ($id_kuiz) {
        return $id_kuiz;
    }

    return false;
}
