Project to produce live weather data for kiting

Two main elements to project:
- PHP script to scrape data from weather sources and present as single JSON source file
- JavaScript page to collect latest data and display

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
		updated: 1251768111,
		reading: {
			windSpeed: 13,
			windDirection: 265,
			pressure: 1010
		}
	},
	{
		name: "Petone",
		updated: 1251768208,
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
data[1].updated = 1251768208

Indexes need to be mapped to sites


== JavaScript page ==

use setInterval to collect JSON file every ~30s
compare data[i].updated to date stamp on currently displayed readings
If newer, update the reading and the date stamp

Provide a counter (time to refresh), and a button to refresh instantly
Indicate new updates with a brief flash?

Start with a table of data, then add map and other visualizations?


