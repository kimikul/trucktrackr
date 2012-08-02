<?php

require_once(realpath(dirname(__FILE__) . "/../config.php"));
require_once(LIBRARY_PATH . "/twitter.php");
require_once(LIBRARY_PATH . "/dbconnect.php");

// Given one tweet, attempt to parse the time in it. We can use the 
// post information to infer times if they're not explicitly given, 
// e.g. the keyword "now" will use the post time of the tweet as the 
// start time.
function getTimesFromUserTimeline($tweet)
{
    //function has been changed to take on a single tweet
    //tweet contains text and post time info
    $tweetTime = array();

    if (!empty($tweet))
    {
        echo '<br>Tweet Time: ' . date("n/j, l @ g:i a", $tweet['post_time']);
        echo '<br>Tweet Text: '. $tweet['text'];

        $parsedTime = parseTimeFromText($tweet['text']);

        if (!empty($parsedTime))
        {
            // If we don't have a start time but we have an end time,
            // set the start time equal to the post time
            if (empty($parsedTime['start']))
            {
                $parsedTime['start']['time'] = date("g:i", $tweet['post_time']);
                $parsedTime['start']['meridiem'] = date("a", $tweet['post_time']);
            }

            // Now that we're guaranteed to have a start time, sometimes we
            // run into the case where they have no end time set. set it
            // equal to 3 hours after the start time if it's not there
            if (empty($parsedTime['end']))
            {
                $timeArr = array();
                $timeArr = explode(":", $parsedTime['start']['time']);
                $hour = (int) $timeArr[0];
                if (($hour + 3) % 12 == 0)
                {
                    $parsedTime['end']['time'] = strval(12) . ":" . $timeArr[1];
                }
                else
                {
                    $parsedTime['end']['time'] = strval(($hour + 3) % 12) . ":" . $timeArr[1];
                }
            }

            // Whether the deduction works or not doesn't really matter since
            // we get the best possible time
            attemptMeridiemDeduction($parsedTime, $tweet['post_time']);

            print_r($parsedTime);

            $unixTimeStart = 0;
            $unixTimeEnd = 0;

            // It's kind of weird that someone would only put a start time. We'll
            // assume that they'll be there for three hours.
            if (!empty($parsedTime['start']) && empty($parsedTime['end']))
            {
                $tmpUnixTime = strtotime($parsedTime['start']['time'] . $parsedTime['start']['meridiem'], $tweet['post_time']);
                $tmpUnixTimeEnd = strtotime("+3 hours", $tmpUnixTime);
                $tmpTimeEnd = date("n/j, l @ g:i a", $tmpUnixTimeEnd);
                echo '<br />Temp: time and date from timestamp: ' . $tmpTimeEnd;

                $parsedTime['end']['time'] = date("g:i", $tmpUnixTimeEnd);
                $parsedTime['end']['meridiem'] = date("a", $tmpUnixTimeEnd);

                $unixTimeStart = strtotime($parsedTime['start']['time'] . $parsedTime['start']['meridiem'], $tweet['post_time']);
                $unixTimeEnd = $tmpUnixTimeEnd;
            }
            else
            {
                $unixTimeStart = strtotime($parsedTime['start']['time'] . $parsedTime['start']['meridiem'], $tweet['post_time']);
                $unixTimeEnd = strtotime($parsedTime['end']['time'] . $parsedTime['end']['meridiem'], $tweet['post_time']);

                // For when we cross the midnight barrier, we need to make sure to add 24 hours to the
                // end time otherwise we'll have the end time set at 12:00am Tuesday when it should be
                // 12:00am Wednesday.
                if ($parsedTime['start']['meridiem'] == "pm" && $parsedTime['end']['meridiem'] == "am")
                {
                    $unixTimeEnd = strtotime("+1 day", $unixTimeEnd);
                }
            }

            echo '<br />Start: time and date from timestamp: ' . date("n/j, l @ g:i a", $unixTimeStart);
            echo '<br />Start: timestamp: ' . $unixTimeStart;
            echo '<br />End: time and date from timestamp: ' . date("n/j, l @ g:i a", $unixTimeEnd);
            echo '<br />End: timestamp: ' . $unixTimeEnd;

            $foundValidTime = !empty($parsedTime['start']['time']) && !empty($parsedTime['start']['meridiem']) &&
                            !empty($parsedTime['end']['time']) && !empty($parsedTime['end']['meridiem']) &&
                            ($unixTimeStart < $unixTimeEnd);

            if ($foundValidTime)
            {
                echo '<br>TIME PARSE FULL OF WIN!';
                $tweetTime['success'] = "true";
                $tweetTime['time_start'] = $unixTimeStart;
                $tweetTime['time_end'] = $unixTimeEnd;
                return $tweetTime;

            }
            else
            {
                echo '<br>TIME PARSE EPIC FAIL!';
                $tweetTime['success'] = "false";
                $tweetTime['time_start'] = $tweet['post_time'];
                $tweetTime['time_end'] = $tweetTime['time_start'] + 10800; //add 3 hours
                return $tweetTime;
            }
        }
        else
        {
            echo '<br>TIME PARSE EPIC FAIL!';
            $tweetTime['success'] = "false";
            $tweetTime['time_start'] = $tweet['post_time'];
            $tweetTime['time_end'] = $tweetTime['time_start'] + 10800; //add 3 hours
            return $tweetTime;
        }

        echo '<br />';
    }

    return NULL;
}

