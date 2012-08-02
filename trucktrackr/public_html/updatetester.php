<?php

    require_once(realpath(dirname(__FILE__) . "/../resources/config.php"));
    require_once(LIBRARY_PATH . "/locupdater.php");
    require_once(LIBRARY_PATH . "/dbconnect.php");

    $query = 'SELECT twitter_id, lat, longit FROM ' . $dbInfo['trackingData'];
    $selectRes = mysql_query($query);
    echo "Location and time data:<br />";

    print_rows($selectRes);
    
    $res = updateLocs();

    if ($res === TRUE)
    {
        echo "Update succeeded";
    }
    else
    {
        echo "Update failed";
    }

    echo "<br />";
    echo "<br />";
    echo "Truck info (static data):<br />";

    $query = 'SELECT * FROM ' . $dbInfo['truckInfo'];
    $selectRes = mysql_query($query);
    print_rows($selectRes);

    echo "<br />";
    echo "<br />";
    echo "Food types:<br />";

    $query = 'SELECT * FROM ' . $dbInfo['foodTypes'];
    $selectRes = mysql_query($query);
    print_rows($selectRes);


    function print_rows($sqlResult)
    {
        if (empty($sqlResult))
        {
            return FALSE;
        }

        while ($row = mysql_fetch_assoc($sqlResult))
        {
            print_r($row);
            echo '<br />';
        }

        return TRUE;
    }

?>
