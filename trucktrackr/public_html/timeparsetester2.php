<?php

    require_once(realpath(dirname(__FILE__) . "/../resources/config.php"));
    require_once(LIBRARY_PATH . "/timeparse.php");
    require_once(LIBRARY_PATH . "/twitter.php");

    getTimesFromUserTimeline("bullkogi");
?>
