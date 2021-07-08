<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json, charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/JWK.php';
require_once '../jwt/JWT.php';
require_once '../jwt/SignatureInvalidException.php';

use Firebase\JWT\JWT;


$conn = mysqli_connect('localhost', 'root', '', 'nsejarah');

$result = [
    'success' => false,
    'message' => 'Invalid Request',
    'code' => 400
];

class Input
{
    static $data = [];
    static $auth = null;
    static $method = 'GET';

    static function get_data($key)
    {
        return static::$data[$key] ?? NULL;
    }

    static function get_auth()
    {
        return static::$auth ?? NULL;
    }

    static function set_auth($auth)
    {
        static::$auth = $auth;
    }

    static function add_data(array $data)
    {
        static::$data = array_merge(static::$data, $data);
    }

    static function is_method($method)
    {
        if (is_array($method)) {
            $method = array_map(function ($m) {
                return strtolower($m);
            }, $method);
            return in_array(static::$method, $method);
        } else {
            return strtolower(static::$method) === strtolower($method);
        }
    }

    static function set_method($method)
    {
        static::$method = $method;
    }
}

class Auth
{
    static $jwt = null;
    static $payload = [];
    static $data = [];
    static $valid = null;
    static $status = 200; // default no auth request
    /** @var string secret key used in encode/decode jwt  */
    private static $secret_key = 'SECRET_KEY_HERE';
    static $time_limit = '1 day';

    static function set_jwt($jwt)
    {
        static::$jwt = $jwt;
        static::decode();
    }

    static function decode()
    {
        try {
            $payload = JWT::decode(static::$jwt, static::$secret_key, ['HS256']);
            static::$payload = json_decode(json_encode($payload), 1);
            static::$data = static::$payload['data'] ?? [];
            static::$valid = true;
        } catch (Exception $e) {
            $message = $e->getMessage();
            if ($message === 'Expired token') {
                static::$status = 401;
            } else {
                static::$status = 404;
            }
            static::$valid = false;
        }
    }

    static function encode($data)
    {

        $issuedAt = new DateTimeImmutable();
        $expire = $issuedAt->modify(static::$time_limit)->getTimestamp();
        $serverName = $_SERVER['HTTP_HOST'];

        $payload = [
            'iat' => $issuedAt->getTimestamp(),
            'iss' => $serverName,
            'nbf' => $issuedAt->getTimestamp(),
            'exp' => $expire,
            'data' => $data
        ];

        $jwt =  JWT::encode($payload, static::$secret_key, 'HS256');
        static::$jwt = $jwt;

        return static::$jwt;
    }

    static function get_data($key)
    {
        return static::$data[$key] ?? false;
    }

    static function get_payload($key)
    {
        return static::$payload[$key] ?? false;
    }

    static function get_type()
    {
        $type = static::get_data('g_jenis')
            ? (static::get_data('g_jenis') === 'admin'
                ? 'admin'
                : 'guru')
            : 'murid';

        return $type;
    }

    static function use($allow_role = null, $force = true)
    {
        global $result;
        if (!static::$valid) {
            $result['success'] = false;
            $result['code'] = static::$status;
            $result['message'] = 'Tiada Akses';

            if ($force) die(json_encode($result));
            return $result;
        } else {
            if ($allow_role) {
                $role = static::get_type();
                if (!in_array($role, $allow_role)) {
                    $result['success'] = false;
                    $result['code'] = 403;
                    $result['message'] = 'Tiada Akses';

                    if ($force) die(json_encode($result));
                }
            }
        }

        return true;
    }
}




$inputs = json_decode(file_get_contents('php://input'), true);

Input::add_data($inputs ?? []);
Input::add_data($_GET ?? []);
Input::add_data($_POST ?? []);
Input::set_auth(strlen($_SERVER['HTTP_AUTHORIZATION']) > 0 ? $_SERVER['HTTP_AUTHORIZATION'] : null);
Input::set_method($_SERVER['REQUEST_METHOD']);


if (Input::get_auth()) {
    if (preg_match('/Bearer\s(\S+)/', Input::$auth, $matches)) {
        $jwt = $matches[1];
        Auth::set_jwt($jwt);
    }
}

//define all basics api here

function expect($cond, $message = '', $force = true)
{
    if (!$cond) {
        if ($force) {
            $result = [
                'success' => false,
                'message' => $message,
                'code' => 400
            ];

            die(json_encode($result));
        }
        return $message;
    }

    return true;
}

function single_get_query($query, ...$data)
{
    global $conn;
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(str_repeat('s', count($data)), ...$data);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }

    return false;
}

function list_query($query, $limit = 10, int $page = 1)
{
    global $conn;
    $offset = ($limit * $page) - $limit;

    $limit++; // increase 1 extra result for has next prop
    $query = $query . " limit {$limit} OFFSET {$offset}";

    if ($stmt = $conn->prepare($query)) {
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return false; // terminate process
        }

        $data = [];
        $paging = [
            'has_next' => false,
            'page' => (int)$page,
            'limit' => $limit - 1,
        ];

        if ($result->num_rows > 0) {
            while ($item = $result->fetch_assoc()) array_push($data, $item);

            if (count($data) > $limit - 1) {
                $paging['has_next'] = true;
                array_pop($data);
            }
        }

        $paging['count'] = $data ? count($data) : 0;

        return [
            'paging' => $paging,
            'data' => $data
        ];
    }

    return false;
}

