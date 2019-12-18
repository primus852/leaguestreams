<?php

namespace App\Utils;

use App\Entity\Streamer;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;

class TwitchApi implements StreamInterface
{

    private $streamer;
    private $responseCode;
    private $em;


    /**
     * TwitchApi constructor.
     * @param ObjectManager $em
     * @param Streamer|null $streamer
     */
    public function __construct(ObjectManager $em = null, Streamer $streamer = null)
    {
        $this->streamer = $streamer;
        $this->em = $em;
    }


    public function checkStreamOnline($update = false, $channel_id = false)
    {

        if ($this->em === null) {
            throw new Exception('EntityManager cannot be null');
        }

        $mod = 'streams/';
        if ($channel_id !== false) {
            $url = Constants::TWITCH_V5 . $mod . $channel_id;
        } else {
            $url = Constants::TWITCH_V5 . $mod . $this->streamer->getChannelId();
        }

        try {
            $data = $this->getData($url);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $result = false;
        if (array_key_exists('stream', $data)) {
            if ($data['stream'] !== null) {
                if ($data['stream']['game'] === 'League of Legends') {
                    $result = true;
                }
            }
        }

        if ($update === true) {

            if($this->streamer === null){

                $platform = $this->em->getRepository('App:Platform')->find(1);

                $this->streamer = new Streamer();
                $this->streamer->setChannelName($data['stream']['channel']['display_name']);
                $this->streamer->setChannelUser($data['stream']['channel']['name']);
                $this->streamer->setChannelId($data['stream']['channel']['_id']);
                $this->streamer->setPlatform($platform);
                $this->streamer->setTotalOnline(0);
                $this->streamer->setCreated();
                $this->streamer->setIsFeatured(false);
            }

            $partner = $data['stream']['channel']['partner'] ? $data['stream']['channel']['partner'] : false;

            $this->streamer->setIsOnline($result);
            $this->streamer->setDescription($data['stream']['channel']['status']);
            $this->streamer->setIsPartner($partner);
            $this->streamer->setViewers($data['stream']['viewers']);
            $this->streamer->setResolution($data['stream']['video_height']);
            $this->streamer->setFps($data['stream']['average_fps']);
            $this->streamer->setDelay($data['stream']['delay']);
            $this->streamer->setLanguage($data['stream']['channel']['language']);
            $this->streamer->setThumbnail($data['stream']['preview']['medium']);
            $this->streamer->setLogo($data['stream']['channel']['logo']);
            $this->streamer->setBanner($data['stream']['channel']['profile_banner']);
            $this->streamer->setStarted(new \DateTime($data['stream']['created_at']));
            $this->streamer->setModified();

            $this->em->persist($this->streamer);
            try {
                $this->em->flush();
            } catch (Exception $e) {
                throw new Exception('Database Error');
            }

        }

        return $result;

    }

    public function getStreamerInfo($channel, $validateGame = false)
    {

        $mod = 'users?login=';
        $url = Constants::TWITCH_V5 . $mod . strtolower(str_replace(" ", "", $channel));
        $result = null;

        try {
            $data = $this->getData($url);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (array_key_exists('error', $data)) {
            throw new Exception('Twitch API Error: ' . $data['error']);
        }

        if (count($data['users']) === 0) {
            throw new Exception('Channel ' . $channel . ' not found');
        }

        if (count($data['users']) > 1) {
            throw new Exception('More than one channel \'' . $channel . '\' found, please specify your search');
        }

        $isOnline = false;
        if ($validateGame === true) {
            if ($this->streamer === null) {
                $isOnline = $this->checkStreamOnline(true, $data['users'][0]['_id']);
            } else {
                $isOnline = $this->checkStreamOnline(true);
            }

        }

        if ($isOnline || $validateGame === false) {
            $result['channel_id'] = $data['users'][0]['_id'];
            $result['display_name'] = $data['users'][0]['display_name'];
            $result['channel_name'] = strtolower(str_replace(" ", "", $channel));
        } else {
            throw new Exception('Channel is currently not streaming League of Legends');
        }


        if ($result === null) {
            throw new Exception('Unexpected $result');
        }

        return $result;

    }

    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function getData($url)
    {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/vnd.twitchtv.v5+json',
            'Client-ID: ' . getenv('TWITCH_CLIENT_ID')
        ));

        $result = curl_exec($ch);
        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$this->responseCode == 200) {
            throw new Exception('Error with the Twitch API, please try again in a few moments');
        }

        return json_decode($result, true);
    }

    /**
     * @return Streamer
     */
    public function getStreamer(): Streamer
    {
        return $this->streamer;
    }

    /**
     * @param Streamer|null $streamer
     * @return TwitchApi
     */
    public function setStreamer($streamer)
    {
        $this->streamer = $streamer;
        return $this;
    }


}