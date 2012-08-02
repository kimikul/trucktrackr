<?php session_start(); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<head>
    <title>trucktrackr.com | find local food trucks!</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <link rel="icon" type="image/ico" href="images/tt_favicon.ico" />
    <link rel="stylesheet" media="screen" href="style.css" type="text/css" />
    <link rel="stylesheet" media="screen" href="jquery.ptTimeSelect.css" type="text/css" />

    <script type="text/javascript" src="jquery.js"></script> 
    <script type="text/javascript" src="jquery.tablesorter.js"></script>
    <script type="text/javascript" src="jquery.ptTimeSelect.js"></script>
    <script type="text/javascript" src="jquery.cust.js"></script>
    <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;sensor=true&amp;key=ABQIAAAA-FoXF5cHF-7H0Cg8xo5lExSBlpvpCsdFVgUHfx1kaSZ-erq0ZRRydK8D3v4o3XHafdaJc3eZl0z-0w" type="text/javascript"></script>

    <?php
        require_once("../resources/config.php");
        require_once(LIBRARY_PATH . "/locupdater.php");
        require_once(LIBRARY_PATH . "/locparse.php");
        require_once(LIBRARY_PATH . "/timeparse.php");
        require_once(LIBRARY_PATH . "/dbconnect.php");
        require_once("search.php");

        $lat = 34;
        $lng = -118;
        $_SESSION['address']=NULL;
    ?>

</head>
<body onload="initialize()" onunload="GUnload()">
<div class="wrapper">
<div class="container">

<?php

$userInput = $_GET['address'];

if(isset($userInput))
{
    $truckSearchRes = truckSearch($userInput);
    $fInputIsTruck = !empty($truckSearchRes);
}

?>

    <form id="search" action="<?php echo $_SERVER['PHP_SELF'];?>" method="get" onsubmit="<?php $_SESSION['address']=$_GET['address'];?>">

    <div id="search_box">
        <?php include 'rounded_start.php'; ?>

        <div id="search_box_content">
            <input id="address_input" name="address" size="60" type="text" <?php if(isset($_GET['address'])) echo "value=\"".$_GET['address']."\"" ?>/>
            <input id="submit" name="submit" type="submit" tabindex="2" value="Go!" />
        </div>

        <?php include 'rounded_end.php'; ?>
    </div>

    <div id="logo_box">
        <img src="images/logo_small.png" width="205" height="38" alt="truck illustration" />
    </div>

    <div id="DiDyAmEaN"></div>

    <div id="mapAndFilters">
        <div id="map_results">
            <?php include 'rounded_start.php'; ?>

            <div id="map_container">
                <div id="map"></div>
            </div>

            <?php include 'rounded_end.php'; ?>
        </div>
    </div>
    <div id="showHideFilters">
        <a class="showHideFiltersLink">show/hide filters</a>
    </div>

    <div id="filters">
        <?php include 'rounded_start.php'; ?>

        <div id="filters_content">
            <!--<input name="type0" type="checkbox" <?php if(isset($_GET["type0"])) echo 'checked=true' ?> onclick="checkAllTypes()" value="All" />All<br/>-->

        <div id="foodTypeFilters">
<?php
    $foodTypes = array("American", "Barbecue", "Burgers", "Chinese", "Dessert", "Fusion", "Hot Dogs", 
                       "Indian", "Japanese", "Korean", "Mediterranean", "Mexican", "Pizza", "Sandwiches", 
                       "Thai", "Vegetarian", "Vietnamese");

    $optionsPerBox = 6;
    $fNeedClosingDiv = false;

    for ($i = 0; $i < count($foodTypes); $i++)
    {
        if($i % 6 == 0)
        {
            echo '<div class="foodTypeFilters">';
            $fNeedClosingDiv = !$fNeedClosingDiv;
        }

        echo '            <input name="type' . ($i + 1) . '" type="checkbox" '; 
        if(isset($_GET["type".(string)($i+1)]))
        {
            echo 'checked=true ';
        }
        echo 'value="' . $foodTypes[$i] . '" />' . $foodTypes[$i] . '<br/>' . "\n";

        if(($i+1) % 6 == 0)
        {
            echo '</div>';
            $fNeedClosingDiv = !$fNeedClosingDiv;
        }
    }

    if ($fNeedClosingDiv)
    {
        echo '</div>';
    }

