<?php
/**
 * Created by PhpStorm.
 * User: torsten
 * Date: 28.09.2018
 * Time: 20:08
 */

namespace App\Utils\StreamPlatform;


use App\Entity\Streamer;
use Doctrine\Common\Persistence\ObjectManager;

interface StreamPlatformInterface
{

    /**
     * StreamInterface constructor.
     * @param ObjectManager|null $em
     * @param Streamer|null $streamer
     */
    public function __construct(ObjectManager $em = null, Streamer $streamer = null);

    /**
     * @param string $channel_id
     * @param bool $update
     * @return mixed
     */
    public function check_online(string $channel_id, bool $update = false);

    /**
     * @param string $channel | is either a string or a channel id, both should be possible
     * @param bool $validateGame | validate that streamer is playing League Of Legends
     * @return array | array with 'channel_id', 'display_name', 'channel_name'
     * @throws StreamPlatformException
     */
    public function info(string $channel, bool $validateGame = false);

    /**
     * @return int (HTTP_STATUS_CODE)
     */
    public function getStatus();

    /**
     * @return mixed
     */
    public function getStreamer();

    /**
     * @param $streamer
     * @return mixed
     */
    public function setStreamer(?Streamer $streamer);

}