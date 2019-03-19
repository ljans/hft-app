<?php // HFT App Event Exporter
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: inline; filename=event.ics');

// ICS datetime format
$format = 'Ymd\THis';

// Event header
$properties = [
	'BEGIN:VCALENDAR',
	'VERSION:2.0',
	'CALSCALE:GREGORIAN',
	'PRODID:-//Luniverse//HFT App//DE',
	'X-WR-CALNAME:Hochschultermine',
	'X-WR-TIMEZONE:Europe/Berlin',
	'BEGIN:VEVENT'
];

// Event body
foreach(['description', 'dtend', 'dtstart', 'location', 'summary'] as $key) {
	if(!isset($_GET[$key])) continue;
	$value = in_array($key, ['dtend', 'dtstart']) ? date($format, $_GET[$key]) : preg_replace('/([\,;])/', '\\\$1', $_GET[$key]);
	$properties[] = strtoupper($key).':'.$value;
}

// Event footer
print implode("\r\n", array_merge($properties, [
	'UID:'.uniqid(),
	'DTSTAMP:'.date($format),
	'END:VEVENT',
	'END:VCALENDAR'
]));