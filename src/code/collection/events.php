<?php namespace Collection;
class Events extends \Collection {
	
	// Fetch events
	public function fetch($gateway) {
		$this->list = [];
		
		// Parse events
		$list = $gateway->fetch($gateway::host.'/Aktuell/Hochschultermine');
		foreach($list->query('//table[@id="HTermin"]/tbody/tr[td[3]]') as $row) {
			$cols = $list->query('td', $row);
			$event = [];
			$titles = [];
			$descriptions = [];
			
			// Parse date
			$date = explode('–', $cols[0]->nodeValue);
			$event['start'] = (new \DateTime($date[0]))->format('Y-m-d H:i:s');
			$event['end'] = isset($date[1]) ? (new \DateTime($date[1]))->format('Y-m-d H:i:s') : NULL;
			
			// Parse text
			foreach($cols[2]->childNodes as $check) {
				if($check->nodeName == '#text') $descriptions[] = $check->nodeValue;
				
				// Skip links
				elseif($check->nodeName == 'a') continue;
				
				// Parse title
				elseif($check->nodeName == 'strong') {
					foreach($check->childNodes as $title) {
						if($title->nodeName == '#text') $titles[] = $title->nodeValue;
					}
				}
			}
			
			// Assemble event
			$event['title'] = empty($titles) ? NULL : implode("<br>", $titles);
			$event['description'] = empty($descriptions) ? NULL : implode("<br>", $descriptions);
			$this->list[] = $event;
		}
	}
	
	// Write events
	public function write($db) {
		$db->query('DELETE FROM events');
		foreach($this->list as $event) $db->query('
			INSERT INTO events (title, description, start, end) 
			VALUES (:title, :description, :start, :end)
		', $event);
	}
}