// Update the values in trackingData
function updateTrackingData($twitter_id, $start_time, $end_time, $post_id)
{
    if (empty($twitter_id) || empty($start_time) || empty($end_time) || empty($post_id))
    {
        return FALSE;
    }

    $query = 'UPDATE trackingData SET start_time=' . $start_time .
             ', end_time=' . $end_time . ', last_post_id=' . $post_id .
             ' WHERE twitter_id="' . $twitter_id . '"';

    $res = mysql_query($query);

    if (!$res)
    {
        return FALSE;
    }

    return TRUE;
}

// Do all meridiem deductions here
function attemptMeridiemDeduction(&$parsedTime, $postTime)
{
    if (empty($parsedTime['start']) || empty($parsedTime['end']))
    {
        return FALSE;
    }

    // If we have both unset, we can figure out the first one from the
    // post time and then deduce the second meridiem from the first
    $fCanDeduceBoth = !empty($parsedTime['start']['time']) && !empty($parsedTime['end']['time']) &&
        empty($parsedTime['start']['meridiem']) && empty($parsedTime['end']['meridiem']);

    $fCanDeduceStart = !empty($parsedTime['start']['time']) && 
        empty($parsedTime['start']['meridiem']) && !empty($parsedTime['end']['meridiem']);

    $fCanDeduceEnd = !empty($parsedTime['end']['time']) && 
        !empty($parsedTime['start']['meridiem']) && empty($parsedTime['end']['meridiem']);

    if ($fCanDeduceStart || $fCanDeduceEnd || $fCanDeduceBoth)
    {
        if ($fCanDeduceBoth)
        {
            $tmpPost = array();
            $tmpPost['time'] = date("g:i", $postTime);
            $tmpPost['meridiem'] = date("a", $postTime);

            $res = deduceMeridiem($parsedTime, $tmpPost);
        }
        else
        {
            $res = deduceMeridiem($parsedTime);
        }
    }
    else
    {
        return FALSE;
    }
    /*else
    {
        // The case for if we have just one time and we need to infer from 
        // the post time
        $tmpPost = array();
        $tmpPost['time'] = date("g:i", $postTime);
        $tmpPost['meridiem'] = date("a", $postTime);

        $res = deduceMeridiem($parsedTime, $tmpPost);
    }*/

    return TRUE;
}

