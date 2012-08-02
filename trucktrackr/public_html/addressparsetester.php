<?php

    require_once(realpath(dirname(__FILE__) . "/../resources/config.php"));

    require_once(LIBRARY_PATH . "/addressparse.php");


    $fp = fopen("timeparsetests.txt", "rb") or die("Couldn't open file");
    $data = fread($fp, filesize($fp));

    while(!feof($fp))
    {
        $data .= fgets($fp, 1024);
    }

    fclose($fp);

    $values = explode("\n", $data);
    $numValues = count($values);
    $numValidParses = 0;

    for ($count = 0; $count < $numValues; $count++)
    {
        echo "Line: " . $values[$count] . "<br>";

        $addressResult = parseAddressFromText($values[$count]);

        echo "Address: ";

        if(is_null($addressResult))
            echo "nothing!";
        else
        {
            print_r($addressResult['address'][0]);
            echo "<br> Intersection: ";
            print_r($addressResult['intersection'][0]);
            echo "<br> GeoCode: ";
            print_r($addressResult['geocode']);
        }
       
        echo "<br />";
        echo "<br />";

    }

    //echo 'Found ' . $numValidParses . '/' . $numValues . ' valid parses.';

?>

