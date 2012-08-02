<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">

<html>
<head>
    <title>trucktrackr.com | find local food trucks!</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" media="screen" href="style.css" type="text/css" />
</head>
<body>
<div class="wrapper">
<div class="container" style="padding-top: 30px;">

    <div id="search_box">
        <?php include 'rounded_start.php'; ?>

        <div id="search_box_content">
            <form name="search" id="search">
                    <input id="address_input" name="address" size="40" type="text" tabindex="1" />
                    <a id="submit" type="submit" tabindex="2" href="#">Go!</a>
            </form>
        </div>

        <?php include 'rounded_end.php'; ?>
    </div>

    <div id="logo_box">
        <img src="images/logo_small.png" width="205" height="38" />
    </div>

    <div id="map_results">
        <?php include 'rounded_start.php'; ?>

        <div id="map_results_content">
            <img src="example_images/map.png" width="500" height="403" />
        </div>

        <?php include 'rounded_end.php'; ?>
    </div>

    <div id="filters">
        <?php include 'rounded_start.php'; ?>

        <div id="filters_content">
            <p>A filter!</p>
        </div>

        <?php include 'rounded_end.php'; ?>
    </div>

    <div id="table">
        <?php include 'rounded_start.php'; ?>

        <div id="table_content">
            <p>A filter!</p>
            <p>A filter!</p>
            <p>A filter!</p>
            <p>A filter!</p>
            <p>A filter!</p>
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

</body>
</html>
