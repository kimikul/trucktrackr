<?php

require_once(LIBRARY_PATH . "/twitter.php");
require_once(LIBRARY_PATH . "/addresstolatlon.php");
require_once(LIBRARY_PATH . "/locparse.php");

//Function takes in twitter ID
//function grabs most recent 20 posts
//function returns array of 20 addresses
function getAddressFromUserTimeline($twitterID)
{

    //grab last post id of twitter
    $query = "SELECT last_post_id FROM trackingData WHERE twitter_id = '".$twitterID."'";
    $selectRes = mysql_query($query);
    if ($selectRes)
    {
        $row = mysql_fetch_assoc($selectRes);
        echo "<br>LAST POST ID: ". $row['last_post_id']."<br>";
        $lastPostID = $row['last_post_id'];
    }

    //get user timeline from last post and onward
    $twitterUser = new Twitter("seunghyochoi","cs130pw");
    $userInfo = $twitterUser->getUser($twitterID);

    //if((int)$lastPostID == 0)
    //{
        $tweetList = $twitterUser->getUserTimeline($twitterID);
    //}
    //else
    //{
    //    $tweetList = $twitterUser->getUserTimeline($twitterID, (int)$lastPostID);
    //}


    //okay no go through the tweets
    //stops at FIRST successful address parse
    $geolocs = array();
    foreach($tweetList as $tweet)
    {
        $tweetInfo = extractInfoFromTweet($tweet);
        $parsedAddress = parseAddressFromText($tweetInfo['text']);
        $parsedAddress['geocode'] = parseGeoCode($tweetInfo['text'], $userInfo, $tweetInfo['post_source']);
        $geoloc = convertAddressToGeoLoc($parsedAddress);

        $geolocs['twitter_id'] = $tweetInfo['twitter_id'];
        echo "<br> TWITTER ID: ".$geolocs['twitter_id'];
        
        $geolocs['post_id']    = $tweetInfo['post_id'];
        echo "<br> POST ID: ".$geolocs['post_id'];
        
        echo "<br> POST SOURCE: ".$tweetInfo['post_source'];

        $geolocs['text']       = $tweetInfo['text'];
        echo "<br> TEXT: ".$geolocs['text'];

        $geolocs['time']       = $tweetInfo['post_time'];
        echo "<br> TIME: ".$geolocs['time'];

        $geolocs['lat']        = $geoloc['lat'];
        echo "<br> LAT: ".$geolocs['lat'];

        $geolocs['lng']        = $geoloc['lng'];
        echo "<br> LNG: ".$geolocs['lng'];

        //no need to pass the following, just echo
        echo "<br> ACC: ".$geoloc['acc'];
        echo "<br> REGEX RESULT: ";
        print_r($parsedAddress);

        //evaluate results (7 or higher acceptable parse)
        $geolocs['success'] = "false";
        
        if((int)$geoloc['acc'] >= 7)
        {
            $geolocs['success'] = "true";
            echo "<br>PARSE FULL OF WIN!";
            break;
        }
        else
        {
           echo "<br>PARSE EPIC FAIL!";
        }
    }
    return $geolocs;
}


//function takes in a tweet as an array
//extracts certain information but really only care about the text
function extractInfoFromTweet($tweet)
{
    if (empty($tweet))
    {
        return NULL;
    }

    $tweetInfo = array();

    $tweetInfo['twitter_id'] = $tweet['user']['screen_name'];
    $tweetInfo['post_id'] = $tweet['id'];
    $tweetInfo['text'] = $tweet['text'];
    $tweetInfo['post_time'] = $tweet['created_at'];
    $tweetInfo['post_source'] = $tweet['source'];

    return $tweetInfo;

}

//function takes in text
//returns array of 3 options
//address field looks for full address
//intersection field looks for intersection
//landmark field looks for a landmark
function parseAddressFromText($text)
{
    if (empty($text))
    {
        return NULL;
    }

    $options = array();

    $options['address'] = parseAddress($text);
    $options['intersection'] = parseIntersection($text);
    $options['landmark'] = parseLandmark($text);

    return $options;
}

function parseAddress($text)
{
    if (empty($text))
    {
        return NULL;
    }

    $matches = array();

    if(preg_match_all("/\d+\s(n |w |e |s |n. |w. |e. |s. |north |west |east |south )?(\w+\s){0,2}(\w+)( blvd| ave| st)?(?! today| tomorrow| for| between| betw| btwn| from| just| due| in| the| u| eat| you| at| \d+)/i",$text, $matches, PREG_PATTERN_ORDER))
    {
        return $matches;
    }
    else
    {
        return NULL;
    }
}
function parseIntersection($text)
{
    if (empty($text))
    {
        return NULL;
    }

    $matches = array();
    $symLessText = preg_replace("/(\@|\.|\!)/","",$text);
    $modText = preg_replace("/DeLacey/i","De Lacey",$symLessText);
    $newText = preg_replace("/(\s?(\+|\&|\\/|\\\\)\s?)|(\s(y|n|x|and|between)\s)/i"," & ",$modText);

    if(preg_match_all("/((\w+)( & )(\w+))|((\w+\s)?(\w+)( & )(\w+)(\s\w+)?)/i",$newText, $matches, PREG_PATTERN_ORDER))
    {
        return $matches;
    }
    else
    {
        return NULL;
    }

}

function parseLandmark($text)
{
    if (empty($text))
    {
        return NULL;
    }
    return $text;
}

function parseGeoCode($text, $userInfo, $source)
{
    if(empty ($text))
    {
        return NULL;
    }

    if(preg_match_all("/myloc\.me|(Google:(\s)?(\+|\-)?(\d){1,3}\.(\d+),(\s)?(\+|\-)?(\d){1,3}\.(\d+))/i",$text, $matches, PREG_PATTERN_ORDER))
    {
        return getLocFromUserInfo($userInfo['location']);
    }
    else
    {
        return NULL;
    }
}
?>
