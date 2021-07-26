<?php

require_once 'conn.php';

if (Input::is_method('post')) {
    expect($jenis = Input::get_data('jenis'), 'Invalid request');

    expect($data_update = Input::get_data('data'), 'Data diperlukan');
    $data = null;

    if ($jenis === 'kuiz') {
        Auth::use(['admin', 'guru']);

        //check if kuiz exist
        expect($kuiz = get_kuiz($data_update['kz_id']), 'Data Tidak Dijumpai');
        //check if the author is or hv admin role
        expect($kuiz['kz_guru'] === Auth::get_data('g_id') || Auth::get_type() === 'admin', 'Akses Tanpa Kebenaran.');

        $update_kuiz = update_kuiz($data_update);

        if ($update_kuiz) {

            $senarai_soalan = $data_update['soalan'] ?? [];
            $constructed_id = [];
            $delete = $data_update['padam'] ?? [];
            $gambar_padam = $data_update['gambar_padam'] ?? [];
            
            // add delete soalan gambar to gambar padam
            foreach( $delete as $s)
            {
                array_push( $gambar_name, $s['s_id']);
            }

            foreach( $gambar_padam as $id_soalan )
            {
                $gambar_name = single_get_query("SELECT s_gambar FROM soalan WHERE s_id = ?", $id_soalan)['s_gambar'];
                $target_dir = '../image/';

                // workaround if gambar is url
                if( substr($gambar_name, 0, 4) === 'http')
                {
                    continue;
                }

                // delete file
                if( unlink( $target_dir . $gambar_name) )
                {
                    // update database
                    bool_query("UPDATE soalan SET s_gambar = NULL WHERE s_id = ?", $id_soalan);
                }
            }

            // filter out all padam soalan
            $senarai_soalan = array_filter($senarai_soalan, function ($soalan) {
                global $delete;
                return !in_array($soalan['s_id'], $delete);
            });

            // get updatable soalan
            $update = array_filter($senarai_soalan, function ($soalan) {
                return is_numeric($soalan['s_id']); //note:  important things to check here. new soalan must have id that is not numerical or all these will broken
            });

            // get new soalan
            $new = array_filter($senarai_soalan, function ($soalan) {
                return !is_numeric($soalan['s_id']); // note: look note above;
            });

            // note: all soalan and jawapan will ignore failure and continue operation
            foreach ($update as $soalan_update) {
                $updated_soalan = update_soalan($soalan_update); // update soalan
                $constructed_id[$updated_soalan['s_id']] = $soalan_update['s_id'];
                $senarai_jawapan = $soalan_update['jawapan'] ?? [];
                $jawapan_betul = $soalan_update['jawapan_betul'] ?? NULL;

                foreach ($senarai_jawapan as $jawapan_update) {
                    $updated_jawapan = update_jawapan($jawapan_update);
                }

                if ($jawapan_betul) {
                    $updated_jawapan_betul = update_jawapan_betul($jawapan_betul);
                }
            }

            foreach ($new as $soalan_new) {
                $jawapan_betul = $soalan_new['jawapan_betul'] ?? NULL;

                //  ? note: this to prevent from soalan having correct answer and breaking the app
                if ($jawapan_betul) {
                    $registered_soalan = register_soalan($update_kuiz['kz_id'], $soalan_new);
                    $constructed_id[$registered_soalan] = $soalan_new['s_id'];
                    // ? note: only register jawapan when soalan is registered
                    if ($registered_soalan) {
                        $senarai_jawapan = $soalan_new['jawapan'] ?? [];

                        foreach ($senarai_jawapan as $jawapan_new) {
                            $registered_jawapan = register_jawapan($registered_soalan, $jawapan_new);

                            if ($registered_jawapan && $jawapan_new['j_id'] === $jawapan_betul['j_id']) {
                                register_jawapan_betul($registered_soalan, $registered_jawapan);
                            }
                        }
                    }
                }
            }

            foreach ($delete as $soalan_padam) {
                // ? note: sometimes? request may pass soalan object instead id
                $id_soalan = is_array($soalan_padam) ? $soalan_padam['s_id'] : $soalan_padam;

                $deleted_soalan = delete_soalan($id_soalan);
            }

            $data = get_kuiz($data_update['kz_id']);
            $keys = array_keys($constructed_id);
            foreach( $data['soalan'] as $i=>$soalan )
            {
                if( in_array($soalan['s_id'], $keys))
                {
                    $data['soalan'][$i]['old_id'] = $constructed_id[$soalan['s_id']];
                }
            }
        }
    } else {
        Auth::use(['admin']);

        switch ($jenis) {
            case 'guru':
                $data = update_guru($data_update);
                break;
            case 'murid':
                $data = update_murid($data_update);
                break;
            case 'tingkatan':
                $exist_ting = single_get_query(
                    "SELECT kt_ting FROM kelas_tingkatan WHERE kt_ting = ? AND kt_kelas = ? ",
                    $data_update['kt_ting'],
                    $data_update['kt_kelas']
                );

                if (!$exist_ting) {
                    $data = update_tingkatan($data_update);
                }
                break;
            case 'kelas':
                $exist_kelas = single_get_query("SELECT k_id FROM kelas WHERE k_nama =? ", $data_update['k_nama']);
                if (!$exist_kelas) {
                    $data = update_kelas($data_update);
                }
                break;
        }
    }

    if ($data) {
        $result = [
            'success' => true,
            'message' => $jenis . ' berjaya dikemaskini',
            'data' => $data,
            'code' => 200
        ];
    } else {
        $result = [
            'success' => false,
            'message' => $jenis . ' gagal dikemaskini',
            'code' => 400
        ];
    }
}

