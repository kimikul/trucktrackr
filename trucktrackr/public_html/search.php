<?php

require_once("../resources/config.php");
require_once(LIBRARY_PATH . "/dbconnect.php");

function truckSearch($userInput)
{
    $cleanInput = sanitizeUserInput($userInput);

    if (!$cleanInput)
    {
        return NULL;
    }

    $truckSearchQuery = 'SELECT distinct(twitter_id), lat, longit, FROM truckInfo NATURAL JOIN trackingData WHERE twitter_id LIKE \'%' . $cleanInput
                        . '%\' OR business_name LIKE \'%' . $cleanInput . '%\'';

    $truckSearchRes = mysql_query($truckSearchQuery);

    if(!$truckSearchRes)
    {
        return NULL;
    }

    $numResults = mysql_num_rows($truckSearchRes);

    if ($numResults <= 0)
    {
        return NULL;
    }

    $twitterIDResults = array();

    for($i = 0; $i < $numResults; $i++)
    {
        $row = mysql_fetch_assoc($truckSearchRes);
        $twitterIDResults[$i]['twitter_id'] = $row['twitter_id'];
        $twitterIDResults[$i]['lat'] = $row['lat'];
        $twitterIDResults[$i]['longit'] = $row['longit'];
    }

    return $twitterIDResults;
}

function sanitizeUserInput($userInput)
{
    // running SELECT max(length(business_name))
    // FROM truckInfo = 35
    if(strlen($userInput) > 35 || empty($userInput))
    {
        return FALSE;
    }

    $cleanInput = str_replace("'", "", $userInput);

    if(!ctype_alnum($cleanInput))
    {
        return FALSE;
    }

    return mysql_real_escape_string($cleanInput);
}

?>
