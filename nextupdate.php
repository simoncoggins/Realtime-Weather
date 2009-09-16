<?php
/**
 * Returns number of milli-seconds until next update in JSON format
 * Used by Javascript code to show time to next update clock
 * Required because server and client times are not always in sync
 */

// how many minutes between server updates
$cronInterval = 15; // in minutes
// how long to wait for server updates to complete (milliseconds)
$loadDelay = 10000; // in milliseconds

// split time into segments based on interval, then find the next
// segment and calcuate the time to next update
$now = getdate();
$currentTime = $now['minutes']+$now['seconds']/60.0;
$segment = (int) ($currentTime / $cronInterval);
$nextsegment = ($segment+1)*$cronInterval;

// add $loadDelay to give server time to fetch data
$timeToUpdate = ($nextsegment - $currentTime) * 60 * 1000 + $loadDelay;

// prevent caching
header("Cache-Control: max-age=0, no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

print json_encode($timeToUpdate);

?>