?>

            </div>

        <div id="costFilters">
            <input name="cost1" type="checkbox" <?php if(isset($_GET["cost1"])) echo 'checked="checked"' ?> value="1" />$0-$5<br/>
            <input name="cost2" type="checkbox" <?php if(isset($_GET["cost2"])) echo 'checked="checked"' ?> value="2" />$5-$10<br/>
            <input name="cost3" type="checkbox" <?php if(isset($_GET["cost3"])) echo 'checked="checked"' ?> value="3" />$10+<br/>
        </div>

        <div id="resultsFilters">

            Search Within
            <select name="radius">
            <option value="5" <?php if(isset($_GET["radius"])) if($_GET["radius"]=="5") echo 'selected="selected"';?>>5</option>
                <option value="10" <?php if(isset($_GET["radius"])) if($_GET["radius"]=="10") echo 'selected="selected"';?>>10</option>
                <option value="20" <?php if(isset($_GET["radius"])) if($_GET["radius"]=="20") echo 'selected="selected"';?>>20</option>
                <option value="50" <?php if(isset($_GET["radius"])) if($_GET["radius"]=="50") echo 'selected="selected"';?>>50</option>

            </select> Miles<br/><br/>
            Show
            <select name="numResults">
                <option value="5" <?php if(isset($_GET["numResults"])) if($_GET["numResults"]=="5") echo 'selected="selected"';?>>5</option>
                <option value="10" <?php if(isset($_GET["numResults"])) if($_GET["numResults"]=="10") echo 'selected="selected"';?>>10</option>
                <option value="20" <?php if(isset($_GET["numResults"])) if($_GET["numResults"]=="20") echo 'selected="selected"';?>>20</option>
                <option value="50" <?php if(isset($_GET["numResults"])) if($_GET["numResults"]=="50") echo 'selected="selected"';?>>50</option>
            </select> Trucks<br/><br/>

            <div id="time">
                <input name="time_selector" style="width:60px" <?php if(isset($_GET["time_selector"])) echo "value=\"".$_GET["time_selector"]."\"";?>/>
            </div>

            <div id="update_area"><input id="update_button" name="submit" type="submit" tabindex="2" value="Update!" /></div>
        </div>

    </div>

        <?php include 'rounded_end.php'; ?>
        <?php

            // Use Google geocoding services to translate the user's input address into coordinates
            define("MAPS_HOST", "maps.google.com");
            define("KEY", "ABQIAAAA-FoXF5cHF-7H0Cg8xo5lExSBlpvpCsdFVgUHfx1kaSZ-erq0ZRRydK8D3v4o3XHafdaJc3eZl0z-0w");
            $base_url = "http://" . MAPS_HOST . "/maps/geo?output=xml" . "&key=" . KEY;

            define("TAB", "    ");
            define("NL", "\n");
            $notEmpty = false;
            if(isset($_GET['submit'])) {
                $address = $_GET['address'];
                $request_url = $base_url . "&q=" . urlencode($address);
                $xml = simplexml_load_file($request_url) or die("url not loading");
                $status = $xml->Response->Status->code;

                // food type filters
                // Run through the food types and create/modify the WHERE condition
                // of the SQL query
                $where_query = "";
                $filters_on = false;
                $desiredRadius = 50;
                $desiredNumResults = 5;
                for($i=1; $i<18; $i++) {
                    $type_string = "type".$i;
                    if(isset($_GET[$type_string])) {
                        if($filters_on === false) {
                            $where_query .= " WHERE food_type=";
                            $filters_on = true;
                            $where_query .= "\"".$_GET[$type_string]."\"";
                        } else {
                            $where_query .= " OR food_type=\"".$_GET[$type_string]."\"";
                        }
                    }
                }

                // price filters
                // Run through the prices and create/modify the WHERE condition
                // of the SQL query
                $price_query = "";
                $pFilter_on = false;
                for($i=1; $i<=3; $i++) {
                    $price_string = "cost".$i;
                    if(isset($_GET[$price_string])) {
                        if ($pFilter_on === false){
                            $pFilter_on = true;
                            $price_query .= "cost=".$_GET[$price_string];
                        }
                        else {
                            $price_query .= " OR cost=".$_GET[$price_string];
                        }
                    }
                }
                if ($filters_on && $pFilter_on) {
                    $where_query .= " AND (" . $price_query. ")";
                }
                else {
                    if ($pFilter_on === true) {
                        $where_query .= " WHERE " . $price_query;
                        $filters_on = true;
                    }
                }

                // time filter
                // Check the time of filters and create/modify the WHERE condition
                // of the SQL Query
                $now = time();
                if (isset($_GET["time_selector"])  && $_GET["time_selector"] != "") {
                    $desiredTime = $_GET['time_selector'];
                    $unixTime = strtotime($desiredTime,$now);
                    //echo date("n/j, l @ g:i a", $unixTime);

                    $time_query = " time_start <= " .$unixTime. " AND time_end >= ". $unixTime;
                    if($filters_on) {
                        $where_query .= " AND " .$time_query;
                    } else {
                        $where_query .= " WHERE " . $time_query;
                    }
                }

                if($filters_on){
                    $where_query .= " AND last_post_id > 0 ";
                }
                else {
                    $where_query .= " WHERE last_post_id > 0 ";
                    $filters_on = true;
                }

                if (isset($fInputIsTruck) && $fInputIsTruck)
                {
                    if($filters_on){
                        $where_query .= " AND (";
                    }
                    else {
                        $where_query .= " WHERE (";
                    }

                    for ($i = 0; $i < count($truckSearchRes); $i++)
                    {
                        $where_query .= 'twitter_id = "' . $truckSearchRes[$i] . '" ';

                        if ($i < (count($truckSearchRes) - 1))
                        {
                            $where_query .= 'OR ';
                        }
                    }

                    $where_query .= ")";
                }

                if(isset($_GET["radius"]))
                    $desiredRadius = $_GET["radius"];

                if(isset($_GET["numResults"]))
                    $desiredNumResults = $_GET["numResults"];
                

                if(strcmp($status, "200") == 0) {
                    $coordinates = $xml->Response->Placemark->Point->coordinates;
                    $coordinatesSplit = explode(",", $coordinates);
                    // Format: Longitude, Latitude, Altitude
                    $lat = $coordinatesSplit[1];
                    $lng = $coordinatesSplit[0];

                    if ($fInputIsTruck)
                    {
                        $radius_query = "SELECT food_tag,".$dbInfo['foodTypes'].".twitter_id,business_name,lat,longit,time_start,time_end,last_post_id,website_url,menu_url,cost";
                    }
                    else
                    {
                        $radius_query = "SELECT food_tag,".$dbInfo['foodTypes'].
                            ".twitter_id,business_name,lat,longit,time_start,time_end,last_post_id,website_url,menu_url,cost, (3959 * acos(cos(radians(".
                            $lat."))*cos(radians(lat))*cos(radians(longit)-radians(".$lng."))+sin(radians(".$lat."))*sin(radians(lat)))) AS distance";
                    }

                    $radius_query .= " FROM " . $dbInfo['foodTypes'] .
                                    " LEFT JOIN ".$dbInfo['truckInfo']." using(twitter_id) " . 
                                    " LEFT JOIN ".$dbInfo['trackingData']." using(twitter_id)";

                    $radius_query .= $where_query;

                    if($fInputIsTruck)
                    {
                        $radius_query .=  " GROUP BY ".$dbInfo['foodTypes'].".twitter_id".
                                          " LIMIT 0 , " . $desiredNumResults . ";";
                    }
                    else
                    {
                        $radius_query .=  " GROUP BY ".$dbInfo['foodTypes'].".twitter_id HAVING distance<".$desiredRadius.
                                          " ORDER BY distance".
                                          " LIMIT 0 , " . $desiredNumResults . ";";
                    }

                    echo $radius_query;

                    $result = mysql_query($radius_query);

                    echo NL."<script type=\"text/javascript\">".NL;
                    echo "var geo = new GClientGeocoder(); ".NL;
                    echo "var address = \"".$_SESSION['address']."\";".NL;
                    echo "function initialize() {".NL;
                    echo "geo.getLocations(address, function (result)".NL;
                    echo "{".NL;
                    echo TAB."var showMap = true;".NL;
                    echo TAB."if (result.Status.code == G_GEO_SUCCESS) {".NL;
                    //If there is more than one result, then give the user a list of options
                    echo TAB.TAB."if (result.Placemark.length > 1) { ".NL;
                    echo TAB.TAB.TAB."showMap = false;".NL;
                    echo TAB.TAB.TAB.TAB."document.getElementById(\"map\").innerHTML = \"Did you mean:\";".NL;
                    echo TAB.TAB.TAB.TAB."for (var i=0; i<result.Placemark.length; i++) {".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var p = result.Placemark[i].Point.coordinates;".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var currAddress = result.Placemark[i].address;".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var currAddress = currAddress.replace(/ /g,'_');".NL;
                    echo TAB.TAB.TAB.TAB.TAB."document.getElementById(\"map\").innerHTML += \"<br>\"+(i+1)+\": <a href=\\\"javascript:useAddress(\\\"\"+currAddress+\"\\\")>\"+result.Placemark[i].address+\"</a>\";".NL;
                    echo TAB.TAB.TAB.TAB."}".NL;
                    echo TAB.TAB."}".NL;

                    //If there is only one result then print out the map
                    echo TAB.TAB. "else {".NL;
                     //-----------------Javascript Map------------------
                    echo TAB.TAB.TAB.TAB."if (GBrowserIsCompatible() && showMap) {".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var map = new GMap2(document.getElementById(\"map\"));".NL;
                    echo TAB.TAB.TAB.TAB.TAB."map.setCenter(new GLatLng(".$lat.", ".$lng."), 13);".NL;
                    echo TAB.TAB.TAB.TAB.TAB."map.setUIToDefault();".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var bounds = new GLatLngBounds;".NL;

                    $count = 1;
                    // create markers and info windows for each result and the address entered
                    echo TAB.TAB.TAB.TAB.TAB."var homeIcon = new GIcon(G_DEFAULT_ICON);".NL;
                    echo TAB.TAB.TAB.TAB.TAB."homeIcon.image=\"images/pins/casetta_blu.png\";".NL;
                    echo TAB.TAB.TAB.TAB.TAB."homeIcon.iconSize= new GSize(32,32);".NL;
                    echo TAB.TAB.TAB.TAB.TAB."homeIcon.shadow= \"images/pins/icon10s.png\";".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var markerOptions_home = {icon:homeIcon};".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var point = new GLatLng({$lat},{$lng});".NL;
                    echo TAB.TAB.TAB.TAB.TAB."var marker_home= new GMarker(point,markerOptions_home);".NL;
                    echo TAB.TAB.TAB.TAB.TAB."map.addOverlay(marker_home);".NL;
                    echo TAB.TAB.TAB.TAB.TAB."bounds.extend(point);".NL;

                    if(mysql_num_rows($result) > 0) {
                        while($row = mysql_fetch_assoc($result)) {
                            $notEmpty = true;
                            $cost = $row['cost'];
                            $costSymbol = "";
                            for ($index = 0; $index < $cost; $index++) {
                                $costSymbol .= "$";
                            }
                            $reverseURL = "http://maps.google.com/maps/geo?";
                            $reverseURL .= http_build_query(array(
                                "output" => "xml",
                                "key" => KEY,
                                "q" => "{$row['lat']},{$row['longit']}"
                            ));
                            $reverse_xml = simplexml_load_file($reverseURL) or die("url not loading");
                            $countryXML = $reverse_xml->Response->Placemark->AddressDetails->Country;
                            $streetName = $countryXML->AdministrativeArea->SubAdministrativeArea->Locality->Thoroughfare->ThoroughfareName;
                            $cityName = $countryXML->AdministrativeArea->SubAdministrativeArea->Locality->LocalityName;
                            $stateName = $countryXML->AdministrativeArea->AdministrativeAreaName;
                            $countryName = $countryXML->CountryNameCode;
                            $zipcode = $countryXML->AdministrativeArea->SubAdministrativeArea->Locality->PostalCode->PostalCodeNumber;

                            $truckStatus = "open";
                            if($row['time_end'] < $now)
                                $truckStatus = "close";
                            else if($row['time_start'] > $now)
                                $truckStatus = "later";
                            echo TAB.TAB.TAB.TAB.TAB."var Icon".$count." = new GIcon(G_DEFAULT_ICON);".NL;
                            echo TAB.TAB.TAB.TAB.TAB."Icon".$count.".image=\"images/pins/".$truckStatus."_".$row['twitter_id'].".png\";".NL;
                            echo TAB.TAB.TAB.TAB.TAB."Icon".$count.".iconSize= new GSize(25,30);".NL;
                            echo TAB.TAB.TAB.TAB.TAB."Icon".$count.".shadowSize= new GSize(40,30);".NL;
                            echo TAB.TAB.TAB.TAB.TAB."Icon".$count.".shadow= \"images/pins/pin_shadow.png\";".NL;
                            echo TAB.TAB.TAB.TAB.TAB."var markerOptions".$count." = {icon:Icon".$count."};".NL;
                            echo TAB.TAB.TAB.TAB.TAB."var point = new GLatLng({$row['lat']},{$row['longit']});".NL;
                            echo TAB.TAB.TAB.TAB.TAB."var marker".$count."= new GMarker(point,markerOptions".$count.");".NL;
                            echo TAB.TAB.TAB.TAB.TAB."map.addOverlay(marker".$count.");".NL;
                            $genContent = "<div style=\"width: 250px;\"><div style=\"width: 50px; float:left\"><img src=\"images/truck_logos/{$row['twitter_id']}.jpg\" alt=\"{$row['business_name']}\" /></div><div style=\"width: 200px; float:right\"><b>{$row['business_name']}</b><br/>{$row['food_tag']}<br/>{$costSymbol}<br/><br/><br/><a href=\"http://www.twitter.com/{$row['twitter_id']}\" target=\"_blank\"><img src=\"images/twitter_icon.png\" border=\"0\" width=\"10\" height=\"13\" alt=\"twitter icon\" /></a> <a href=\"{$row['website_url']}\" target=\"_blank\">Website</a> <a href=\"{$row['menu_url']}\" target=\"_blank\">Menu</a></div>";
                            $locContent='<b>Address</b>';
                            echo TAB.TAB.TAB.TAB.TAB."var tab1 = new GInfoWindowTab(\"General Info\",'{$genContent}');".NL;
                            $locContentAddress="{$streetName}<br/>{$cityName}, {$stateName} {$zipcode}, {$countryName}";
                            if(!isset($streetName)) {
                                $landmark_query = "select landmark from landmarks where lat={$row['lat']} and longit={$row['longit']}";
                                $landmark_result = mysql_query($landmark_query);
                                $landmark_row = mysql_fetch_assoc($landmark_result);
                                $locContentAddress = "{$landmark_row['landmark']}<br/>{$cityName}, {$stateName} {$zipcode}, {$countryName}";
                            }
                            $startTime=date('g:i A',$row['time_start']);
                            $endTime=date('g:i A',$row['time_end']);
                            $locContentTime="{$startTime} to {$endTime}";
                            echo TAB.TAB.TAB.TAB.TAB."var tab2 = new GInfoWindowTab(\"Location\",'{$locContent}<br/>{$locContentAddress}<br/><br/>{$locContentTime}');".NL;
                            echo TAB.TAB.TAB.TAB.TAB."var tabArray".$count." = [tab1, tab2];".NL;
                            echo TAB.TAB.TAB.TAB.TAB."marker".$count.".bindInfoWindowTabs(tabArray".$count.");".NL;
                            echo TAB.TAB.TAB.TAB.TAB."bounds.extend(point);".NL;
                            echo TAB.TAB.TAB.TAB.TAB."GEvent.addDomListener(document.getElementById('{$row['twitter_id']}_link'),'click',function(e) {".NL;
                            echo TAB.TAB.TAB.TAB.TAB.TAB."marker".$count.".openInfoWindow(tabArray".$count.");".NL;
                            echo TAB.TAB.TAB.TAB.TAB."});".NL;
                            echo NL.NL;
                            $count++;
                        }
                    }

                    if ($notEmpty){
                        echo TAB.TAB.TAB."map.setZoom(map.getBoundsZoomLevel(bounds)-1);".NL;
                        echo TAB.TAB.TAB."map.setCenter(bounds.getCenter());".NL;
                    }
                    echo TAB.TAB.TAB."}".NL;
                    echo NL.TAB.TAB."}".NL;
                    echo TAB."}".NL;
                    echo TAB."else {".NL;
                    echo TAB.TAB."alert(\"could not find address\");".NL;
                    echo TAB."}".NL;
                    echo "});".NL;
                    echo "}".NL;
                    echo "</script>".NL;
                }
                else {
                    echo "<script>".NL;
                    echo TAB."document.getElementById(\"map\").innerHTML += \"<br>Address not found! Please find another!</br>\"".NL;
                    echo "</script>".NL;
                }
            }

        ?>
    </div>
    </form>

<!-- separate container for table results -->

    <div id="resultsTable">
        <?php include 'rounded_start.php'; ?>

        <div id="table_content">
            <?php
            if(isset($_GET["submit"])) {
                if($notEmpty){
                    mysql_data_seek($result,0);

echo <<<tableStart
<table id="sortable_results" class="tablesorter">
    <thead>
        <tr>
            <th align='left'>Name</th>
            <th align='left'>Food Type</th>
            <th align='left'>Cost</th>
            <th align='left'>Distance</th>
            <th align='left'>Twitter</th>
        </tr>
    </thead>
    <tbody>
tableStart;

                    while($row = mysql_fetch_assoc($result)) {
                        echo TAB.TAB.TAB.TAB."<tr id=\"{$row['twitter_id']}_link\">";
                        echo "<td><a href=\"".$row['website_url']."\" target=\"_blank\">" . $row['business_name'] . "</a></td>".

                             "<td>{$row['food_tag']}</td>".
                             "<td>";

                             $cost = $row['cost'];
                             for ($index = 0; $index < $cost; $index++)
                             {
                                 echo "$";
                             }

                             echo "</td>";
                             echo "<td>" . $num = number_format($row['distance'], 1, '.', '') . " mi</td>".
                             "<td><a href=\"http://www.twitter.com/" . $row['twitter_id'] . "\" target=\"_blank\"><img src=\"images/twitter_icon.png\" border=\"0\" width=\"10\" height=\"13\" alt=\"" . $row['business_name'] . "\" /></a></td></tr>".NL;
                    }
                    echo TAB.TAB.TAB.TAB."</tbody>".NL;
                    echo TAB.TAB.TAB."</table>".NL;
                }
                else {
                    echo "No trucks matching the given criteria were found.";
                }
            }
            ?>
         </div>

        <?php include 'rounded_end.php'; ?>
    </div>

    <div class="push"></div>
</div>
</div>

<div class="footer">
    <br />&copy; 2010 Gang of Four
    <br />Drop us a line! <a href="mailto:gang@trucktrackr.com">gang@trucktrackr.com</a>
</div>

<script type="text/javascript">
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript">
    try
    {
        var pageTracker = _gat._getTracker("UA-15776378-2");
        pageTracker._trackPageview();
    } catch(err) {}
</script>

</body>
</html>
