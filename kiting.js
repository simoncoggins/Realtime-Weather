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
    // how long between page refreshes (to update time ago field)
    reloadInterval: 10, // seconds
    // how often to refresh if next reload time cannot be obtained from server
    defaultReload: 15*60*1000, // milliseconds
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
    if(CFG.clockTime == 'Unknown') {
        $('#clock').html('Next update time unknown. Trying again in '+parseInt(CFG.defaultReload/(1000*60))+'m');
    } else {

        var timeSecs = parseInt(CFG.clockTime/1000.0);
    
        var reading = (timeSecs > 0) ? agostr(timeSecs) : 'Loading...';

        $('#clock').html('Next update in '+reading);
    }
}


// modifies the global clockTime and updates the clock
function resetClock(when) {
    CFG.clockTime = when;
    updateClock();
}


// decrease the clock time by 1 second and updates the clock
function decrementClock() {
    if (CFG.clockTime != 'Unknown') {
        CFG.clockTime -= 1000;
        // every reloadInterval secs, refresh the page without new data
        if(Math.round(CFG.clockTime/1000.0) % CFG.reloadInterval == 0) {
            buildData();
        }
        updateClock();
    }
}


// update the table via AJAX, then calculate when next update should
// occur and setTimeout to call this function again when required
function updateTable() {
    // get latest date from JSON and update the page
    var source="data.json";
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

    var updateTime;
    $.ajax({
        url: "nextupdate.php",
        dataType: "json",
        success: function(data,textStatus){
                if(textStatus=="success") {
                    var updateTime = data;
                    if (updateTime < 1000) {
                        updateTime = 1000;
                    }
                    resetClock(updateTime);
                    setTimeout("updateTable()", updateTime);
                } else {
                    var updateTime = 'Unknown';
                    // try again in 10 secs
                    resetClock(updateTime);
                    setTimeout("updateTable()", CFG.defaultReload);
                }
            },
        error: function() {
                var updateTime = 'Unknown';
                resetClock(updateTime);
                setTimeout("updateTable()", CFG.defaultReload);
            }
    });

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
