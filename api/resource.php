<?php

require_once '../jwt/BeforeValidException.php';
require_once '../jwt/ExpiredException.php';
require_once '../jwt/JWK.php';
require_once '../jwt/JWT.php';
require_once '../jwt/SignatureInvalidException.php';

use Firebase\JWT\JWT;

if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
    header('HTTP/1.0 400 Bad Request');
    echo 'Token not found in request';
    exit;
}

$jwt = $matches[1];
if( !$jwt )
{
    header( 'HTTP/1.00 400 Bad Request' );
    exit;
}

$key = 'token_key';
$token = JWT::decode( $jwt, $key, ['HS512'] );
$now = new DateTimeImmutable();
$serverName = 'localhost';

if ($token->iss !== $serverName ||
    $token->nbf > $now->getTimestamp() ||
    $token->exp > $now->getTimestamp())
{
    header('HTTP/1.1 401 Unauthorized');
    echo 'wrong jwt';
    exit;
}
else 
{
    echo $now->getTimestamp();
}

