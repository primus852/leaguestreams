<?php

namespace App\Utils;


use App\Entity\Streamer;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Config\Definition\Exception\Exception;

interface StreamInterface
{

    /**
     * StreamInterface constructor.
     * @param ObjectManager|null $em
     * @param Streamer|null $streamer
     */
    public function __construct(ObjectManager $em = null, Streamer $streamer = null);


    /**
     * @param bool $update
     * @param bool $channel_id
     * @return mixed
     * @throws Exception
     */
    public function checkStreamOnline($update = false, $channel_id = false);

    /**
     * @param $channel | is either a string or a channel id, both should be possible
     * @param bool $validateGame | validate that streamer is playing League Of Legends
     * @return array | array with 'channel_id', 'display_name', 'channel_name'
     * @throws Exception
     */
    public function getStreamerInfo($channel, $validateGame = false);

    /**
     * @return int (HTTP_STATUS_CODE)
     */
    public function getResponseCode();

    /**
     * @param $url
     * @return mixed [json_decode($data,true)]
     * @throws Exception
     */
    public function getData($url);

    /**
     * @return mixed
     */
    public function getStreamer();

    /**
     * @param $streamer
     * @return mixed
     */
    public function setStreamer($streamer);

}