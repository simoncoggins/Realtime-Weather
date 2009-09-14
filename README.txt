Project to produce live weather data for kiting

Two main elements to project:
- PHP script to scrape data from weather sources and present as single JSON source file (collect.php)
- JavaScript page to collect latest data and display (currently table.html)

== Script ==

Don't want to run too often (~5 minutes?)

Input using DOM and Xpath to get values
Needs to collect data from various sources: RSS/XML/web pages
Need to convert to one set of units (e.g. kts)
Need checks to ensure sane values
Only update JSON file if new values are sensible/not null/have changed
Get time of reading collection if known
What to do re: time of reading vs. time of reading collection by script?


Output as JSON file:

var data = { [
	{
		name: "Lyall Bay",
		obsTime: 1251768111,
		reading: {
			windSpeed: 13,
			windDirection: 265,
			pressure: 1010
		}
	},
	{
		name: "Petone",
		obsTime: 1251768208,
		reading: {
			windSpeed: 20,
			windDirection: 180,
			pressure: 1020
		}
	}
] }

Accessed as:

data[0].name = "Lyall Bay"
data[1].reading.windSpeed = 20
data[1].obsTime = 1251768208



== JavaScript page ==

use setInterval to collect JSON file every ~30s
compare data[i].obsTime to date stamp on currently displayed readings
If newer, update the reading and the date stamp

Provide a counter (time to refresh), and a button to refresh instantly
Indicate new updates with a brief flash?

Start with a table of data, then add map and other visualizations?

Note, for value checking wunderground feeds use -999 as null value (at least for wind direction).


== To Do ==

- deal with data.json caching issues
- error handling if data cannot be loaded
- add wind gust, temperature, pressure, others?
- add lat/lng from file if present?
- improve Time Ago in table (two units or just secs)
- colours in ago column to warn of old data
- colours to indicate good/bad conditions (right wind speed range, right wind dir for site?)
- map version
- let users pick units (e.g. kts/mph/ms^-1/kph)
- correct plurialisation of time units

== Longer term To Do ==

- Add trend graphs showing change over last few hours
- Add forecasts if possible
- Recommendations/star ratings of each site in real-time?
- Make a small pop-up version

