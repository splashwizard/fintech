<?php

namespace App\Utils\GameUtils;

class TripleDES
{
    private static function pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    //php7 or above need to use this method to encrypt
    public static function encryptText($string, $key)
    {
        $key = base64_decode($key);
        $string = TripleDES::pkcs5Pad($string, 8);
        $data = openssl_encrypt($string, 'DES-EDE3-CBC', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, base64_decode("AAAAAAAAAAA="));
        $data = base64_encode($data);
        return $data;
    }
}