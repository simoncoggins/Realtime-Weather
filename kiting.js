// requires JQuery


// config object
var CFG = {
    // to keep track of update countdown for clock
    clockTime: 0, 
    // how many levels units to show in time ago strings
    timeUnitLevels: 2, 
    // how old before data is considered out of date (bad)
    badAge: 4*60*60, // 4 hrs
    // how old before data is considered a bit old (average)
    okAge: 20*60, // 20 mins
    // how many minutes between server updates
    cronInterval: 15, 
    // how long to wait for server updates to complete (milliseconds)
    loadDelay: 10000,  // 10 secs
    // how long between page refreshes (to update time ago field)
    reloadInterval: 10, // seconds
    // somewhere to store the data array
    data: null
};


// given a number of seconds, returns a formatted "Time Ago" string
function agostr(secs) {
  var out='';  
  var periods = [365.25*24*60*60, 24*60*60, 60*60, 60, 1];
  var strings = ['y ','d ','h ','m ','s'];
  // maximum number of time units to display
  // e.g. 4h 10m = 2 levels
  //      1y 34d 5h 6m 10s = 5 levels
  var level = CFG.timeUnitLevels;
    
  // loop through successively smaller time periods, trying 
  // to subtract whole numbers of units from the total
  // and appending to output when successful
  $.each(periods, function(i,period) {
      if (secs >= period && level > 0) {
        var num = parseInt(secs/period);
        out += num+strings[i];
        secs -= num*period;
        level--;
      }
  });

  return out;  
  
}


// get the HTML string required to build a row for the data table
function getTableRow(item) {
    
    // extract data from object and build into individual strings
    // some formatting and type checking here to ensure we print sensible values
    
    var site = (item['name']) ? item['name'] : 'Unknown';
    var comment = (item['comment']) ? '<small>'+item['comment']+'</small>' : '';
    var link = (item['link']) ? '<a href="'+item['link']+'">Source</a>' : '&mdash;';
    var temp = (item['reading']['temp'] !== false) ? item['reading']['temp']+'&deg;C' : '&mdash;';
    var pressure = (item['reading']['pressure'] !== false) ? item['reading']['pressure']+' mb' : '&mdash;';
    
    // convert wind speed and gust to 1 decimal place and add units
    var windSpeed = (item['reading']['windSpeed'] !== false) ? (Math.round(10.0*item['reading']['windSpeed'])/10.0)+' kts' : '&mdash;';
    var windGust = (item['reading']['windGust'] !== false) ? (Math.round(10.0*item['reading']['windGust'])/10.0)+' kts' : '&mdash;';

    // add degree symbol to direction
    // if direction in degrees is missing, assume cardinal direction is no good either
    if (item['reading']['windDir'] !== false) {
      var windDir = item['reading']['windDir']+'&deg;';
      var windCardinal = (item['reading']['windCardinal']) ? item['reading']['windCardinal'] : '&mdash;';
    } else {
      var windDir = '&mdash;';
      var windCardinal = '&mdash;';
    }

    // calculate time difference between observation and now
    var obsTime = item['reading']['obsTime'];
    var d = new Date();
    var now = d.getTime()/1000.0;
    var diff = now - obsTime;    
    
    // start building HTML string to return
    var retStr = '<tr><td>'+site+'</td>';

    // diff is negative if obsTime is a future date
    if (obsTime !== false && diff >= 0) {
        var obsTimeFormatted = (item['reading']['obsTimeFormatted']) ? item['reading']['obsTimeFormatted'] : 'Obs time unknown';
        var agoString = agostr(diff);
        if (diff > CFG.badAge) {
            var quality="bad";
        } else if (diff > CFG.okAge) {
            var quality="average";
        } else {
            var quality="good";
        }
        var obsTimeStr = '<span class="'+quality+'" title="'+obsTimeFormatted+'">'+agoString+'</span>';
        retStr += '<td>'+windSpeed+'</td><td>'+windDir+'</td><td>'+windCardinal+'</td><td>'+windGust+'</td><td>'+temp+'</td><td>'+pressure+'</td><td>'+obsTimeStr+'</td>';
    } else {
       // if obsTime is bad, assume that whole row is no good and show Invalid Data error
       retStr += '<td colspan="7" class="aligncenter"><span class="bad">No data</td>';
    }

    // finish building return string
    retStr += '<td>'+link+'</td><td class="alignleft">'+comment+'</td></tr>';

    return retStr;

}


