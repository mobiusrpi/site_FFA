<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    public function generate(
        array $header, 
        array $payload, 
        string $secret, 
        int $validity =3600):string
    {
        if ($validity > 0){
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload["iat"] = $now->getTimestamp();
            $payload["exp"] = $exp;
        }

        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        $base64Header = str_replace(['+','/','='],['-','_',''], $base64Header);
        $base64Payload = str_replace(['+','/','='],['-','_',''], $base64Payload);

        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);
        $base64Signature = base64_encode($signature);
        $base64Signature = str_replace(['+','/','='],['-','_',''], $base64Signature);
 
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }

    public function isValid(string $token):bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    public function getPayload(string $token): array
    {
        $array = explode('.', $token);
        $payload = json_decode(base64_decode($array[1]), true);
        return $payload;
    }

    public function isExpired(string $token):bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();
        return $payload['exp'] < $now->getTimestamp(); 
    }

    public function getHeader(string $token):array
    {
        $array = explode('.', $token);
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }

    public function check(string $token, string $secret):bool
    {
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        $verifToken = $this->generate($header,$payload,$secret,0);

        return $token === $verifToken;
    }
}