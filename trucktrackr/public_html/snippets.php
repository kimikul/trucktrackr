<?php

function cleanString1($str, $wordArray)
{
    $sentenceArr = explode(' ', $str);
    $index = 0;

    for ($index = 0; $index < count($sentenceArr); $index++)
    {
        if (isset($wordArray[$sentenceArr[$index]]))
        {
            $sentenceArr[$index] = $wordArray[$sentenceArr[$index]];
        }
    }

    return implode(' ', $sentenceArr);
}

function cleanString2($str, $wordArray)
{
    $patterns = array_keys($wordArray);
    $index = 0;

    for ($index = 0; $index < count($patterns); $index++)
    {
        $patterns[$index] = '/(?<=([\s\p{P}]))' . $patterns[$index] . '(?=(([\s\p{P}]|\z)))/';
    }

    print_r($patterns);
    $res = preg_replace($patterns, array_values($wordArray), $str);

    return $res;
}


$wordArray = array('cat'=>'dog','bad'=>'good','old'=>'new');
$someString = 'that cat, is bad';

echo cleanString2($someString, $wordArray);

?>
