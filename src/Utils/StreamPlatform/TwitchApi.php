<?php

namespace App\Utils\StreamPlatform;


use App\Entity\OnlineTime;
use App\Entity\Platform;
use App\Entity\Streamer;
use App\Entity\TwitchOauth;
use App\Entity\Vod;
use DateTime;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Exception;
use primus852\SimpleStopwatch\Stopwatch;
use primus852\SimpleStopwatch\StopwatchException;

class TwitchApi implements StreamPlatformInterface
{

    private $em;
    private $streamer;
    private $status;
    private $url = 'https://api.twitch.tv';
    private $urlOAuth = 'https://id.twitch.tv';
    # private $gameId = '21779';

    /**
     * TwitchApi constructor.
     * @param ObjectManager|null $em
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
            $data = $this->_data($url, true);
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
            $data = $this->_data($url, true);
        } catch (StreamPlatformException $e) {
            throw new StreamPlatformException($e->getMessage());
        }

        /**
         * Check if the stream key is not null ( = is online)
         */
        $result = false;
        $stream = null;
        $randTrace = rand();
        if (!array_key_exists('stream', $data)) {
            dump('STREAM EMPTY '.$randTrace);
            return false;
        }
        dump('STILL HERE '.$randTrace);
        if ($data['stream'] !== null) {

            /**
             * Check if it is the correct game name
             */
            if (!array_key_exists('game', $data['stream'])) {
                return false;
            }
            if (strtolower($data['stream']['game']) === strtolower('League of Legends')) {
                $stream = $data['stream'];
                $result = true;
            }
        }

