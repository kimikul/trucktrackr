<?php

    require_once(realpath(dirname(__FILE__) . "/../resources/config.php"));
    require_once(LIBRARY_PATH . "/timeparse.php");


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
        echo "Line: " . $values[$count] . "<br />";

        $res = parseTimeFromText($values[$count]);

        echo "Result: ";

        if(is_null($res))
            echo "nothing!";
        else
        {
            print_r($res);
            $numValidParses++;
        }

        echo "<br />";
        echo "<br />";
    }

    echo 'Found ' . $numValidParses . '/' . $numValues . ' valid parses.';

?>
