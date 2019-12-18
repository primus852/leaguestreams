<?php

namespace App\Utils\StreamPlatform;


use App\Entity\Platform;
use App\Entity\Streamer;
use App\Entity\Vod;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;

class TwitchApi implements StreamPlatformInterface
{

    private $em;
    private $streamer;
    private $status;
    private $url = 'https://api.twitch.tv';
    private $gameId = '21779';

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

    /**
     * @param string $channel_id
     * @return mixed
     * @throws StreamPlatformException
     */
    public function channel_info(string $channel_id)
    {

        /**
         * API: v5
         */
        $url = '/kraken/streams/' . $channel_id;

        try {
            $data = $this->data($url, true);
        } catch (StreamPlatformException $e) {
            throw new StreamPlatformException($e->getMessage());
        }

        return $data;

    }

    /**
     * @param string $channel_id
     * @param bool $update
     * @return bool|mixed
     * @throws StreamPlatformException
     */
    public function check_online(string $channel_id, bool $update = false)
    {

        /**
         * API: v5
         */
        $url = '/kraken/streams/' . $channel_id;

        try {
            $data = $this->data($url, true);
        } catch (StreamPlatformException $e) {
            throw new StreamPlatformException($e->getMessage());
        }

        /**
         * Check if the stream key s not null ( = is online)
         */
        $result = false;
        $stream = null;
        if ($data['stream'] !== null) {

            /**
             * Check if it is the correct game name
             */
            if (strtolower($data['stream']['game']) === strtolower('League of Legends')) {
                $stream = $data['stream'];
                $result = true;
            }
        }

        /**
         * Check if we need to update the DB Data
         */
        if ($update) {

            /**
             * Check if we found the Streamer streaming LoL
             */
            $channelData = $stream['channel'];

            /**
             * Get Info for Streamer User
             */
            $isPartner = $channelData['partner'] === true ? true : false;

            /**
             * Check if the Streamer exists in DB
             */
            $streamer = $this->em->getRepository(Streamer::class)->findOneBy(array(
                'channelId' => $channel_id
            ));

            $was = false;
            if ($streamer === null) {

                /**
                 * Twitch Platform Entity
                 */
                $platform = $this->em->getRepository(Platform::class)->find(1);

                $streamer = new Streamer();
                $streamer->setChannelId($channel_id);
                $streamer->setPlatform($platform);
                $streamer->setTotalOnline(0);
                $streamer->setCreated();
                $streamer->setIsFeatured(false);
            } else {
                $was = $streamer->getIsOnline();
            }

            if ($channelData != null) {
                $streamer->setChannelName($channelData['display_name']);
                $streamer->setChannelUser($channelData['name']);
            }
            $streamer->setIsPartner($isPartner);
            $streamer->setIsOnline($result);
            $streamer->setDescription($channelData['status']);
            $streamer->setViewers($stream['viewers']);
            $streamer->setResolution($stream['video_height']);
            $streamer->setFps($stream['average_fps']);
            $streamer->setDelay($stream['delay']);
            $streamer->setLanguage($channelData['language']);
            $streamer->setThumbnail($stream['preview']['medium']);
            $streamer->setLogo($channelData['logo']);
            $streamer->setBanner($channelData['profile_banner']);
            $streamer->setStarted(new \DateTime($stream['created_at']));

            /**
             * If the Streamer is online, update the total time Online (now - last modified)
             */
            try {
                $minutes = Stopwatch::stop($streamer->getModified(), true, 'm');
            } catch (StopwatchException $e) {
                throw new StreamPlatformException('Error parsing Timer: ' . $e->getMessage());
            }
            $streamer->setTotalOnline($streamer->getTotalOnline() + $minutes);

            /**
             * Now we update the Modified Col
             */
            $streamer->setModified();

            $this->em->persist($streamer);

            try {
                $this->em->flush();
            } catch (\Exception $e) {
                throw new StreamPlatformException('MySQL Error: ' . $e->getMessage());
            }

            /**
             * if we set the streamer to Offline update the VODs
             */
            if ($was && !$streamer->getisOnline()) {
                try {
                    $this->vods($streamer);
                } catch (StreamPlatformException $e) {
                    throw new StreamPlatformException('Could not get VODs: ' . $e->getMessage());
                }
            }
        }

        return $result;
    }

