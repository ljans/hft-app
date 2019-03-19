<?php namespace Collection;
class Professors extends \Collection {
	
	// Fetch professors
	public function fetch($gateway) {
		$this->list = [];
		foreach(['A', 'B', 'C'] as $faculty) {
			
			// Parse professor list
			$list = $gateway->fetch($gateway::host.'/Hochschule/Organisation/Professoren/Fak'.$faculty.'/index.html/de?printable=true');
			foreach($list->query('//div[@id="Proftab"]/table/tr') as $index => $row) {
				$link = $list->query('td[1]/h2/a', $row)->item(0);
				$professor = [
					'name' => rtrim($link->nodeValue, ' >>'),
					'phone' => NULL,
					'email' => NULL,
					'room' => NULL,
					'time' => NULL
				];
				
				// Parse professor details
				$details = $gateway->fetch($gateway::host.$link->getAttribute('href').'?printable=true');
				foreach($details->query('//div[@id="Tabelle"]/table/tr') as $data) {
					
					// Extract dataset
					$key = rtrim($details->query('td[1]', $data)->item(0)->nodeValue, ':');
					$value = $details->query('td[2]', $data)->item(0)->nodeValue;
					
					// Parse information
					switch($key) {
						case 'Telefon':	$professor['phone'] = $value; break;
						case 'E-Mail': $professor['email'] = $value; break;
						case 'Büro': $professor['room'] = ltrim($value, 'Raum '); break;
						case 'Sprechzeiten': $professor['time'] = $value; break;
					}
				} $this->list[] = $professor;
				print $professor['name']."\r\n";
			}
		}
	}
	
	// Write professors
	public function write($db) {
		$db->query('DELETE FROM professors');
		foreach($this->list as $professor) $db->query('
			INSERT INTO professors (name, phone, email, room, time) 
			VALUES (:name, :phone, :email, :room, :time)
		', $professor);
	}
}