        /**
         * Check if we need to update the DB Data
         */
        if ($update) {

            dump(getenv('TWITCH_CLIENT_SECRET'));
            dump($data);

            /**
             * Check if we found the Streamer streaming LoL
             */

            $channelData = $stream['channel'];

            /**
             * Get Info for Streamer User
             */
            $isPartner = $channelData['partner'] === true;

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
            try {
                $streamer->setStarted(new DateTime($stream['created_at']));
            } catch (Exception $e) {
                throw new StreamPlatformException('Could not create DateTime from ' . $stream['created_at']);
            }

            /**
             * If the Streamer is online, update the total time Online (now - last modified)
             */
            try {
                $minutes = Stopwatch::stop($streamer->getModified(), true, 'm');
            } catch (StopwatchException $e) {
                throw new StreamPlatformException('Error parsing Timer: ' . $e->getMessage());
            }

            /**
             * Find a OnlineTime for the respective Streamer
             */
            $today = new DateTime();
            $onlineTime = $this->em->getRepository(OnlineTime::class)->findOneBy(array(
                'Streamer' => $streamer,
                'onlineDate' => $today
            ));

            if ($onlineTime === null) {
                $onlineTime = new OnlineTime();
                $onlineTime->setTotalOnline(0);
                $onlineTime->setStreamer($streamer);
                $onlineTime->setOnlineDate($today);
            } else {
                $onlineTime->setTotalOnline($onlineTime->getTotalOnline() + $minutes);
            }


            /**
             * Now we update the Modified Col
             */
            $streamer->setModified();

            $this->em->persist($streamer);
            $this->em->persist($onlineTime);

            try {
                $this->em->flush();
            } catch (Exception $e) {
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
            $data = $this->_data($url);
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
            $data = $this->_data($url, true);
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
                        $v->setLastCheck(new DateTime());
                        $v->setStreamer($streamer);

                        $this->em->persist($v);

                        try {
                            $this->em->flush();
                        } catch (Exception $e) {
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
     * @param bool $isRetry
     * @return mixed
     * @throws StreamPlatformException
     */
    private function _data(string $endpoint, bool $useV5 = false, bool $isRetry = false)
    {

        /**
         * Get the latest Token
         */
        $token = $this->_getToken();

        $url = $this->url . $endpoint;

        $headers = array(
            'Client-ID: ' . getenv('TWITCH_CLIENT_ID')
        );

        /**
         * Use the v5 API for missing fields
         */
        if ($useV5) {
            $headers[] = 'Accept: application/vnd.twitchtv.v5+json';
            $headers[] = 'Authorization: OAuth ' . $token->getAccessToken();
        } else {
            $headers[] = 'Authorization: Bearer ' . $token->getAccessToken();
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $result = json_decode(curl_exec($ch), true);
            $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

        } catch (Exception $e) {
            throw new StreamPlatformException('cURL Error: ' . $e->getMessage());
        }

        if (!$this->status === 200) {

            throw new StreamPlatformException(
                'Error with the Twitch API, please try again in a few moments.' .
                ' Status Code: ' . $this->status .
                ' Url: ' . $url
            );
        }

        if (array_key_exists('status', $result)) {
            if ($result['status'] === 401 && !$isRetry) {
                /**
                 * Refresh the Token and try again
                 */
                $this->_refreshToken($token);
                $this->_data($endpoint, $useV5, true);
            }
        }

        return $result;
    }

    /**
     * @return TwitchOauth|object|null
     * @throws StreamPlatformException
     */
    private function _getToken()
    {

        /**
         * Select the last Token
         */
        $token = $this->em->getRepository(TwitchOauth::class)->findOneBy(
            array(),
            array(
                'expiresAt' => 'DESC'
            )
        );

        /**
         * If no Token in DB, create a new one
         */
        if ($token === null) {
            try {
                return $this->_obtainToken();
            } catch (StreamPlatformException $e) {
                throw new StreamPlatformException($e->getMessage());
            }
        }

        return $token;

    }

    /**
     * @param TwitchOauth $token
     * @return TwitchOauth
     * @throws StreamPlatformException
     */
    private function _refreshToken(TwitchOauth $token)
    {

        if ($token->getRefreshToken() === null) {
            try {
                return $this->_obtainToken();
            } catch (StreamPlatformException $e) {
                throw new StreamPlatformException($e->getMessage());
            }
        }

        /**
         * Try to refresh it first
         */
        $url = $this->urlOAuth . '/oauth2/token';

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'client_id' => getenv('TWITCH_CLIENT_ID'),
                'client_secret' => getenv('TWITCH_CLIENT_SECRET'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $token->getRefreshToken()
            ));

            $result = json_decode(curl_exec($ch), true);
            $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

        } catch (Exception $e) {
            throw new StreamPlatformException('cURL _refreshToken Error: ' . $e->getMessage());
        }

        /**
         * Invalid Token, get a new one
         */
        if ($result['status'] === 400 || $result['status'] === 401) {
            try {
                return $this->_obtainToken();
            } catch (StreamPlatformException $e) {
                throw new StreamPlatformException($e->getMessage());
            }
        }

        /**
         * Refresh success, update the AccessToken
         */
        $token->setAccessToken($result['access_token']);
        $token->setRefreshToken($result['refresh_token']);
        $token->setScope(json_encode($result['scope']));

        $this->em->persist($token);

        try {
            $this->em->flush();
        } catch (Exception $exception) {
            throw new StreamPlatformException('MySQL Error while flushing OAuth Token Refresh');
        }

        return $token;

    }

    /**
     * @return TwitchOauth
     * @throws StreamPlatformException
     */
    private function _obtainToken()
    {

        $url = $this->urlOAuth . '/oauth2/token';

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
                'client_id' => getenv('TWITCH_CLIENT_ID'),
                'client_secret' => getenv('TWITCH_CLIENT_SECRET'),
                'grant_type' => 'client_credentials'
            ));

            $result = json_decode(curl_exec($ch), true);
            $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

        } catch (Exception $e) {
            throw new StreamPlatformException('cURL _getToken Error: ' . $e->getMessage());
        }

        /**
         * Create Expiry DateTime
         */
        $expiry = new DateTime();
        $expiry->modify('+' . $result['expires_in'] . ' seconds');

        /**
         * Save Token to DB
         */
        $token = new TwitchOauth();
        $token->setAccessToken($result['access_token']);
        if (array_key_exists('refresh_token', $result)) {
            $token->setRefreshToken($result['refresh_token']);
        }
        if (array_key_exists('scope', $result)) {
            $token->setScope(json_encode($result['scope']));
        }
        $token->setExpiresAt($expiry);
        $token->setTokenType($result['token_type']);

        $this->em->persist($token);

        try {
            $this->em->flush();
        } catch (Exception $e) {
            throw new StreamPlatformException('MySQL Error while flushing OAuth Token: ' . $e->getMessage());
        }

        return $token;


    }


}