// Given a set of times where we have the end time's meridiem set but
// we don't have the first meridiem set, figure out the first time's
// meridiem and set it
function deduceMeridiem(&$times, $postTime = NULL)
{
    if (empty($times))
    {
        return FALSE;
    }

    $timeStart = explode(":", $times['start']['time']);
    $timeEnd = explode(":", $times['end']['time']);

    // This is the case where neither the start nor the end time have 
    // the meridiems set. We can find out the first time based on the 
    // post time of the tweet and let everything else do the rest
    if (!empty($postTime) && !empty($times['start']))
    {
        $fSame = (compareTimeDigits($postTime, $times['start']) < 0 && $timeStart[0] != "12") ||
            (compareTimeDigits($postTime, $times['start']) == 0);

        if ($fSame)
        {
            $times['start']['meridiem'] = $postTime['meridiem'];
        }
        else
        {
            if ($postTime['meridiem'] == "am")
            {
                $times['start']['meridiem'] = "pm";
            }
            else
            {
                $times['start']['meridiem'] = "am";
            }
        }
    }

    // Handle the cases for if one is set and one isn't
    $fOpposite = (compareTimeDigits($times['start'], $times['end']) > 0 && $timeStart[0] != "12") ||
        ($timeEnd[0] == "12" && $timeStart[0] != "12");

    // One is AM and one is PM
    if ($fOpposite)
    {
        if (empty($times['start']['meridiem']) && !empty($times['start']['time']))
        {
            if ($times['end']['meridiem'] == "am")
            {
                $times['start']['meridiem'] = "pm";
            }
            else
            {
                $times['start']['meridiem'] = "am";
            }
        }
        else
        {
            if (!empty($times['end']['time']))
            {
                if ($times['start']['meridiem'] == "am")
                {
                    $times['end']['meridiem'] = "pm";
                }
                else
                {
                    $times['end']['meridiem'] = "am";
                }
            }
        }
    }
    else
    {
        if (empty($times['start']['meridiem']) && !empty($times['start']['time']))
        {
            $times['start']['meridiem'] = $times['end']['meridiem'];
        }
        else
        {
            if (!empty($times['end']['time']))
            {
                $times['end']['meridiem'] = $times['start']['meridiem'];
            }
        }
    }

    return TRUE;
}


// Sort the necessary information for the above function. We don't really
// need any other info from the tweet other than the below items
function extractTimeFromTweet($tweet)
{
    if (empty($tweet))
    {
        return NULL;
    }

    $tweetTime = array();

    $tweetTime['twitter_id'] = $tweet['user']['screen_name'];
    $tweetTime['post_id'] = $tweet['id'];
    $tweetTime['post_time'] = $tweet['created_at'];
    $tweetTime['text'] = $tweet['text'];

    return $tweetTime;
}

// Input a line of text $text and attempt to parse a time range, e.g. 2-4PM
//      start: time
//             meridiem
//      end:   time
//             meridiem
// Meridiem indicates "am" or "pm".
// Times are of the format hh:mm. hh will be a 12-hour clock.
function parseTimeFromText($text)
{
    if (empty($text))
    {
        return NULL;
    }

    $parseText = strtolower($text) . " ";

    // This will probably cover 70% of the cases. Find these "between time" 
    // markers and then go left and right to find the times

    $matches = array();
    //$res = preg_match_all("/((10|11|12)|(0?\d{1}))(:?[0-5]\d)? *((a|p)\.?m?\.?)?(ish)? *(to|till|-|~) *((10|11|12)|(0?\d{1}))(:?[0-5]\d)? *((a|p)\.?m?\.?)?(ish)?(?!(\d|-))/", $parseText, $matches, PREG_PATTERN_ORDER);
    $res = preg_match_all("/((10|11|12)|(0?\d{1}))(:?[0-5]\d)? *((a|p)\.?m?\.?)?(ish)? *(to|till|-|~) *((10|11|12)|(0?\d{1}))(:?[0-5]\d)? *((a|p)\.?m?\.?)?(ish)?(?=([\p{P}\p{S}\s]))/", $parseText, $matches, PREG_PATTERN_ORDER);

    if ($res !== FALSE)
    {
        if (!empty($matches[0]))    // we have full time range matches, e.g. 12-2PM
        {
            return parseMatch($matches[0][0]);
        }
        else    // maybe it's just a singular time
        {
            return parseMatchSingular($parseText);
        }
    }

    return NULL;
}

// Helper function for determining meridiems
// Compare two digit times (purely just the numbers).
// Return FALSE on fail, otherwise
// > 0 = $time1 > $time2, e.g. 11:30, 1:30 (since 11 > 1)
// = 0 = $time1 = $time2
// < 0 = $time1 < $time2, e.g. 2:00, 4:00 (since 2 < 4)
function compareTimeDigits($time1, $time2)
{
    if (empty($time1) || empty($time2))
    {
        return FALSE;
    }

    $timePair1 = array();
    $timePair2 = array();

    $timePair1 = explode(":", $time1['time']);
    $timePair2 = explode(":", $time2['time']);

    $diff = $timePair1[0] - $timePair2[0];

    return $diff;

    /*if ($diff != 0)
    {
        return $diff;
    }
    else
    {
        return $timePair1[1] - $timePair2[1];
    }*/
}


