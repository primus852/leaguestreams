# ReadMe #

## todo ##
- update this ReadMe

## Nice functions to look at ##
### Search/Add Summoner ###
#### Problem ####
- The search for a summoner by name and updating all his/her stats can take some time. I wanted to show the progress for each step of [checkSummonerAction](https://github.com/primus852/leaguestreams/blob/master/src/Controller/AjaxController.php#L61) but PHP is not very good with that.
#### Solution ####
- Set Session vars (and SAVE!!!, thanks to [THIS](https://codingexplained.com/coding/php/solving-concurrent-request-blocking-in-php)) and request with another ajax call periodically: [Click #submitSummoner](https://github.com/primus852/leaguestreams/blob/master/public/assets/ls/js/app.js#L872)

### Jump to VOD by Game ###
#### Problem ####
- Twitch does not save any timestamps or info when a game starts or ends
#### Solution ####
- Calculate the offset of a VOD with the time a game starts [getByStreamer()](https://github.com/primus852/leaguestreams/blob/master/src/Utils/LSVods.php#L435)