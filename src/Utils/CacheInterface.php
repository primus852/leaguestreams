<?php

namespace App\Utils;


interface CacheInterface
{

    /**
     * @param $key
     * @return mixed
     */
    public function has($key);

    /**
     * @param $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param $key
     * @param $data
     * @param int $ttl
     * @return mixed
     */
    public function put($key, $data, $ttl = 0);

}