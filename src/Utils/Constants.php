<?php

namespace App\Utils;

class Constants
{


    /* Riot API Key */
    const API_KEY = "RGAPI-YOUR-API-KEY";
    //const API_KEY = "RGAPI-YOUR-API-KEY"; //DEV

    /* Twitch API Client ID */
    const TWITCH_API_CLIENT = 'YOUR-API-KEY';

    /* Old Twitch API //TODO deprecated 2018/12/31 */
    const TWITCH_V5 = 'https://api.twitch.tv/kraken/';

    /* New Twitch  Api */
    const TWITCH_HELIX = 'https://api.twitch.tv/helix/';
    const TWITCH_LOL_ID = '21779';

    /* Number of Smurfs required before going to DB */
    const SMURFS_REQUIRED = 5;

    /* Smurfs need report or go to DB directly (false) */
    const SMURFS_ENABLED = false;

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