echo json_encode($result);

function bool_query($query, ...$data)
{
    global $conn;
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(str_repeat('s', count($data)), ...$data);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt && !$stmt->errno) {
            return true;
        }
    }

    return false;
}

function update_guru($data)
{
    $update = bool_query(
        "UPDATE guru SET g_nokp = ?, g_nama = ?, g_katalaluan = ?, g_jenis = ? WHERE g_id = ?",
        $data['g_nokp'],
        $data['g_nama'],
        $data['g_katalaluan'],
        $data['g_jenis'],
        $data['g_id']
    );

    if ($update) {
        return get_guru($data['g_id']);
    }

    return false;
}

function update_murid($data)
{
    $update = bool_query(
        "UPDATE murid SET m_nokp = ?, m_nama = ?, m_katalaluan = ?, m_kelas = ? WHERE m_id = ?",
        $data['m_nokp'],
        $data['m_nama'],
        $data['m_katalaluan'],
        $data['m_kelas'],
        $data['m_id']
    );

    if ($update) {
        return get_murid($data['m_id']);
    }

    return false;
}

function update_tingkatan($data)
{
    $update = bool_query(
        "UPDATE kelas_tingkatan SET kt_ting = ?, kt_kelas = ?, kt_guru = ? WHERE kt_id = ?",
        $data['kt_ting'],
        $data['kt_kelas'],
        $data['kt_guru'],
        $data['kt_id']
    );

    if ($update) {
        return get_ting($data['kt_id']);
    }

    return false;
}

function update_kelas($data)
{
    $update = bool_query(
        "UPDATE kelas SET k_nama = ? WHERE k_id = ?",
        $data['k_nama'],
        $data['k_id']
    );

    if ($update) {
        return get_kelas($data['k_id']);
    }

    return false;
}

function update_kuiz($data)
{
    $update = bool_query(
        "UPDATE kuiz SET kz_nama = ?, kz_tarikh = ?, kz_jenis = ?, kz_masa = ? WHERE kz_id = ?",
        $data['kz_nama'],
        $data['kz_tarikh'],
        $data['kz_jenis'],
        $data['kz_masa'],
        $data['kz_id']
    );

    if ($update) {
        return get_kuiz($data['kz_id']);
    }

    return false;
}


function update_soalan($data)
{
    $update = bool_query(
        "UPDATE soalan SET s_teks = ? WHERE s_id = ?",
        $data['s_teks'],
        $data['s_id']
    );

    if ($update) {
        return get_soalan($data['s_id']);
    }
    return false;
}

function update_jawapan($data)
{
    $update = bool_query(
        "UPDATE jawapan SET j_teks = ? WHERE j_id = ?",
        $data['j_teks'],
        $data['j_id']
    );

    if ($update) {
        return get_jawapan($data['j_id']);
    }

    return false;
}

function update_jawapan_betul($data)
{
    $update = bool_query(
        "UPDATE soalan_jawapan SET sj_jawapan = ? WHERE sj_soalan = ?",
        $data['j_id'],
        $data['j_soalan']
    );

    if ($update) {
        return get_jawapan_betul($data['j_soalan']);
    }

    return false;
}


function delete_soalan($id_soalan)
{
    $delete = bool_query("DELETE FROM soalan WHERE s_id = ?", $id_soalan);

    if ($delete) {
        return true;
    }

    return false;
}