    /**
     * @param string $channel
     * @param bool $validateGame
     * @return array|mixed
     * @throws StreamPlatformException
     */
    public function info(string $channel, bool $validateGame = false)
    {

        /**
         * API: new Twitch
         */
        $url = '/helix/users?login=' . $channel;

        try {
            $data = $this->data($url);
        } catch (StreamPlatformException $e) {
            throw new StreamPlatformException($e->getMessage());
        }

        /**
         * Error Handling
         * @todo improve...
         */
        if (empty($data)) {
            throw new StreamPlatformException('Channel ' . $channel . ' not found');
        }

        if (empty($data['data'])) {
            throw new StreamPlatformException('Channel ' . $channel . ' not found');
        }

        if (count($data) > 1) {
            throw new StreamPlatformException('More than one channel \'' . $channel . '\' found, please specify your search');
        }

        $data = $data['data'][0];
        $isOnline = $validateGame ? $this->check_online($data['id'], true) : false;

        if ($isOnline || !$validateGame) {
            $result['channel_id'] = $data['id'];
            $result['display_name'] = $data['display_name'];
            $result['channel_name'] = $data['login'];
        } else {
            throw new StreamPlatformException('Channel is currently not streaming League of Legends');
        }

        return $result;

    }

    /**
     * @param Streamer $streamer
     * @throws StreamPlatformException
     */
    public function vods(Streamer $streamer)
    {

        /**
         * API: v5
         * @todo: Replace when possible
         */
        $url = '/kraken/channels/' . $streamer->getChannelId() . '/videos?broadcast_type=archive&limit=100';

        /**
         * Get the VOD List
         */
        try {
            $data = $this->data($url, true);
        } catch (StreamPlatformException $e) {
            throw new StreamPlatformException($e->getMessage());
        }

        if ($data !== null) {
            if (array_key_exists('videos', $data)) {

                foreach ($data['videos'] as $vod) {

                    $thumb = $vod['preview']['medium'];
                    $videoId = $vod['_id'];
                    $length = $vod['length'];
                    $created = $vod['created_at'];

                    /**
                     * Check if it is the right game and publicly viewable
                     */
                    if ($vod['game'] === 'League of Legends' && $vod['viewable'] === 'public') {

                        $v = $this->em->getRepository(Vod::class)->find($videoId);

                        if ($v === null) {
                            $v = new Vod();
                            $v->setVideoId($videoId);
                        }

                        $v->setThumbnail($thumb);
                        $v->setCreated($created);
                        $v->setLength($length);
                        $v->setLastCheck(new \DateTime());
                        $v->setStreamer($streamer);

                        $this->em->persist($v);

                        try {
                            $this->em->flush();
                        } catch (\Exception $e) {
                            throw new StreamPlatformException('MySQL Error: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * @return Streamer|null
     */
    public function getStreamer(): ?Streamer
    {
        return $this->streamer;
    }

    /**
     * @param Streamer|null $streamer
     */
    public function setStreamer(?Streamer $streamer): void
    {
        $this->streamer = $streamer;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @param string $endpoint
     * @param bool $useV5
     * @return mixed
     * @throws StreamPlatformException
     */
    private function data(string $endpoint, bool $useV5 = false)
    {

        $url = $this->url . $endpoint;

        $headers = array(
            'Client-ID: ' . getenv('TWITCH_CLIENT_ID')
        );

        /**
         * Use the v5 API for missing fields
         */
        if ($useV5) {
            $headers[] = 'Accept: application/vnd.twitchtv.v5+json';
        }


        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($ch);
            $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

        } catch (\Exception $e) {
            throw new StreamPlatformException('cURL Error: ' . $e->getMessage());
        }

        if (!$this->status === 200) {
            throw new StreamPlatformException(
                'Error with the Twitch API, please try again in a few moments.' .
                ' Status Code: ' . $this->status .
                ' Url: ' . $url
            );
        }

        return json_decode($result, true);
    }


}