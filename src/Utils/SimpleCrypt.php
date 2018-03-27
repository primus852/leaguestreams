<?php

namespace App\Utils;

class SimpleCrypt
{

    private $key, $iv, $encrypt_method;

    public function __construct($key = Constants::SC_KEY, $iv = Constants::SC_IV, $method = Constants::SC_METHOD)
    {
        $this->encrypt_method = $method;
        $this->key = hash('sha256', $key);
        $this->iv = substr(hash('sha256', $iv), 0, 16);

    }

    public function encode($string)
    {
        return base64_encode(openssl_encrypt($string, $this->encrypt_method, $this->key, 0, $this->iv));
    }

    public function decode($string)
    {
        return openssl_decrypt(base64_decode($string), $this->encrypt_method, $this->key, 0, $this->iv);
    }

    /**
     * @param string $key
     */
    public function setKey(string $key): void
    {
        $this->key = hash('sha256', $key);
    }

    /**
     * @param bool|string $iv
     */
    public function setIv($iv): void
    {
        $this->iv = substr(hash('sha256', $iv), 0, 16);
    }

    /**
     * @param string $encrypt_method
     */
    public function setEncryptMethod(string $encrypt_method): void
    {
        $this->encrypt_method = $encrypt_method;
    }



}