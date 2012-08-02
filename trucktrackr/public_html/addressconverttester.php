<?php
require_once(realpath(dirname(__FILE__) . "/../resources/config.php"));
require_once(LIBRARY_PATH . "/addressparse.php");
require_once(LIBRARY_PATH . "/addresstolatlon.php");

//testing w/ one food truck, later run with all food trucks
$testlist = getAddressFromUserTimeline("kogibbq");
$newList = convertAddressToGeoLoc($testlist);
?>
