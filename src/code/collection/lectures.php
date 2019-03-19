<?php namespace Collection;
class Lectures extends \Collection {
	private $subject;
	
	// Constructor
	public function __construct($subject) {
		$this->subject = $subject;
	}
	
	// Extract lecture data
	private static function extract($key, $string) {
		switch($key) {
		
			// Extract room
			case 'room': return preg_match('/((?:[1-9]|L)\/(?:A|U|[0-9])[0-9]{1,2}|1\/AULA|VAI-Labor|Flur EG|Aula Vorraum)/', $string, $room) ? $room[1] : false;
			
			// Extract professor
			case 'professor': return preg_match('/^Durchf\. Lehrperson(?:en)?: ?([^\,]+)(?:\,.+)?$/', $string, $professor) ? $professor[1] : false;
			
			// Extract time
			case 'time': return preg_match('/(2[0-3]|[01][0-9]):([0-5][0-9]) - (2[0-3]|[01][0-9]):([0-5][0-9])/', $string, $match) ? [
				'start' => ['H' => $match[1], 'i' => $match[2]],
				'end' => ['H' => $match[3], 'i' => $match[4]]
			] : false;
		}
	}
	
	// Fetch lectures
	public function fetch($gateway) {
		$this->list = [];
		
		// Fetch lectures from next weeks
		for($n=0; $n<3; $n++) {
			$date = strtotime('+'.$n.' weeks');
			$week = intval(date('W', $date)).'_'.date('Y', $date);
			
			// Fetch timetable view
			$view = $gateway->fetch($gateway::host.'?state=wplan&pool=stg&act=stg&P.vx=lang&P.Print', [
				'week' => $week,
				'k_parallel.parallelid' => $this->subject['parallelid'],
				'k_abstgv.abstgvnr' => $this->subject['id']
			]);
			
			// Reset days of week and grid
			$days = [];
			$grid = [];
			for($y=0; $y<48; $y++) $grid[$y] = [];
			
			// Traverse headline cols (for information about the date)
			foreach($view->query('//body/table[2]/tr[1]/th') as $x => $head) {
				foreach($head->childNodes as $index => $info) {
					if($index == 1) $days[$x] = new \DateTime($info->nodeValue);
				}
			}
			
			// Traverse rows
			foreach($view->query('//body/table[2]/tr[position() > 2][position() < 49]') as $y => $row) {
				
				// Skip time cols (every 4th is doubled)
				$skip = $y % 4 == 0 ? 2 : 1;
				
				// Traverse cols
				foreach($view->query('td[position() > '.$skip.']', $row) as $col) {
					
					// Find lecture day (next free grid item)
					// CAUTION: Check if day of week isset due to a strange timetable-column "keine Angabe" after friday in LSF
					$x = 0;
					while(isset($grid[$y][$x]) && isset($days[$x+1])) $x++;
					
					// Get col rowspan (=> contains lecture) or occupy grid item
					$rowspan = $col->getAttribute('rowspan');
					if($rowspan == '') $grid[$y][$x] = false;
					
					// Occupy vertical grid items
					else {
						for($dy=0; $dy<$rowspan; $dy++) $grid[$y+$dy][$x] = true;
						
						// Traverse lectures
						foreach($view->query('table', $col) as $table) {
							
							// Parse id
							$href = $view->query('tr/td[1]/a[@class="ver"]', $table)->item(0)->getAttribute('href');
							preg_match('/publishid=(\d+)/', $href, $course);
							
							// Setup new lecture
							$lecture = [
								'subject' => $this->subject['id'],
								'course' => $course[1],
								'room' => NULL,
								'professor' => NULL
							];
							
							// Parse infos with effort-ordered conditions
							foreach($view->query('tr/td[@class="notiz"]', $table) as $info) {
								foreach(['room', 'professor', 'time'] as $key) {
									
									// Skip already known values
									if(isset($lecture[$key])) continue;
									
									// Try to extract value
									$result = self::extract($key, $info->nodeValue);
									if(!$result) continue;
									
									// Build datetimes from day + time
									if($key == 'time') {
										$lecture['start'] = (clone $days[$x])->setTime($result['start']['H'], $result['start']['i'])->format('Y-m-d H:i:s');
										$lecture['end'] = (clone $days[$x])->setTime($result['end']['H'], $result['end']['i'])->format('Y-m-d H:i:s');
										
									// Add plain information
									} else $lecture[$key] = $result;
									
									// Skip further checks for this info
									continue 2;
								}
								
							// Add lecture to the list
							} if(isset($lecture['start']) && isset($lecture['end'])) $this->list[] = $lecture;
						}
					}
				}
			}
		}
	}
	
	// Write lectures
	public function write($db) {
		$db->query('DELETE FROM lectures WHERE subject = ?', $this->subject['id']);
		foreach($this->list as $lecture) $db->query('
			INSERT INTO lectures (subject, course, start, end, room, professor) 
			VALUES (:subject, :course, :start, :end, :room, :professor)
		', $lecture);
	}
}