// builds the HTML code that makes up the table using JSON data
function buildData(){
    var data = CFG.data;
    // if data loaded, empty the table ready for values
    $('tbody#tablebody').html('');
    // loop through each site in turn
    $.each(data, function(i,item){
        var htmlStr = getTableRow(item);
        $('tbody#tablebody').html($('tbody#tablebody').html()+htmlStr );
    });
            
}


// replaces contents of #clock element with current time to update value
function updateClock() {
    var timeSecs = parseInt(CFG.clockTime/1000.0);
    
    var reading = (timeSecs > 0) ? agostr(timeSecs) : 'Loading...';

    $('#clock').html('Next update in '+reading);
}


// modifies the global clockTime and updates the clock
function resetClock(when) {
    CFG.clockTime = when;
    updateClock();
}


// decrease the clock time by 1 second and updates the clock
function decrementClock() {
    CFG.clockTime -= 1000;
    // every reloadInterval secs, refresh the page without new data
    if(Math.round(CFG.clockTime/1000.0) % CFG.reloadInterval == 0) {
        buildData();
    }
    updateClock();
}


// determines how long (in milliseconds) before the page 
// should next try to obtain fresh data via ajax
// For best results the interval and delay variables must be set in 
// the global CFG to synchronise with the cron schedule of the collection 
// script
function getTimeToUpdate() {
    
    // split time into segments based on interval, then find the next
    // segment and calcuate the time to next update
    var now = new Date();
    var currentTime = now.getMinutes()+now.getSeconds()/60;
    var segment = parseInt(currentTime / CFG.cronInterval);
    var nextSegment = (segment+1)*CFG.cronInterval;
    var timeToUpdate = (nextSegment - currentTime) * 60 * 1000 + CFG.loadDelay; // in milliseconds

    // simple sanity check to avoid overloading the browser
    if (timeToUpdate < 1000) {
        timeToUpdate = 1000;
    }

    // adjust the clock to show time to next update
    resetClock(timeToUpdate);

    return timeToUpdate; 
}


// update the table via AJAX, then calculate when next update should
// occur and setTimeout to call this function again when required
function updateTable() {
    // get latest date from JSON and update the page
    var source="http://localhost:8888/kiting/data.json";
    $.ajax({
        url: source,
        dataType: "json",
        success: function(data,textStatus){
                if(textStatus=="success") {
                    $('#msg').html('');
                    $('#msg').removeClass('error');
                } else {
                    $('#msg').html('Unspecified error loading data');
                    $('#msg').addClass('error');
                }
                // store the current data in global CFG object
                CFG.data = data;
                buildData();
            },
        error: function(xmlreq, textStatus, errorThrown) {
                if (textStatus=="error") {
                    $('#msg').html('Error: Could not load data file');
                    $('#msg').addClass('error');
                }
                if (textStatus=="parsererror") {
                    $('#msg').html('Error: Could not parse data file');
                    $('#msg').addClass('error');
                }
                if (textStatus=="timeout") {
                    $('#msg').html('Error: Timeout while trying to loading data file');
                    $('#msg').addClass('error');
                }
            }
    });

    var nextUpdate = getTimeToUpdate();
    if (nextUpdate && nextUpdate >= 1000) {
        // call this function again to refresh table when new data is expected to have arrived
        setTimeout("updateTable()",nextUpdate);
    } else {
        // maximum refresh rate of once every second
        setTimeout("updateTable()",1000);
    }
}

// execute once the DOM has loaded

$(document).ready(function() {
    // Show loading indicator
    $('tbody#tablebody').html('<tr><td colspan="10" class="aligncenter"><strong>Loading data...</strong></td></tr>');
    
    // start updating the table data
    updateTable();
    
    // start counting down the time to next update clock
    setInterval("decrementClock()",1000);
    

});
