<?php

/* a library of location parsing functions */

// Return the location from the location string in a Twitter user's info.
// Return an array of ['lat']['longit']
// inputs:  $locStr     A location string from the user info
// outputs: $locPair    A pair of strings, latitude and longitude if exists
function getLocFromUserInfo($locStr)
{
    if (is_null($locStr))
    {
        return NULL;
    }
    else
	{
        $splitStr = explode(' ', $locStr);
	    $locPos = 0;
        $lenSplit = count($splitStr);

        // stop until the first plausible coordinate. skips if the locations of 
        // text, e.g. New York, New York and skips over things like
        // "iPhone: " and "UT: "
	    while ($splitStr[$locPos][0] != '-' && !ctype_digit($splitStr[$locPos][0]) &&
			   $splitStr[$locPos][0] != '+')
        { 
            $locPos++;

            // couldn't find any location coordinates
            if($locPos >= $lenSplit)
                return NULL;
        }

        $splitStrPair = explode(',', $splitStr[$locPos]);
        $locPair = array ();

        // strip away any leading +
        if($splitStrPair[0][0] == '+')
	    {
            $splitStrPair[0] = substr($splitStrPair[0], 1);
        }

		if($splitStrPair[1][0] == '+')
	    {
            $splitStrPair[1] = substr($splitStrPair[1], 1);
        }

		// return the pair if they are valid decimals
        if (is_numeric($splitStrPair[0]) && is_numeric($splitStrPair[1]))
	    {
			$locPair['lat'] = $splitStrPair[0];
			$locPair['longit'] = $splitStrPair[1];

			return $locPair;
        }
        else
	    {
            return NULL;
        }
    }
}

?>