function insert_query($query, ...$data)
{
    global $conn;
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param(str_repeat('s', count($data)), ...$data);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt && !$stmt->errno) {
            $id = $stmt->insert_id;
            return $id;
        }
    }

    return false;
}

// global functions
// is use in many different files

function register_soalan($id_kuiz, $data)
{
    $id_soalan = insert_query(
        "INSERT INTO soalan( s_kuiz, s_teks ) VALUE ( ?, ? )",
        $id_kuiz,
        $data['s_teks']
    );

    if ($id_soalan) {
        return $id_soalan;
    }

    return false;
}


function register_jawapan($id_soalan, $jawapan)
{
    $id_jawapan = insert_query(
        "INSERT INTO jawapan( j_soalan, j_teks ) VALUE (?, ?)",
        $id_soalan,
        $jawapan['j_teks']
    );

    if ($id_jawapan) {
        return $id_jawapan;
    }

    return false;
}


function register_jawapan_betul($id_soalan, $id_jawapan)
{

    $id_jawapan_betul = insert_query(
        "INSERT INTO soalan_jawapan(sj_soalan, sj_jawapan) VALUE (?, ?)",
        $id_soalan,
        $id_jawapan
    );

    if ($id_jawapan_betul) {
        return $id_jawapan_betul;
    }

    return false;
}

function get_ting($id_ting)
{
    $ting = single_get_query("SELECT * FROM kelas_tingkatan WHERE kt_id = ?", $id_ting);
    if ($ting) {
        $kelas = get_kelas($ting['kt_kelas']);
        $guru = get_guru($ting['kt_guru']);

        $ting['kelas'] = $kelas;
        $ting['guru'] = $guru;
        return $ting;
    }
    return false;
}

function get_kelas($id_kelas)
{
    $kelas = single_get_query("SELECT * FROM kelas WHERE k_id = ?", $id_kelas);

    if ($kelas) {
        return $kelas;
    }

    return false;
}

function get_guru($id_guru)
{
    $guru = single_get_query("SELECT * FROM guru WHERE g_id = ?", $id_guru);
    if ($guru) {
        return $guru;
    }

    return false;
}

function get_murid($id_murid)
{
    $murid = single_get_query("SELECT * FROM murid WHERE m_id = ?", $id_murid);

    if ($murid) {
        $kelas = get_ting($murid['m_kelas']);

        $murid['kelas'] = $kelas;

        return $murid;
    }

    return false;
}


function get_ting_murid($id_ting)
{
    $list_murid = list_query("SELECT m_id FROM murid WHERE m_kelas = '{$id_ting}' ORDER BY m_id", 100000, 1);

    if ($list_murid) {
        $data = array_map(function ($murid) {
            return get_murid($murid['m_id']);
        }, $list_murid['data']);

        return $data;
    }

    return false;
}


function get_kuiz($id_kuiz)
{
    $kuiz = single_get_query("SELECT * FROM kuiz WHERE kz_id = ?", $id_kuiz);

    if ($kuiz) {
        $guru = get_guru($kuiz['kz_guru']);
        $ting = get_ting($kuiz['kz_ting']);
        $soalan = get_soalan_kuiz($kuiz['kz_id']);

        $kuiz['guru'] = $guru;
        $kuiz['ting'] = $ting;
        $kuiz['soalan'] = $soalan;

        return $kuiz;
    }

    return false;
}

function get_soalan($id_soalan, $remove_jawapan_betul = false)
{
    $soalan = single_get_query("SELECT * FROM soalan WHERE s_id = ?", $id_soalan);
    if ($soalan) {
        $jawapan = get_jawapan_soalan($soalan['s_id']);
        $jawapan_betul = get_jawapan_betul($soalan['s_id']);

        $soalan['jawapan'] = $jawapan ? $jawapan : [];
        $soalan['jawapan_betul'] = $jawapan_betul ? $jawapan_betul : NULL;

        return $soalan;
    }
    return false;
}

function get_soalan_kuiz($id_kuiz)
{
    $soalan_kuiz = list_query("SELECT s_id FROM soalan WHERE s_kuiz = '$id_kuiz'", 1000000, 1);

    if ($soalan_kuiz && count($soalan_kuiz['data']) > 0) {
        $data = array_map(function ($soalan) {
            return get_soalan($soalan['s_id']);
        }, $soalan_kuiz['data']);

        return $data;
    }

    return [];
}

function get_jawapan($id_jawapan)
{
    $jawapan = single_get_query("SELECT * FROM jawapan WHERE j_id = ? ", $id_jawapan);

    if ($jawapan) {
        return $jawapan;
    }

    return false;
}

function get_jawapan_soalan($id_soalan)
{
    $list_jawapan = list_query("SELECT j_id FROM jawapan WHERE j_soalan = '$id_soalan' ORDER BY j_id ASC", 1000000);

    if ($list_jawapan) {
        $senarai_jawapan = array_map(function ($jawapan) {
            return get_jawapan($jawapan['j_id']);
        }, $list_jawapan['data']);


        return $senarai_jawapan;
    }


    return false;
}


function get_jawapan_betul($id_soalan)
{
    $jawapan_betul = single_get_query("SELECT * FROM soalan_jawapan WHERE sj_soalan = ?", $id_soalan);

    if ($jawapan_betul) {
        return get_jawapan($jawapan_betul['sj_jawapan']);
    }

    return false;
}
