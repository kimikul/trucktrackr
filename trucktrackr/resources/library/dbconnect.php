<?php
    /* 
       connect to the server at the website and select a database
       selected with variables from the config.php file.
    */
    require_once(realpath(dirname(__FILE__) . "/../config.php"));

    $dbConnection = mysql_connect($dbInfo['host'],
            $dbInfo['username'],
            $dbInfo['password']);

    if (!$dbConnection)
    {   
        die('Could not connect: ' . mysql_error());
    }   

    mysql_select_db($dbInfo['dbName']) or die(mysql_error());
?>
