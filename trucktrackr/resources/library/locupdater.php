<?php

    /*
       LocUpdater is a class that updates the database with new location information 
       from the Twitter accounts of various food trucks.
    */

    require_once(realpath(dirname(__FILE__) . "/../config.php"));
    require_once(LIBRARY_PATH . "/dbconnect.php");
    require_once(LIBRARY_PATH . "/twitter.php");
    require_once(LIBRARY_PATH . "/locparse.php");

    // Get the list of trucks from the database and compare the location
    // in the database to the one we get from Twitter. If they're different,
    // send an UPDATE query to the database to refresh the new location
    function updateLocs()
    {
        // test database stuff
        $query = 'SELECT twitter_id, lat, longit FROM ' . $dbInfo['trackingInfo'];
        $selectRes = mysql_query($query);

        if ($selectRes)
        {
            while ($row = mysql_fetch_assoc($selectRes))
            {
                // Grab the user information which contains a location
                $twitterUser = new Twitter("seunghyochoi","cs130pw");
                $truckID = $row['twitter_id'];
                $userInfo = $twitterUser->getUser($truckID);

                // Parse the geocode from the user's information
                $locPair = getLocFromUserInfo($userInfo['location']);

                if (is_null($locPair))
                {
                    // We couldn't get any location information
                    // We'll need to defer location gathering from parsing
                    return FALSE;
                }
                else    // Got a location from their user info; Update the DB with the new info
                {
                    $isNewLoc = $row['lat'] != $locPair['lat'] || $row['longit'] != $locPair['longit'];

                    if ($isNewLoc)
                    {
                        $updateRes = updateLocation($truckID, $locPair);

                        if (!$updateRes)
                        {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    // send an UPDATE query to the database with the new location
    function updateLocation($user, $locPair)
    {
        if (!is_numeric($locPair['lat']) || !is_numeric($locPair['longit']) || is_null($user))
        {
            return false;
        }

        $query = 'UPDATE ' . $dbInfo['trackingInfo'] . ' SET lat="' . $locPair['lat'] .
                 '", longit="' . $locPair['longit'] . '" WHERE twitter_id="' . $user . '"';
        $res = mysql_query($query);

        if (!$res)
        {
            return false;
        }

        return true;
    }
?>
