<?php
/**
 * Obtain an individual value from an XML file using XPath
 * Note that result is returned as a DOMNodeList which must be parsed
 * 
 * @param string $xpath DOMXPath() object
 * @param string $query XPath query to apply to DOMXPath object
 * @return First match to query, or false if no match
 */
function get_value_by_xpath($xpath, $query) {
    $match = $xpath->query($query); 
    if($match === null || $match == "") {
        return false;
    } else {
        return $match;
    }
}

/**
 * Obtain an individual value from an XML file using getElementsBayTagName()
 * 
 * @param string $doc DOMDocument() object
 * @param string $tag Tag name to find in DOMDocument
 * @return First matching tag, or false if no match
 */
function get_value_by_tag_name($doc, $tag) {
    $match = $doc->getElementsByTagName($tag)->item(0)->nodeValue;
    if($match === null || $match == "") {
        return false;
    } else {
        return $match;
    }
}

/**
 * Obtain an individual value from an array by array key
 * 
 * Could just get via $source[$key] but this function does some checks
 * and returns false in consistent way to other get_value_by_* functions
 * 
 * @param array $source_as_array Array of elements to look through
 * @param string $key Key to find
 * @return Matching element, or false if no match
 */
function get_value_by_array_key($source_as_array, $key) {
    if(!isset($source_as_array[$key]) || $source_as_array[$key] == "") {
        return false;   
    }
    else {
        return $source_as_array[$key];
    }
}

/**
 * Given a data feed and associative array of tags, 
 * returns associative array of values for those tags
 * 
 * @param string $source URL or path to source XML file
 * @param array $tags Array of tag names to match
 * @param string $querytype If contents of tags are tag names or xpath queries
 * @return Array of matches to input tags
 */
function get_reading($source, $tags, $querytype="tags") {

    $readings = array();
    if ($querytype=="xpath" || $querytype=="tags") {
        $doc = new DOMDocument();
        $doc->load($source);
    }
    
    if ($querytype=="xpath") {
        $xp = new DOMXPath($doc);   
    }

    if ($querytype=="text") {
        $doc = file_get_contents($source);
        $source_as_array = explode(' ',$doc);   
    }
    
    foreach ($tags as $key => $tag) {
        switch ($querytype) {
            case 'xpath':
                // xpath queries must pass xpath query to element and attribute to select as an array
                $nodeList = get_value_by_xpath($xp, $tag['query']);
                if (!is_null($nodeList)) {
                    $readings[$key] = $nodeList->item(0)->getAttributeNode($tag['attr'])->value;
                }
                break;
            case 'tags':
                $readings[$key] = get_value_by_tag_name($doc, $tag);
                break;
            case 'text':
                $readings[$key] = get_value_by_array_key($source_as_array, $tag);
                break;
        }
    }
    return $readings;
}

/**
 * Converts a reading to include the desired fields and units
 * What needs to be done depends on format of source reading
 * 
 * @param array $reading Passed by reference. Array of readings to convert
 * @param string $convert Name of type of feed to determine what is required
 */
