<?php

namespace App\Utils\RiotApi;


use App\Entity\Champion;

class RiotApi
{

    private $region;
    private $shortQueue;
    private $longQueue;
    private $responseCode;
    private $cache;
    private $setting;
    private $key;

    /* Riot API endpoints */
    private const API_URL_PLATFORM = "https://{platform}.api.riotgames.com/lol/platform/v3/";
    private const API_URL_CHAMPION_MASTERY = "https://{platform}.api.riotgames.com/lol/champion-mastery/v3/";
    private const API_URL_CHAMPION_MASTERY_V4 = "https://{platform}.api.riotgames.com/lol/champion-mastery/v4/";
    private const API_URL_SPECTATOR = 'https://{platform}.api.riotgames.com/lol/spectator/v3/';
    private const API_URL_SPECTATOR_V4 = 'https://{platform}.api.riotgames.com/lol/spectator/v4/';
    private const API_URL_STATIC = 'https://{platform}.api.riotgames.com/lol/static-data/v3/';
    private const API_URL_MATCH = 'https://{platform}.api.riotgames.com/lol/match/v3/';
    private const API_URL_MATCH_V4 = 'https://{platform}.api.riotgames.com/lol/match/v4/';
    private const API_URL_LEAGUE = 'https://{platform}.api.riotgames.com/lol/league/v3/';
    private const API_URL_LEAGUE_V4 = 'https://{platform}.api.riotgames.com/lol/league/v4/';
    private const API_URL_SUMMONER = 'https://{platform}.api.riotgames.com/lol/summoner/v3/';
    private const API_URL_SUMMONER_V4 = 'https://{platform}.api.riotgames.com/lol/summoner/v4/';
    private const API_URL_STATUS = 'https://{platform}.api.riotgames.com/lol/status/v3/';
    private const API_STATIC_VERSION = 'https://ddragon.leagueoflegends.com/api/versions.json';
    private const API_STATIC_CHAMPIONS = 'http://ddragon.leagueoflegends.com/cdn/{version}/data/en_US/champion.json';
    private const API_STATIC_CHAMPION = 'http://ddragon.leagueoflegends.com/cdn/{version}/data/en_US/champion/{champion}.json';

    /**
     * Cache Timeout for requests to the Riot Api
     * Fairly low, due to the fact that most hits come from the Python Crawler
     * @const CACHE_REFRESH
     */
    private const CACHE_REFRESH = 10;

    private const RIOT_ERROR_CODES = array(
        0 => 'The Riot API returned no response',
        400 => 'Bad Request',
        401 => 'You are not authorized to make this request',
        403 => 'You are not allowed to make this request',
        404 => 'Not found',
        405 => 'This method is not allowed',
        415 => 'This media type is not supported',
        429 => 'The rate limit was exceeded, please try again in a few minutes',
        500 => 'Server Error',
        502 => 'Bad Gateway',
        503 => 'The Riot API is currently not available',
        504 => 'The Gateway has timed out',
    );

    /**
     * RiotApi constructor.
     * @param Settings $riotApiSetting
     * @param Cache|null $cache
     * @param string $region
     */
    public function __construct(Settings $riotApiSetting, Cache $cache = null, $region = 'na1')
    {

        $this->region = $region;
        $this->shortQueue = new \SplQueue();
        $this->longQueue = new \SplQueue();
        $this->cache = $cache;
        $this->setting = $riotApiSetting->getSettings();
        $this->key = $riotApiSetting->getKey();
    }

