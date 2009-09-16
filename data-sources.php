<?php
/*
 * Created on Sep 2, 2009
 *
 * Data Sources
 * 
 * To create a new one add an element to the $sites array.
 * 
 * Array elements:
 * 
 * name - Text name of site
 * source - URL of XML/RSS feed or webpage to be scraped
 * format - Gives details of how data is formatted. Used by convert_reading() to transform
 *          data into a standard format (e.g same units)
 * tagformat - Defaults to "tags" but can be set to "xpath". Used to determine how to treat 
 *             the contents of the tags array.
 * tags - Associative array containing a quantity to measure and a query that is used to obtain
 *        that measurement from the source file.
 * 
 *        If tagformat is "tags" then the array value is just the name of the XML tag to return the contents
 *        from. For instance windDir => wind_dir would return 15 if the following line was in the source
 *        file:   <wind_dir>15</wind_dir>
 * 
 *        If tagformat is "xpath" then the array value is an array consisting of a query (which is 
 *        an xpath query returning the matching tag) and an attribute (which is the name of attribute 
 *        who's value should be returned. For example if windDir => array('/obtb','d') then 15 would be 
 *        returned from the following file:  <obtb d="15" />
 * 
 */
 
$sites = array();

$sites[] = array('name' => 'Eastbourne',
              'link' => 'http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=IWELLING17',
              'source' => 'http://api.wunderground.com/weatherstation/WXCurrentObXML.asp?ID=IWELLING17',
              'format' => 'wund',
              'tags' => array('windSpeedMph' => 'wind_mph',
                              'windDir' => 'wind_degrees',
                              'obsTimeRFC822' => 'observation_time_rfc822',
                              'temp' => 'temp_c',
                              'pressure' => 'pressure_mb',
                              'windGustMph' => 'wind_gust_mph'),
              'comment' => 'Seems to be offline at present.'
           );

$sites[] = array('name' => 'Waikanae Beach',
              'link' => 'http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=IWAIKANA1',
              'source' => 'http://api.wunderground.com/weatherstation/WXCurrentObXML.asp?ID=IWAIKANA1',
              'format' => 'wund',
              'tags' => array('windSpeedMph' => 'wind_mph',
                              'windDir' => 'wind_degrees',
                              'obsTimeRFC822' => 'observation_time_rfc822',
                              'temp' => 'temp_c',
                              'pressure' => 'pressure_mb',
                              'windGust' => 'wind_gust_mph')
           );

$sites[] = array('name' => 'Lyall Bay/Airport',
              'link' => 'http://www.wunderground.com/cgi-bin/findweather/getForecast?query=NZWN&wuSelect=WEATHER',
              'source' => 'http://api.wunderground.com/auto/wui/geo/WXCurrentObXML/index.xml?query=NZWN',
              'format' => 'wund',
              'tags' => array('windSpeedMph' => 'wind_mph',
                              'windDir' => 'wind_degrees',
                              'obsTimeRFC822' => 'observation_time_rfc822',
                              'temp' => 'temp_c',
                              'pressure' => 'pressure_mb',
                              'windGustMph' => 'wind_gust_mph'),
              'comment' => 'Updates hourly.'
           );
           
           $sites[] = array('name' => 'Seatoun',
              'link' => 'http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=I90580064',
              'source' => 'http://api.wunderground.com/weatherstation/WXCurrentObXML.asp?ID=I90580064',
              'format' => 'wund',
              'tags' => array('windSpeedMph' => 'wind_mph',
                              'windDir' => 'wind_degrees',
                              'obsTimeRFC822' => 'observation_time_rfc822',
                              'temp' => 'temp_c',
                              'pressure' => 'pressure_mb',
                              'windGustMph' => 'wind_gust_mph')
           );
           