function convert_reading(&$reading, $format=null) {
    // conversions specific to individual formats
    switch ($format) {
        // XML file from http://api.wunderground.com/
        case 'wund':
            // convert MPH into knots
            if(isset($reading['windSpeedMph'])) {
                if ($reading['windSpeedMph'] !== false) {
                    $reading['windSpeed'] = $reading['windSpeedMph']*0.87;
                } else {
                    $reading['windSpeed'] = false;
                }
                unset($reading['windSpeedMph']);
            }
            if(isset($reading['windGustMph'])) {
                if ($reading['windGustMph'] !== false) {
                    $reading['windGust'] = $reading['windGustMph']*0.87;
                } else {
                    $reading['windGust'] = false;
                }
                unset($reading['windGustMph']);
            }
            // convert RFC822 timestamp into UNIX timestamp
            if(isset($reading['obsTimeRFC822'])) {
                if($reading['obsTimeRFC822'] !== false) {
                    $reading['obsTime'] = strtotime($reading['obsTimeRFC822']);
                } else {
                    $reading['obsTime'] = false;
                }
                unset($reading['obsTimeRFC822']);
            }
            break;
        
        case 'wind':
            // convert YYYYMMDDHHMMSS into UNIX timestamp
            if(isset($reading['obsTimeYMDHMS'])) {
                if($reading['obsTimeYMDHMS'] !== false) {
                    preg_match('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $reading['obsTimeYMDHMS'], $matches);
                    list($all,$yr,$mth,$day,$hr,$mn,$sc) = $matches;
                    $reading['obsTime'] = mktime($hr,$mn,$sc,$mth,$day,$yr);
                } else {
                    $reading['obsTime'] = false;
                }
                unset($reading['obsTimeYMDHMS']);
            }

            break;
            
        case 'wdl':
            // convert individual dates to timestamp
            if (isset($reading['obsDay']) && isset($reading['obsMon']) && isset($reading['obsYear'])) {
                if($reading['obsDay'] !== false && $reading['obsMon'] !== false && $reading['obsYear'] !== false) {
                    $reading['obsTime'] = mktime($reading['obsHour'], $reading['obsMin'], $reading['obsSec'],
                                                 $reading['obsMon'], $reading['obsDay'], $reading['obsYear']);
                } else {
                    $reading['obsTime'] = false;
                }
                unset($reading['obsHour']);
                unset($reading['obsMin']);
                unset($reading['obsSec']);
                unset($reading['obsDay']);
                unset($reading['obsMon']);
                unset($reading['obsYear']);  
            }
        break;
    }
    
    // conversions applicable to all formats

    // also send a formatted date string
    if($reading['obsTime'] !== false) {
        $reading['obsTimeFormatted'] = strftime('%c',$reading['obsTime']);   
    } else {
        $reading['obsTimeFormatted'] = false;   
    }

    // check readings are set
    if(!isset($reading['pressure'])) {
        $reading['pressure'] = false;   
    }
    if(!isset($reading['temp'])) {
        $reading['temp'] = false;   
    }
    if(!isset($reading['windGust'])) {
        $reading['windGust'] = false;   
    }
    if(!isset($reading['windDir'])) {
        $reading['windDir'] = false;   
    }
    if(!isset($reading['windSpeed'])) {
        $reading['windSpeed'] = false;   
    }
    

    // sometimes bad readings show as -999
    if($reading['windDir'] == '-999') {
        $reading['windDir'] = false;   
    }

    if($reading['windDir'] !== false) {
        // standardise direction to 0 -> 359
        $reading['windDir'] = $reading['windDir'] % 360;
        // add cardinal wind direction
        $reading['windCardinal'] = bearing2compass($reading['windDir']);
    } else {
        $reading['windCardinal'] = false;
    }

}

/**
 * Given an array of site information, returns the current values
 * 
 * @param array $site Associative array containing source feed and tags required
 * @return Associative array of value to be used
 */
function get_site_data($site) {
    $querytype = (isset($site['tagformat'])) ? $site['tagformat'] : 'tags';
    // get readings from XML feed
    $reading = get_reading($site['source'], $site['tags'], $querytype);
    // convert reading format if required
    convert_reading($reading, $site['format']);   
    
    // build output array
    $sitedata['name'] = $site['name'];
    $sitedata['reading'] = $reading;
    $sitedata['link'] = $site['link'];
    $sitedata['comment'] = $site['comment'];
    $sitedata['latitude'] = $site['latitude'];
    $sitedata['longitude'] = $site['longitude'];
    $sitedata['elevation'] = $site['elevation'];
    

    return $sitedata;
}

/**
 * This function is called as a callback function by array_filter(), to 
 * compare the latest data (latest) to the most recent feed data (feed).
 * 
 * If this function returns true, the current feed element is included in
 * a new list of elements to use. If this function returns false, the current 
 * feed element is excluded.
 * 
 * If the feed data is newer, the latest value is unset to avoid both being 
 * included in the merged feed.
 * 
 * @param array $feed Associative array containing a single site from the data feed
 * @global array $latest Array containing all the latest data to be compared to
 * @return bool True if current $feed element should be included in merged output array
 * 
 */
function compare_arrays($feed) {
    global $latest;
    // loop through output elements and compare to this element of orig
    foreach ($latest as $sitekey => $sitevalue) {
        // find elements with the same names
        if ($sitevalue['name'] == $feed['name']) {
            // see which is newer
            if ($sitevalue['reading']['obsTime'] > $feed['reading']['obsTime']) {
                // if output is newer, don't include this orig element
                return false;    
            } else {
                // if orig is newer, do include it, and unset this element from output
                unset($latest[$sitekey]);
                return true;
            }
            
        }
    }
    // if none of latest elements match this feed element, include it
    return true;
}

/**
 * This function takes the most recent data feed data ($feed) and the 
 * latest values that have just been obtained from the source ($latest) and
 * compares them. After matching on the 'Name' key, it retains the most 
 * recent of the two. If entries exist in one or the other both are retained.
 * It returns the new merged list, or false if no changes were made.
 * 
 * @param array $feed Most recent data feed as an array
 * @param array $latest Most recent data obtained directly from the source
 * @return array Merged array containing most recent data from both sources or false if no changes
 */
// take most recent info, only updating data.json if necessary
function merge_site_data($feed, $latest) {
    global $latest;
    // if feed not found, use new values
    if(!isset($feed)) {
        return $latest;
    }
    // keep elements from $feed that are more recent than indentically named 
    // elements in $latest.
    // Older matching elements are unset() from $latest to avoid duplication
    // when arrays are merged.
    $ret = array_filter($feed, 'compare_arrays');
    
    if(count($latest)>0) {
        // now add any remaining contents of $latest to ret
        $ret = array_merge($latest, $ret);
    } else {
        // if no elements are left in $latest, no changes have been
        // made to the data feed.
        $ret = false;
    }
    
    return $ret;
}

/**
 * Given a valid compass bearing returns the appropriate cardinal point
 * 
 * @param float $bearing The bearing to be converted
 * @returns string The cardinal point (e.g. NW) for the bearing or false if invalid
 * 
 */
function bearing2compass($bearing) {
    // check input
    if(!isset($bearing) || $bearing >= 360 || $bearing < 0) {
        return false;
    }

    // define cardinal directions
    $cardinals = array('N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 
                       'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW');
    // calculate array index to use
    $key = (int) ( ( ($bearing+11.25) % 360)/360.0 * 16 );

    return $cardinals[$key];
}




// debug mode
$debug = false;

// Path to latest data feed
$feedfile = 'data.json';

// message to display if viewed directly
$message = 'This script collects real time weather data from publicly available data feeds. See 
            <a href="kiting.html">this</a> page for further information. If this script is causing 
            problems or for further information please <a href="kiting.html#contact">contact</a> the site 
            administrator.';

// import data sources to use
require_once('data-sources.php');

// get the current data feed to compare with
if ($feedstr = file_get_contents($feedfile) ) {
    $feed = json_decode($feedstr,true);
}

// collect data for each site
$latest = array();
foreach ($sites as $site) {
    $latest[] = get_site_data($site);
}

// compare latest data to feed and 
// update feed file if changes required
if ($merged = merge_site_data($feed, $latest)) {
    // sort data array by name key
    foreach($merged as $key => $row) {
        // array_multisort requires array of columns to sort by
        $name[$key] = $row['name'];   
    }
    array_multisort($name, SORT_ASC, $merged);

    file_put_contents($feedfile,json_encode($merged));
    if($debug) {
        print "<h3>Data Feed Updated:</h3>";
        print "<pre>".print_r($merged,true)."</pre>";
    } else {
        print $message;    
    }
}
else {
    if($debug) {
        print "<h3>No changes to original feed:</h3>";
        print "<pre>".print_r($feed,true)."</pre>";
    } else {
        print $message;
    }
}


?>