    /**
     * @param $id
     * @param bool $championId
     * @param bool $upgrade
     * @return mixed
     * @throws RiotApiException
     */
    public function getChampionMastery($id, $championId = false, bool $upgrade = false)
    {

        $mod = 'champion-masteries/by-summoner/' . $id;

        if ($championId)
            $mod .= "/by-champion/" . $championId;

        $url = $upgrade === false ? self::API_URL_CHAMPION_MASTERY . $mod : self::API_URL_CHAMPION_MASTERY_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetChampionMastery Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @param bool $upgrade
     * @return mixed
     * @throws RiotApiException
     */
    public function getCurrentGame($id, bool $upgrade = false)
    {

        $mod = 'active-games/by-summoner/' . $id;
        $url = $upgrade === false ? self::API_URL_SPECTATOR . $mod : self::API_URL_SPECTATOR_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetCurrentGame Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $mod
     * @param null $id
     * @param null $params
     * @return mixed
     * @throws RiotApiException
     * @deprecated
     */
    public function getStatic($mod, $id = null, $params = null)
    {

        $url = self::API_URL_STATIC . $mod;

        if ($id !== null)
            $url .= "/" . $id;

        if ($params !== null)
            $url .= "?" . $params;

        try {
            return $this->getData($url, true);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetStatic Exception: ' . $e->getMessage());
        }
    }

    /**
     * @return mixed
     * @throws RiotApiException
     */
    public function getVersion()
    {
        $url = self::API_STATIC_VERSION;

        try {
            return $this->getData($url, true);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetVersion Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param string $version
     * @return mixed
     * @throws RiotApiException
     */
    public function getChampions(string $version)
    {
        $url = str_replace('{version}', $version, self::API_STATIC_CHAMPIONS);

        try {
            return $this->getData($url, true);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetChampions Exception: ' . $e->getMessage());
        }

    }

    /**
     * @param string $version
     * @param string $champion
     * @return mixed
     * @throws RiotApiException
     */
    public function getChampion(string $version, string $champion)
    {

        $url = str_replace('{version}', $version, str_replace('{champion}', $champion, self::API_STATIC_CHAMPION));

        try {
            return $this->getData($url, true);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetChampion Exception: ' . $e->getMessage());
        }

    }

    /**
     * @param $matchId
     * @param bool $includeTimeline
     * @param bool $upgrade
     * @return mixed
     * @throws RiotApiException
     */
    public function getMatch($matchId, $includeTimeline = true, bool $upgrade = false)
    {

        $mod = 'matches/' . $matchId;
        $url = $upgrade === false ? self::API_URL_MATCH . $mod : self::API_URL_MATCH_V4 . $mod;

        if (!$includeTimeline) {
            try {
                return $this->getData($url);
            } catch (RiotApiException $e) {
                throw new RiotApiException('GetMatch Exception: ' . $e->getMessage());
            }
        }

        $modTimeline = 'timelines/by-match/' . $matchId;
        $urlTimeline = $upgrade === false ? self::API_URL_MATCH . $modTimeline : self::API_URL_MATCH_V4 . $modTimeline;

        $data = $this->getMultipleData(array(
            "data" => $url,
            "timeline" => $urlTimeline
        ));

        $data["data"]["timeline"] = $data["timeline"];

        return $data["data"];

    }

    /**
     * @param $matchId
     * @return mixed
     * @throws RiotApiException
     */
    public function getTimeline($matchId)
    {

        $mod = 'timelines/by-match/' . $matchId;
        $url = self::API_URL_MATCH_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetTimeline Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $accountId
     * @param null $params
     * @param bool $upgrade
     * @return mixed
     * @throws RiotApiException
     */
    public function getMatchList($accountId, $params = null, bool $upgrade = false)
    {

        $mod = 'matchlists/by-account/' . $accountId;
        if ($params !== null) {
            $mod = 'matchlists/by-account/' . $accountId . '?';
        } else {

            if (is_array($params)) {
                foreach ($params as $key => $param) {

                    if (is_array($param)) {
                        foreach ($param as $p) {
                            $mod .= $key . '=' . $p . '&';
                        }
                    } else {
                        $mod .= $key . '=' . $param . '&';
                    }
                }
            } else
                $mod .= $params . '&';
        }

        $url = $upgrade === false ? self::API_URL_MATCH . $mod : self::API_URL_MATCH_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetMatchList Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $accountId
     * @return mixed
     * @throws RiotApiException
     */
    public function getRecentMatchList($accountId)
    {

        $mod = 'matchlists/by-account/' . $accountId . '/recent';
        $url = self::API_URL_MATCH_V4 . $mod;


        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetRecentMatchList Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws RiotApiException
     */
    public function getLeague($id)
    {

        $mod = 'leagues/by-summoner/' . $id;
        $url = self::API_URL_LEAGUE_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetLeague Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @param string $type
     * @param bool $upgrade
     * @return mixed
     * @throws RiotApiException
     */
    public function getLeaguePosition($id, $type = 'RANKED_SOLO_5x5', bool $upgrade = false)
    {

        $mod = 'entries/by-summoner/' . $id;
        $url = $upgrade === false ? self::API_URL_LEAGUE . $mod : self::API_URL_LEAGUE_V4 . $mod;

        $positions = $this->getData($url);

        foreach ($positions as $key => $position) {

            if ($position['queueType'] === $type) {
                return $positions[$key];
            }

        }

        throw new RiotApiException(self::RIOT_ERROR_CODES[404] . ' [404]');

    }

    /**
     * @param string $queue
     * @return mixed
     * @throws RiotApiException
     */
    public function getChallenger($queue = "RANKED_SOLO_5x5")
    {

        $mod = 'challengerleagues/by-queue/' . $queue;
        $url = self::API_URL_LEAGUE_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetChallenger Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param string $queue
     * @return mixed
     * @throws RiotApiException
     */
    public function getMaster($queue = "RANKED_SOLO_5x5")
    {

        $mod = 'masterleagues/by-queue/' . $queue;
        $url = self::API_URL_LEAGUE_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetMaster Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $name
     * @return mixed
     * @throws RiotApiException
     */
    public function getSummonerId($name)
    {

        $name = strtolower($name);

        try {
            $summoner = $this->getSummonerByName($name, true);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetSummonerId Exception: ' . $e->getMessage());
        }


        return $summoner['id'];

    }

    /**
     * @param $name
     * @return mixed
     * @throws RiotApiException
     */
    public function getSummonerAccountId($name)
    {

        $name = strtolower($name);

        try {
            $summoner = $this->getSummonerByName($name, true);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetSummonerAccountId Exception: ' . $e->getMessage());
        }


        return $summoner['accountId'];
    }


    /**
     * @param $id
     * @param bool $accountId
     * @param bool $upgrade
     * @return mixed
     * @throws RiotApiException
     */
    public function getSummoner($id, $accountId = false, bool $upgrade = false)
    {

        $mod = 'summoners/';
        if ($accountId) {
            $mod .= 'by-account/';
        }

        $mod .= $id;
        $url = $upgrade === false ? self::API_URL_SUMMONER . $mod : self::API_URL_SUMMONER_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetSummoner Exception: ' . $e->getMessage() . ' Url: ' . $url);
        }
    }


    /**
     * @param $name
     * @param bool $upgrade
     * @return mixed
     * @throws RiotApiException
     */
    public function getSummonerByName($name, bool $upgrade = false)
    {

        $mod = 'summoners/by-name/' . rawurlencode($name);
        $url = $upgrade === false ? self::API_URL_SUMMONER . $mod : self::API_URL_SUMMONER_V4 . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetSummonerByName Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws RiotApiException
     */
    public function getMasteries($id)
    {

        $mod = 'masteries/by-summoner/' . $id;
        $url = self::API_URL_PLATFORM . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetMasteries Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws RiotApiException
     */
    public function getRunes($id)
    {

        $mod = 'runes/by-summoner/' . $id;
        $url = self::API_URL_PLATFORM . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetRunes Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws RiotApiException
     */
    public function getRunesReforged($id)
    {

        $mod = 'runes-reforged/by-summoner/' . $id;
        $url = self::API_URL_PLATFORM . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetRunesReforged Exception: ' . $e->getMessage());
        }
    }

    /**
     * @return mixed
     * @throws RiotApiException
     */
    public function getStatus()
    {

        $mod = 'shard-data';
        $url = self::API_URL_STATUS . $mod;

        try {
            return $this->getData($url);
        } catch (RiotApiException $e) {
            throw new RiotApiException('GetStatus Exception: ' . $e->getMessage());
        }
    }

    /**
     * @param array $ids
     * @param bool $includeTimeline
     * @return array
     */
    public function getMatches(array $ids, $includeTimeline = true)
    {

        $calls = array();

        foreach ($ids as $matchId) {

            $mod = 'matches/' . $matchId;
            $calls["match-" . $matchId] = self::API_URL_MATCH . $mod;

            if ($includeTimeline) {
                $modTimeline = 'timelines/by-match/' . $matchId;
                $calls["timeline-" . $matchId] = self::API_URL_MATCH . $modTimeline;
            }
        }

        if (!$includeTimeline) {
            return $this->getMultipleData($calls);
        }


        $results = array();

        $data = $this->getMultipleData($calls);

        foreach ($data as $k => $d) {
            $e = explode("-", $k);

            if ($e[0] == "match") {
                if (isset($data["timeline-" . $e[1]]["frames"])) {
                    $d["timeline"] = $data["timeline-" . $e[1]];
                }
                array_push($results, $d);
            }
        }

        return $results;
    }


    /**
     * @param $plainUrl
     * @param bool $static
     * @return mixed
     * @throws RiotApiException
     */
    private function getData($plainUrl, $static = false)
    {


        $url = $this->formatUrl($plainUrl);

        if ($this->cache !== null && $this->cache->has($url)) {
            $result = $this->cache->get($url);
        } else {
            if (!$static) {
                $this->updateQueue($this->longQueue, $this->setting['max_requests_long'], $this->setting['interval_long']);
                $this->updateQueue($this->shortQueue, $this->setting['max_requests_short'], $this->setting['interval_short']);
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Riot-Token: ' . $this->key
            ));

            $result = curl_exec($ch);
            $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($this->responseCode == 200) {
                if ($this->cache !== null) {
                    $this->cache->put($url, $result, self::CACHE_REFRESH);
                }
            } else {
                throw new RiotApiException(self::RIOT_ERROR_CODES[$this->responseCode] . ' [' . $this->responseCode . '|'.$url.']');
            }
        }

        return json_decode($result, true);
    }

    /**
     * @param array $calls
     * @return array
     */
    private function getMultipleData(array $calls)
    {

        $urls = array();
        $results = array();

        foreach ($calls as $k => $call) {

            $url = $this->formatUrl($call);

            if ($this->cache !== null && $this->cache->has($url)) {
                $results[$k] = $this->cache->get($url);
            } else {
                $urls[$k] = $url;
            }
        }

        $callResult = $this->getMultiThreadData($urls);

        foreach ($callResult as $k => $result) {
            if ($this->cache !== null) {
                $this->cache->put($urls[$k], $result, self::CACHE_REFRESH);
            }
            $results[$k] = json_decode($result, true);
        }

        return array_merge($results);
    }

    /**
     * @param $nodes
     * @return array
     */
    private function getMultiThreadData($nodes)
    {
        $mh = curl_multi_init();
        $curl_array = array();
        foreach ($nodes as $i => $url) {
            $curl_array[$i] = curl_init($url);
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, array(
                'X-Riot-Token: ' . $this->key
            ));
            curl_multi_add_handle($mh, $curl_array[$i]);
        }
        $running = NULL;
        do {
            usleep(10000);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $res = array();
        foreach ($nodes as $i => $url) {
            $res[$i] = curl_multi_getcontent($curl_array[$i]);
        }

        foreach ($nodes as $i => $url) {
            curl_multi_remove_handle($mh, $curl_array[$i]);
        }
        curl_multi_close($mh);
        return $res;
    }

    private function updateQueue(\SplQueue $queue, $interval, $call_limit)
    {

        while (!$queue->isEmpty()) {

            $timeSinceOldest = time() - $queue->bottom();

            if ($timeSinceOldest > $interval) {
                $queue->dequeue();
            } elseif ($queue->count() >= $call_limit) {
                if ($timeSinceOldest < $interval) {
                    sleep($interval - $timeSinceOldest);
                }
            } else {
                break;
            }
        }

        $queue->enqueue(time());
    }

    /**
     * @param $url
     * @return mixed
     */
    private function formatUrl($url)
    {
        return str_replace('{platform}', $this->region, $url);
    }

    /**
     * @return mixed
     */
    public function getLastResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @param $region
     */
    public function setRegion($region)
    {
        $this->region = strtolower($region);
    }

    /**
     * @return string
     */
    public function getRegion(): string
    {
        return $this->region;
    }

}