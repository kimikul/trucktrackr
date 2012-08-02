#!/usr/bin/php

<?php

    echo "Cron script started.";
    /*
       LocUpdater is a class that updates the database with new location information
       from the Twitter accounts of various food trucks.
    */

    $MINUTE = 60;
    $multiplier = 10;
    set_time_limit($multiplier*$MINUTE);
    echo "PHP time limit lengthened to ".$multiplier." minutes.";

    require_once(realpath(dirname(__FILE__) . "/../config.php"));
    require_once(LIBRARY_PATH . "/tweetparse.php");

    dateAndTimeUpdate();

    echo "Cron script ended.";

?>
