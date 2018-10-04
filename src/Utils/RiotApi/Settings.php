<?php

namespace App\Utils\RiotApi;


class Settings
{

    private $settings;
    private $key;

    /**
     * Settings constructor.
     * @param array|null $settings
     * @param bool $isDev
     */
    public function __construct(array $settings = null, bool $isDev = true)
    {

        /* Default Settings */
        if($settings === null){
            $this->settings = array(
                'max_requests_short' => 3000, /* Max requests in short interval */
                'max_requests_long' => 180000, /* Max requests in long interval */
                'interval_short' => 10, /* Short interval in seconds */
                'interval_long' => 600, /* Long interval in seconds */
            );
        }

        /* Developer Settings */
        if($isDev){
            $this->settings = array(
                'max_requests_short' => 20, /* Max requests in short interval */
                'max_requests_long' => 100, /* Max requests in long interval */
                'interval_short' => 1, /* Short interval in seconds */
                'interval_long' => 120, /* Long interval in seconds */
            );
        }

        /* Apply Settings */
        if($settings !== null && !$isDev){
            $this->settings = $settings;
        }

        /* Riot Api Key */
        $this->key = getenv('RIOT_API_KEY');
    }

    /**
     * @return array|null
     */
    public function getSettings(): ?array
    {
        return $this->settings;
    }

    /**
     * @return array|false|string
     */
    public function getKey(): string
    {
        return $this->key;
    }


}