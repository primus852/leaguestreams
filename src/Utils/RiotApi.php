<?php

namespace App\Utils;

use Symfony\Component\Config\Definition\Exception\Exception;

class RiotApi
{


    private $region;
    private $shortQueue;
    private $longQueue;
    private $responseCode;
    private $cache;


    /**
     * RiotApi constructor.
     * @param FileSystemCache|null $cache
     * @param string $region
     */
    public function __construct(FileSystemCache $cache = null, $region = 'na1')
    {

        $this->region = $region;
        $this->shortQueue = new \SplQueue();
        $this->longQueue = new \SplQueue();
        $this->cache = $cache;
    }

    /**
     * @param bool $free
     * @return mixed
     */
    public function getChampion($free = false)
    {

        $mod = 'champions';
        $url = Constants::API_URL_PLATFORM . $mod . '?freeToPlay=' . $free;

        return $this->getData($url);
    }

    /**
     * @param $id
     * @param string $locale
     * @return mixed
     */
    public function getChampionById($id, $locale = 'en_US')
    {

        $mod = 'champions/' . $id . '?locale=' . $locale;
        $url = Constants::API_URL_STATIC . $mod;

        return $this->getData($url);
    }

    /**
     * @param $id
     * @param bool $championId
     * @return mixed
     */
    public function getChampionMastery($id, $championId = false)
    {

        $mod = 'champion-masteries/by-summoner/' . $id;

        if ($championId)
            $mod .= "/by-champion/" . $championId;

        $url = Constants::API_URL_CHAMPION_MASTERY . $mod;

        return $this->getData($url);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getCurrentGame($id)
    {

        $mod = 'active-games/by-summoner/' . $id;
        $url = Constants::API_URL_SPECTATOR . $mod;

        return $this->getData($url);
    }

    /**
     * @param $mod
     * @param null $id
     * @param null $params
     * @return mixed
     */
    public function getStatic($mod, $id = null, $params = null)
    {

        $url = Constants::API_URL_STATIC . $mod;

        if ($id !== null)
            $url .= "/" . $id;

        if ($params !== null)
            $url .= "?" . $params;

        return $this->getData($url, true);
    }

    public function getMatch($matchId, $includeTimeline = true)
    {

        $mod = 'matches/' . $matchId;
        $url = Constants::API_URL_MATCH . $mod;

        if (!$includeTimeline) {
            return $this->getData($url);
        }

        $modTimeline = 'timelines/by-match/' . $matchId;
        $urlTimeline = Constants::API_URL_MATCH . $modTimeline;

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
     */
    public function getTimeline($matchId)
    {

        $mod = 'timelines/by-match/' . $matchId;
        $url = Constants::API_URL_MATCH . $mod;

        return $this->getData($url);
    }

    /**
     * @param $accountId
     * @param null $params
     * @return mixed
     */
    public function getMatchList($accountId, $params = null)
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

        $url = Constants::API_URL_MATCH . $mod;

        return $this->getData($url);
    }

    /**
     * @param $accountId
     * @return mixed
     */
    public function getRecentMatchList($accountId)
    {

        $mod = 'matchlists/by-account/' . $accountId . '/recent';
        $url = Constants::API_URL_MATCH . $mod;

        return $this->getData($url);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getLeague($id)
    {

        $mod = 'leagues/by-summoner/' . $id;
        $url = Constants::API_URL_LEAGUE . $mod;

        return $this->getData($url);
    }


    /**
     * @param $id
     * @param string $type
     * @return mixed
     */
    public function getLeaguePosition($id, $type = 'RANKED_SOLO_5x5')
    {

        $mod = 'positions/by-summoner/' . $id;
        $url = Constants::API_URL_LEAGUE . $mod;

        $positions = $this->getData($url);

        foreach ($positions as $key => $position) {

            if ($position['queueType'] === $type) {
                return $positions[$key];
            }

        }

        throw new Exception(Constants::RIOT_ERROR_CODES[404]);

    }

    /**
     * @param string $queue
     * @return mixed
     */
    public function getChallenger($queue = "RANKED_SOLO_5x5")
    {

        $mod = 'challengerleagues/by-queue/' . $queue;
        $url = Constants::API_URL_LEAGUE . $mod;

        return $this->getData($url);
    }

    /**
     * @param string $queue
     * @return mixed
     */
    public function getMaster($queue = "RANKED_SOLO_5x5")
    {

        $mod = 'masterleagues/by-queue/' . $queue;
        $url = Constants::API_URL_LEAGUE . $mod;

        return $this->getData($url);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSummonerId($name)
    {

        $name = strtolower($name);
        $summoner = $this->getSummonerByName($name);

        return $summoner['id'];

    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSummonerAccountId($name)
    {

        $name = strtolower($name);
        $summoner = $this->getSummonerByName($name);

        return $summoner['accountId'];
    }

    /**
     * @param $id
     * @param bool $accountId
     * @return mixed
     */
    public function getSummoner($id, $accountId = false)
    {

        $mod = 'summoners/';
        if ($accountId) {
            $mod .= 'by-account/';
        }

        $mod .= $id;
        $url = Constants::API_URL_SUMMONER . $mod;

        return $this->getData($url);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function getSummonerByName($name)
    {

        $mod = 'summoners/by-name/' . rawurlencode($name);
        $url = Constants::API_URL_SUMMONER . $mod;

        return $this->getData($url);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getMasteries($id)
    {

        $mod = 'masteries/by-summoner/' . $id;
        $url = Constants::API_URL_PLATFORM . $mod;

        return $this->getData($url);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRunes($id)
    {

        $mod = 'runes/by-summoner/' . $id;
        $url = Constants::API_URL_PLATFORM . $mod;

        return $this->getData($url);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getRunesReforged($id)
    {

        // TODO: Update to correct url
        $mod = 'runes-reforged/by-summoner/' . $id;
        $url = Constants::API_URL_PLATFORM . $mod;

        return $this->getData($url);
    }

    public function getStatus()
    {

        $mod = 'shard-data';
        $url = Constants::API_URL_STATUS . $mod;

        return $this->getData($url);
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
            $calls["match-" . $matchId] = Constants::API_URL_MATCH . $mod;

            if ($includeTimeline) {
                $modTimeline = 'timelines/by-match/' . $matchId;
                $calls["timeline-" . $matchId] = Constants::API_URL_MATCH . $modTimeline;
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
     */
    private function getData($plainUrl, $static = false)
    {


        $url = $this->formatUrl($plainUrl);

        if ($this->cache !== null && $this->cache->has($url)) {
            $result = $this->cache->get($url);
        } else {
            if (!$static) {
                $this->updateQueue($this->longQueue, Constants::API_LONG_INTERVAL, Constants::API_MAX_LONG);
                $this->updateQueue($this->longQueue, Constants::API_SHORT_INTERVAL, Constants::API_MAX_SHORT);
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'X-Riot-Token: ' . Constants::API_KEY
            ));

            $result = curl_exec($ch);
            $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($this->responseCode == 200) {
                if ($this->cache !== null) {
                    $this->cache->put($url, $result, Constants::CACHE_REFRESH);
                }
            } else {
                throw new Exception(Constants::RIOT_ERROR_CODES[$this->responseCode]);
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
                $this->cache->put($urls[$k], $result, Constants::CACHE_REFRESH);
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
                'X-Riot-Token: ' . Constants::API_KEY
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