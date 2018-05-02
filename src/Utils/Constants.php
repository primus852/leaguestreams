<?php

namespace App\Utils;

class Constants
{

    /* Old Twitch API //TODO deprecated 2018/12/31 */
    const TWITCH_V5 = 'https://api.twitch.tv/kraken/';

    /* New Twitch Api */
    const TWITCH_HELIX = 'https://api.twitch.tv/helix/';
    const TWITCH_LOL_ID = '21779';

    /* Number of Smurfs required before going to DB */
    const SMURFS_REQUIRED = 5;

    /* Smurfs need report or go to DB directly (false) */
    const SMURFS_ENABLED = false;

    /* Simple Crypt  UPDATE WITH CARE!!! */
    const SC_METHOD = 'AES-256-CBC';

}