// A second attempt at parsing times. We'll need to guess or infer if it is a 
// start time or end time, e.g., "until 2PM" implies that 2PM is the end time 
// while "at 6PM" or "ETA 6:30PM" implies a start time.
function parseTimeFromTextSingular($text)
{
    if (empty($text))
    {
        return NULL;
    }

    $parseText = strtolower($text) . " ";
    $matches = array();

    //preg_match_all("/(?<=\D)((10|11|12)|(0?\d{1}))(:?[0-5]\d)? *((a|p)\.?m?\.?)?(ish)?(?!(\d|[A-Za-z]))/", $parseText,
    preg_match_all("/(?<=\D)((10|11|12)|(0?\d{1}))(:?[0-5]\d)? *((a|p)\.?m?\.?)?(ish)?(?=([\p{P}\p{S}\s]))/", $parseText,
        $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE);

    // if we have any full matches
    if (!empty($matches[0]))
    {
        return $matches[0];
    }
    else    // we have no parseable times in this string at all
    {
        return NULL;
    }
}

// First parse the date to make it uniform, e.g "2" -> "2:00"
// Second, add the meridiems to each time whenever possible
function formatTime($timeStr, &$timeSave)
{
    if (empty($timeStr))
    {
        return FALSE;
    }

    $timeStr = trim($timeStr);

    // remove any "ish", e.g. "10:30ish"
    if (strpos($timeStr, "ish") !== FALSE)
    {
        $timeStr = substr($timeStr, 0, strpos($timeStr,  "ish"));
    }

    $res = formatTimeDigits($timeStr, $timeSave['time']);

    if (!$res)
    {
        return FALSE;
    }

    // check for PM or p, e.g. 6PM
    $pos = strpos($timeStr, "p");
    $fmeridiemSet = FALSE;

    if ($pos !== FALSE)
    {
        $timeSave['meridiem'] = "pm";
        $fmeridiemSet = TRUE;
    }

    // check for AM or a, e.g. 10:30a
    $pos = strpos($timeStr, "a");
    if ($pos !== FALSE)
    {
        $timeSave['meridiem'] = "am";
        $fmeridiemSet = TRUE;
    }

    return TRUE;
}

// Strip away any meridiems and then make the times more pretty by adding
// minutes if there are no minutes. Also, if the times don't have colons,
// add them in.
// e.g. 2    -> 2:00
//      1030 -> 10:30
function formatTimeDigits($digitStr, &$timeSave)
{
    if (empty($digitStr))
    {
        return FALSE;
    }

    // Strip away any AM/PM data for digit parsing
    if (strpos($digitStr, "p") !== FALSE)
    {
        $digitStr = substr($digitStr, 0, strpos($digitStr, "p"));
    }
    else if (strpos($digitStr, "a") !== FALSE)
    {
        $digitStr = substr($digitStr, 0, strpos($digitStr, "a"));
    }
    
    if (strpos($digitStr, ":") !== FALSE) // if there's a colon, done
    {
        $timeSave = $digitStr;
    }
    else if (strlen($digitStr) <= 2) // Times that are like: "12", "2", "11"
    {
        $timeSave = trim($digitStr) . ":00";
    }
    else // Times that have no colons, but have an hour and minute, e.g. 1030
    {
        $strLen = strlen($digitStr);
        $timeSave = substr($digitStr, 0, $strLen - 2) .
            ":" . substr($digitStr, $strLen - 2, 2);
    }

    return TRUE;
}

// For time ranges of the format 2-4PM or in general, a range that has something 
// in between, split it at that point and then parse each time
function parseMatch($matchText)
{
    if (empty($matchText))
    {
        return NULL;
    }

    // Split the two times
    $times = preg_split("/(to|till|-|~)/", $matchText);
    $timeRes = array();

    $res = formatTime($times[0], $timeRes['start']);

    if ($res)
    {
        $res = formatTime($times[1], $timeRes['end']);

        if ($res)
        {
            return $timeRes;
        }
    }

    return NULL;
}

// Given some text, try our best to find a plausible time in the string.
// Some numbers in the string are not times, e.g. "507 Westwood Ave." but have
// still been passed by the regular expressions. We can pick our best guess 
// for times, e.g. "Come get tacos at 507 Westwood at 11p" would mean that 
// the time is "11:00 pm" rather than "5:07"
function parseMatchSingular($matchText)
{
    if (empty($matchText))
    {
        return NULL;
    }

    $timeRes = array();
    $timesList = parseTimeFromTextSingular($matchText);

    //print_r($timesList);
    //echo "<br />";

    // Get the best guess for a time
    $timePickPair = deduceBestTime($timesList, $matchText);

    if ($timePickPair['context'] === TRUE)
    {
        $res = formatTime($timePickPair['time'], $timeRes['start']);

        if ($res)
        {
            return $timeRes;
        }
    }
    else
    {
        $res = formatTime($timePickPair['time'], $timeRes['end']);

        if ($res)
        {
            return $timeRes;
        }
    }

    return NULL;
}

