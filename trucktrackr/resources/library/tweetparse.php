

<?php

require_once(realpath(dirname(__FILE__) . "/../config.php"));
require_once(LIBRARY_PATH . "/dbconnect.php");
require_once(LIBRARY_PATH . "/addressparse.php");
require_once(LIBRARY_PATH . "/timeparse.php");

//function grabs all twitter ids
//parses current location and time of twitter id
//populates database with location
function dateAndTimeUpdate()
{
    //grabs list of twitter ids from database
    $query = 'SELECT twitter_id FROM truckInfo';
    $sqlResult = mysql_query($query) or die(mysql_error());

    //for every single twitter id...
    while($row = mysql_fetch_array($sqlResult))
    {
        $id = $row['twitter_id'];

        //look for first successful location in timeline
        //function returns array of...
        //twitter_id,post_id,text,time,lat,lng,success;
        $locs = getAddressFromUserTimeline($id);
        $timeParameters = array();
        $times = array();
        $replace = array();
        $replace[0] = "'";
        $replace[1] = '"';

        if($locs['success'] == "true")
        {
            //continue
            $timeParameters['twitter_id'] = $locs['twitter_id'];
            $timeParameters['post_id'] = $locs['post_id'];
            $timeParameters['post_time'] =$locs['time'];
            $timeParameters['text']= $locs['text'];
            $times  = getTimesFromUserTimeline($timeParameters);

            //there is always a time, no matter what
            //but if it's old, clear the post
            if((int)$locs['time'] < (time()-86400))
            {
                $locs['lat'] = '0';
                $locs['lng'] = '0';
                echo "<br> TRUCK CLOSED!";
            }
            else if(preg_match_all("/(cancel|canceled|error|sorry)/i",$locs['text'], $matches, PREG_PATTERN_ORDER))
            {
                $locs['lat'] = '0';
                $locs['lng'] = '0';
                echo "<br> TRUCK CANCELED!";
            }//look for cancells and errors and thanks and websites

            //form query
            $query = "UPDATE trackingData SET lat='".$locs['lat'].
            "', longit='".$locs['lng'].
            "', time_start=".$times['time_start'].
            ", time_end=".$times['time_end'].
            ", last_post_id=".$locs['post_id'].
            ", text='".str_replace($replace, "", $locs['text']).
            "' WHERE twitter_id='".$id."'";
            $res = mysql_query($query);

            if ($res)
            {
                echo "<br>UPDATE SUCCESS!! ID: ".$id.
                     "<br>START: ".$times['time_start'].
                     "<br>END: ".$times['time_end'].
                     "<br>LAST POST: ".$locs['post_id'].
                     "<br>QUERY: ".$query."<br>";
            }
            else
            {
                echo "<br>UPDATE FAILED!<br>".mysql_error();

            }
        }//end if
    }//end while loop
}//end function

// returns a list of twitter_id's of trucks that are currently open,
// where "open" is just the time this function is called along with
// their start_time, end_time, latitude, longitude, and truck_names
function getOpenTrucks($userTimeRequest = NULL)
{
    $openTrucks = array();
    $index = 0;
    $timeNow = 0;

    if (empty($userTimeRequest))
    {
        $timeNow = time();
    }
    else
    {
        $timeNow = $userTimeRequest;
    }

    if ($timeNow > 0)
    {
        // time_start <= $userTime <= time_end
        $query = 'SELECT twitter_id, time_start, time_end FROM trackingData WHERE time_start <= ' .
                 $timeNow . ' AND time_end >= ' . $timeNow;

        $selectRes = mysql_query($query);

        if ($selectRes)
        {
            while ($row = mysql_fetch_assoc($selectRes))
            {
                $openTrucks[$index] = $row;
                $index++;
            }

            return $openTrucks;
        }
    }

    return NULL;
}
?>
