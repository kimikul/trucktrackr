<?php

// Open the folder
$dir_handle = opendir("./images/truck_logos/");

$files = array();
$fIndex = 0;
// Loop through the files
while ($file = readdir($dir_handle))
{
    if ($file != '.' && $file != '..' && (substr($file, strlen($file) - strlen( ".jpg" )) == ".jpg") )
    {
       $files[$fIndex++] = $file;
    }
}

foreach($files as $file)
{
    //blank transparent image
    $pintrue = imagecreatetruecolor(60,68);
    imagealphablending($pintrue, false);
    imagesavealpha($pintrue, true);
    $transparent = imagecolorallocatealpha($pintrue, 0,0,0,127);
    imagefill($pintrue, 0, 0, $transparent);

    //create pin
    $pin = imagecreatefrompng("./images/pins/later.png");
    imagealphablending($pin, false);
    imagesavealpha($pin, true);

    //copy pin over to transparent image
    imagecopy($pintrue, $pin, 0,0,0,0,60,68);
    
    //copy logo over
    $logo = imagecreatefromjpeg("./images/truck_logos/".$file);
    $logotrue = imagecreatetruecolor(48,48);
    imagecopy($logotrue, $logo, 0,0,0,0, 48, 48);
    imagecopyresized($pintrue, $logotrue, 12,12,0,0,35,35,48,48);
    imagepng($pintrue, './images/pins/later_'.strtr($file, ".jpg",".png"));
}
//free images from memeory
imagedestroy($logo);
imagedestroy($pin);
imagedestroy($pintrue);
imagedestroy($logotrue);

//now do the bigger images
$dir_handle = opendir("./images/truck_logos/big/");
$files2 = array();
$fIndex = 0;
// Loop through the files
while ($file = readdir($dir_handle))
{
    if ($file != '.' && $file != '..' && (substr($file, strlen($file) - strlen( ".jpg" )) == ".jpg") )
    {
       $files2[$fIndex++] = $file;
    }
}
foreach($files2 as $file)
{
    //blank transparent image
    $pintrue = imagecreatetruecolor(60,68);
    imagealphablending($pintrue, false);
    imagesavealpha($pintrue, true);
    $transparent = imagecolorallocatealpha($pintrue, 0,0,0,127);
    imagefill($pintrue, 0, 0, $transparent);

    //create pin
    $pin = imagecreatefrompng("./images/pins/later.png");
    imagealphablending($pin, false);
    imagesavealpha($pin, true);

    //copy pin over to transparent image
    imagecopy($pintrue, $pin, 0,0,0,0,60,68);

    //copy logo over
    $logo = imagecreatefromjpeg("./images/truck_logos/big/".$file);
    $logotrue = imagecreatetruecolor(73,73);
    imagecopy($logotrue, $logo, 0,0,0,0, 73, 73);
    imagecopyresized($pintrue, $logotrue, 12,12,0,0,35,35,73,73);
    imagepng($pintrue, './images/pins/later_'.strtr($file,".jpg",".png"));
}
imagedestroy($logo);
imagedestroy($pin);
imagedestroy($pintrue);
imagedestroy($logotrue);

?>
