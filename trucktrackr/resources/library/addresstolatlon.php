<?php

define("MAPS_HOST", "maps.google.com");
define("KEY", "ABQIAAAA-FoXF5cHF-7H0Cg8xo5lExSBlpvpCsdFVgUHfx1kaSZ-erq0ZRRydK8D3v4o3XHafdaJc3eZl0z-0w");

//function takes in a list of options
//address - options for full addresses
//intersection - options for intersections
//TODO: landmark support
//function returns single lat/lon
require_once(LIBRARY_PATH . "/dbconnect.php");
require_once(realpath(dirname(__FILE__) . "/../config.php"));

function convertAddressToGeoLoc($options)
{   

    $geoloc = array();
    $streetOptions = $options['address'][0];
    $xOptions = $options['intersection'][0];
    $tweet = $options['landmark'];
    $codes = $options['geocode'];

    //first check for geocode success
    if(!empty($codes['lat']) && !empty($codes['longit']))
    {
        $geoloc['lat'] = $codes['lat'];
        $geoloc['lng'] = $codes['longit'];
        $geoloc['acc'] = 10;
        return $geoloc;
    }

    $geoloc['lat'] = 0;
    $geoloc['lng'] = 0;
    $geoloc['acc'] = 0;

    //fetch all landmark options
    $query = "SELECT landmark, lat, longit FROM landmarks";
    $sqlResult = mysql_query($query) or die(mysql_error());
    $base_url = "http://" . MAPS_HOST . "/maps/geo?output=csv" . "&key=" . KEY;


    $delay = 0; //in case geocode requests are too fast
    //test each intersection option for geoloc
    if(!empty($xOptions))
    {
        foreach($xOptions as $intersection)
        {
            //formulate street address and extract results
            $address = $intersection.", Los Angeles, CA";
            $request_url = $base_url . "&q=" . urlencode($address);
            $csv = file_get_contents($request_url);
            $result = explode(',',$csv);
            $status = $result[0];
            $accuracy = $result[1];
            $lat = $result[2];
            $lng = $result[3];
            $geoloc['acc'] = $accuracy;
            //if api was successful
            if($status == "200")
            {
                //if result was address level accuracy
                if($accuracy == "7")
                {
                    $geoloc['lat'] = $lat;
                    $geoloc['lng'] = $lng;
                    return $geoloc;
                }
            }
            else if($status == "620")
            {
                //going too fast!
                $delay+=100000;
            }
            usleep($delay);
        }
    }

    $delay = 0;
    //test each address option for geoloc
    if(!empty($streetOptions))
    {
        foreach($streetOptions as $street)
        {
            //formulate street address and extract results
            //TODO: LA is default, parse db of other cities as well
            $address = $street.", Los Angeles, CA";
            $request_url = $base_url . "&q=" . urlencode($address);
            $csv = file_get_contents($request_url);
            $result = explode(',',$csv);
            $status = $result[0];
            $accuracy = $result[1];
            $lat = $result[2];
            $lng = $result[3];
            $geoloc['acc'] = $accuracy;
            //if api was successful
            if($status == "200")
            {
                //if result was address level accuracy
                if($accuracy == "8")
                {
                    $geoloc['lat'] = $lat;
                    $geoloc['lng'] = $lng;            
                    return $geoloc;
                }
            }
            else if($status == "620")
            {
                //going too fast!
                $delay+=100000;
            }
            usleep($delay);
        }
    }

    //look for landmark in tweet
    while($row = mysql_fetch_array($sqlResult))
    {
        if(stristr($tweet, $row[0]))
        {
            $geoloc['lat'] = $row[1];
            $geoloc['lng'] = $row[2];
            $geoloc['acc'] = "9";
        }
    }

    if($geoloc['lat'] == $geoloc['lng'])
    {
        //something is wrong, they're probably both zero
        $geoloc['acc'] = 0; //clearly inaccurate
    }
    //if still here then return no results...
    return $geoloc;
}
?>
