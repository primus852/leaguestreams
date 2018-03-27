<?php

namespace App\Utils;

class Constants{


    /* Riot API endpoints */
    const API_URL_PLATFORM = "https://{platform}.api.riotgames.com/lol/platform/v3/";
    const API_URL_CHAMPION_MASTERY = "https://{platform}.api.riotgames.com/lol/champion-mastery/v3/";
    const API_URL_SPECTATOR = 'https://{platform}.api.riotgames.com/lol/spectator/v3/';
    const API_URL_STATIC = 'https://{platform}.api.riotgames.com/lol/static-data/v3/';
    const API_URL_MATCH = 'https://{platform}.api.riotgames.com/lol/match/v3/';
    const API_URL_LEAGUE = 'https://{platform}.api.riotgames.com/lol/league/v3/';
    const API_URL_SUMMONER = 'https://{platform}.api.riotgames.com/lol/summoner/v3/';

    /* Riot API Key */
    const API_KEY = "RGAPI-ENTER-YOUR-API-KEY";
    //const API_KEY = "RGAPI-ENTER-YOUR-API-KEY"; //DEV

    /* Riot API Ratelimits */
    //TODO: This needs to account the python crawl as well
    const API_MAX_SHORT = 3000;
    const API_SHORT_INTERVAL = 10;

    const API_MAX_LONG = 180000;
    const API_LONG_INTERVAL = 600;

    /* Cache Timeout for requests */
    /* !! This is fairly low, due to the fact that most hits come from the crawler, not the manual refresh !! */
    const CACHE_REFRESH = 10;

    /* Riot Error Codes */
    const RIOT_ERROR_CODES = array(
        0   => 'The Riot API returned no response',
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

    /* Twitch API Client ID */
    const TWITCH_API_CLIENT = 'ENTER-YOUR-API-KEY';

    /* Old Twitch API //TODO deprecated 2018/12/31 */
    const TWITCH_V5 = 'https://api.twitch.tv/kraken/';

    /* New Twitch  Api */
    const TWITCH_HELIX = 'https://api.twitch.tv/helix/';
    const TWITCH_LOL_ID = '21779';

    /* Number of Smurfs required before going to DB */
    const SMURFS_REQUIRED = 5;

    /* Smurfs need report or go to DB directly (false) */
    const SMURFS_ENABLED = true;

    /* Simple Crypt  UPDATE WITH CARE!!! */
    const SC_KEY = 'YOUR-KEY';
    const SC_IV = 'YOUR-IV';
    const SC_METHOD = 'AES-256-CBC';

    /* Versions of Riot API endpoints */

    /* --->Static Data */
    const RIOT_STATIC_API_VERSION = '1.2';

    /* --->Summoners */
    const RIOT_STATIC_API_SUMMONER = '1.4';

    /* --->League */
    const RIOT_STATIC_API_LEAGUE = '2.5';

    /* --->Match History */
    const RIOT_STATIC_API_MATCH = '2.2';

    /* ---> NEW GLOBAL VERSION */
    const RIOT_CURRENT_VERSION = '3';

}