// After getting several options for parses, pick the best one
// If there's no best one, just return the first option
//
// Arrays have the word in [0] and the offset in [1], e.g. $timesList[0][0]
// We can also use the $srcText and intelligently guess if one time is best.
// e.g. "At C.A.V.E Gallery on 507 Rose Ave in Venice until 11."
//      -- has two possible numbers: 507 and 11. Looking for "until" lends
//         more strength to "11" over "507"
function deduceBestTime($timesList, $srcText)
{
    if (empty($timesList) || empty($srcText))
    {
        return NULL;
    }

    $bestPair = array();

    // if we have just one entry, return it immediately
    if (count($timesList) == 1)
    {
        $bestPair['time'] = $timesList[0][0];
        $bestPair['context'] = getTimeContext($timesList[0][1], $srcText);

        return $bestPair;
    }

    // Search for obviously good times
    foreach ($timesList as $strPair)
    {
        $str = $strPair[0];
        $index = $strPair[1];

        // Look for dead giveaways regarding if it's a time or not
        $goodPick = strpos($str, ":") !== FALSE || strpos($str, "a") !== FALSE || strpos($str, "p") !== FALSE;

        if ($goodPick)
        {
            $bestPair['time'] = $str;
            $bestPair['context'] = getTimeContext($index, $srcText);

            return $bestPair;
        }
    }

    // If we haven't found a good time yet (pun intended), look through 
    // the tweet in the text and look for keywords like "until" or "ETA".
    // If we find those words, the odds are very good that the number 
    // is actually a time, so return it.
    foreach ($timesList as $strPair)
    {
        $str = $strPair[0];
        $index = $strPair[1];

        // We have a time parsed from the regular expression along with an 
        // index of where it was. Get the word immediately before it.
        $prevStr = getPrevWord($index, $srcText);

        if (!is_null($prevStr))
        {
            // If the word is in $endTime, it's an end time and same with $startTime
            $timeMarkers = array("until", "to", "at", "eta", "from", "starting", "til", "till");
            $decentPick = in_array($prevStr, $timeMarkers);

            if ($decentPick)
            {
                $bestPair['time'] = $str;
                $bestPair['context'] = getTimeContext($index, $srcText);

                return $bestPair;
            }
        }
    }

    // If we haven't been able to figure out which one, just return the 
    // first one and try to grab whatever context we can
    $bestPair['time'] = $timesList[0][0];
    $bestPair['context'] = getTimeContext($timesList[0][1], $srcText);

    return $bestPair;
}

// Return the word right before $index, NULL otherwise
function getPrevWord($index, $str)
{
    if ($index < 0 || empty($str))
    {
        return NULL;
    }

    $subStr = substr($str, 0, $index);

    if ($subStr !== FALSE)
    {
        $explStr = explode(" ", trim($subStr));

        if ($explStr !== FALSE)
        {
            return $explStr[count($explStr) - 1];
        }
    }

    return NULL;
}

// Go through the string and find out if it's a start time or end time.
// Default to a start time if we can't figure it out
// returns: FALSE -- end time
//          TRUE  -- start time
function getTimeContext($index, $srcText)
{
    if ($index < 0 || empty($srcText))
    {
        // kind of a bad thing to return.
        return TRUE;
    }

    $prevStr = getPrevWord($index, $srcText);

    if (!is_null($prevStr))
    {
        // if the word is in $endTime, it's an end time and same with $startTime
        $endTime = array("until", "to", "close", "til", "'til", "till");
        $startTime = array("eta", "from", "by");

        if (in_array($prevStr, $endTime))
        {
            return FALSE;
        }

        if (in_array($prevStr, $startTime))
        {
            return TRUE;
        }
    }

    // now we get desperate. we couldn't find the right prefix words so do
    // some very rough searching
    $endTimeAlts = array("until", "close", "closing", "til", "'til", "till", "leave", "leaving", "done");
    $isEndRes = array_intersect($endTimeAlts, explode(" ", $srcText));

    if (!empty($isEndRes))
    {
        return FALSE;
    }

    return TRUE;
}

?>