$sites[] = array('name' => 'Petone',
              'link' => 'http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=IWELLING15',
              'source' => 'http://api.wunderground.com/weatherstation/WXCurrentObXML.asp?ID=IWELLING15',
              'format' => 'wund',
              'tags' => array('windSpeedMph' => 'wind_mph',
                              'windDir' => 'wind_degrees',
                              'obsTimeRFC822' => 'observation_time_rfc822',
                              'temp' => 'temp_c',
                              'pressure' => 'pressure_mb',
                              'windGustMph' => 'wind_gust_mph')
           );
           
$sites[] = array('name' => 'Point Jerningham',
              'link' => 'http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=IWELLING25',
              'source' => 'http://api.wunderground.com/weatherstation/WXCurrentObXML.asp?ID=IWELLING25',
              'format' => 'wund',
              'tags' => array('windSpeedMph' => 'wind_mph',
                              'windDir' => 'wind_degrees',
                              'obsTimeRFC822' => 'observation_time_rfc822',
                              'temp' => 'temp_c',
                              'pressure' => 'pressure_mb',
                              'windGustMph' => 'wind_gust_mph')
           );

$sites[] = array('name' => 'Paraparaumu',
              'link' => 'http://www.wunderground.com/weatherstation/WXDailyHistory.asp?ID=INIOTAIH2',
              'source' => 'http://api.wunderground.com/weatherstation/WXCurrentObXML.asp?ID=INIOTAIH2',
              'format' => 'wund',
              'tags' => array('windSpeedMph' => 'wind_mph',
                              'windDir' => 'wind_degrees',
                              'obsTimeRFC822' => 'observation_time_rfc822',
                              'temp' => 'temp_c',
                              'pressure' => 'pressure_mb',
                              'windGustMph' => 'wind_gust_mph')
           );
           
// See here for clientraw.txt data specification:
// http://www.tnetweather.com/nb-0100.php
$sites[] = array('name' => 'Plimmerton',
              'link' => 'http://www.plimmertonboatingclub.org.nz/weatherstation.htm',
              'source' => 'http://www.plimmertonboatingclub.org.nz/weather/clientraw.txt',
              'format' => 'wdl',
              'tagformat' => 'text',
              'tags' => array('windDir' => 3,
                              'windSpeed' => 1,
                              'windGust' => 140, // max gust in last minute - could also use 133
                              'temp' => 4,
                              'pressure' => 6,
                              'obsHour' => 29,
                              'obsMin' => 30,
                              'obsSec' => 31,
                              'obsDay' => 35,
                              'obsMon' => 36,
                              'obsYear' => 141 )
           );

           
/* FORMAT FOR DATA FROM WIND.CO.NZ
$sites[] = array('name' => 'Airport',
              'source' => 'http://www.wind.co.nz/nonhtml/getxml.php?regionid=53',
              'format' => 'wind',
              'tagformat' => 'xpath',
              'tags' => array('windDir' => array('query' => '//sensor[@sensorid="1"]/obtb/ob[@dtid="1"]', 
                                                 'attr' => 'd'),
                              'windSpeed' => array('query' => '//sensor[@sensorid="1"]/obtb/ob[@dtid="2"]', 
                                                 'attr' => 'd'),
                              'obsTimeYMDHMS' => array('query' => '//sensor[@sensorid="1"]/obtb', 
                                                 'attr' => 't')
                             )
           );
          
$sites[] = array('name' => 'Paraparaumu',
              'source' => 'http://www.wind.co.nz/nonhtml/getxml.php?regionid=53',
              'format' => 'wind',
              'tagformat' => 'xpath',
              'tags' => array('windDir' => array('query' => '//sensor[@sensorid="8"]/obtb/ob[@dtid="1"]', 
                                                 'attr' => 'd'),
                              'windSpeed' => array('query' => '//sensor[@sensorid="8"]/obtb/ob[@dtid="2"]', 
                                                 'attr' => 'd'),
                              'obsTimeYMDHMS' => array('query' => '//sensor[@sensorid="8"]/obtb', 
                                                 'attr' => 't')
                             )
           );
*/            
?>
