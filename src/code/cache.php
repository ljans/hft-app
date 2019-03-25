<?php class Cache {
	private $controller, $refreshed;
	
	// Constructor
	public function __construct($controller) {
		$this->controller = $controller;
		
		// Setup rate limiting
		$this->controller->lsf->requestLimit = 3.0;
		$this->controller->hft->requestLimit = 3.0;
		
		// Reset refresh times
		$this->refreshed = [
			'meals' => 0,
			'events' => 0,
			'subjects' => 0,
			'professors' => time(),
		];
	}
	
	// This method refreshes a single cache and then returns so that control messages can be handled
	public function cycle() {
		
		// Skip maintenance period
		if(date('H') < 2) return sleep(10);
				
		// Clear inactive devices and users
		$devices = $this->controller->db->query('DELETE FROM devices WHERE active < ADDDATE(CURRENT_TIMESTAMP, INTERVAL -3 MONTH) AND active IS NOT NULL');
		$users = $this->controller->db->query('DELETE FROM users WHERE active < ADDDATE(CURRENT_TIMESTAMP, INTERVAL -1 YEAR) AND active IS NOT NULL');
		if($devices->rowCount() > 0) Service::log('cleared '.$devices->rowCount().' devices');
		if($users->rowCount() > 0) Service::log('cleared '.$users->rowCount().' users');

		
		// Refresh subjects
		if(time() - $this->refreshed['subjects'] > 60*60*24) {
			$this->refreshed['subjects'] = time();
			
			// Log action
			Service::log('refreshing subjects');
			
			// Fetch subjects
			$subjects = new Collection\Subjects();
			$subjects->fetch($this->controller->lsf);
			$subjects->write($this->controller->db);
			
			// Log action
			return Service::log($subjects->length().' subjects refreshed');
		}
		
		// Refresh events
		if(time() - $this->refreshed['events'] > 60*60*24) {
			$this->refreshed['events'] = time();
			
			// Log action
			Service::log('refreshing events');
			
			// Fetch events
			$events = new Collection\Events();
			$events->fetch($this->controller->hft);
			$events->write($this->controller->db);
			
			// Log action
			return Service::log($events->length().' events refreshed');
		}
		
		// Refresh meals
		if(time() - $this->refreshed['meals'] > 60*60*24) {
			$this->refreshed['meals'] = time();
			
			// Log action
			Service::log('refreshing meals');
			
			// Fetch meals
			$meals = new Collection\Meals();
			$meals->fetch($this->controller->sws);
			$meals->write($this->controller->db);
			
			// Log action
			return Service::log($meals->length().' meals refreshed');
		}
		
		// Refresh professors
		if(time() - $this->refreshed['professors'] > 60*60*24*7) {
			$this->refreshed['professors'] = time();
			
			// Log action
			Service::log('refreshing professors');
			
			// Fetch professors
			$professors = new Collection\Professors();
			$professors->fetch($this->controller->hft);
			$professors->write($this->controller->db);
			
			// Log action
			return Service::log($professors->length().' professors refreshed');
		}
		
		// Refresh courses and lectures by subject
		{
			$query['subject'] = $this->controller->db->query('
				SELECT id, parallelid FROM subjects 
				WHERE refreshed IS NULL OR refreshed < ADDDATE(CURRENT_TIMESTAMP, INTERVAL -1 DAY) 
				ORDER BY refreshed ASC LIMIT 1
			');
			
			// A subject has to be refreshed
			if($query['subject']->rowCount() == 1) {
				$subject = $query['subject']->fetch();
				
				// Log action
				Service::log('refreshing courses and lectures of subject '.$subject['id']);
				
				// Update refresh time
				$this->controller->db->query('UPDATE subjects SET refreshed = CURRENT_TIMESTAMP WHERE id = ?', $subject['id']);
				
				// Fetch courses
				$courses = new Collection\Courses($subject);
				$courses->fetch($this->controller->lsf);
				$courses->write($this->controller->db);
				
				// Fetch lectures
				$lectures = new Collection\Lectures($subject);
				$lectures->fetch($this->controller->lsf);
				$lectures->write($this->controller->db);
					
				// Log action
				return Service::log($courses->length().' courses with a total of '.$lectures->length().' lectures refreshed for subject '.$subject['id']);
			}
		}

        // Refresh lectures changes
        {
            $courses = new Collection\LectureChanges();
            $courses->fetch($this->controller->lsf);
            $courses->write($this->controller->db);
        }
		
		// Refresh users
		{
			$query['user'] = $this->controller->db->query('
				SELECT username, password FROM users 
				WHERE (refreshed IS NULL OR refreshed < ADDDATE(CURRENT_TIMESTAMP, INTERVAL -15 MINUTE)) 
				AND active > ADDDATE(CURRENT_TIMESTAMP, INTERVAL -3 MONTH) 
				AND valid IS TRUE AND enabled IS TRUE 
				ORDER BY refreshed ASC LIMIT 1
			');
			
			// A user has to be refreshed
			if($query['user']->rowCount() == 1) {
				$user = $query['user']->fetch();
				
				// Update refresh time
				$this->controller->db->query('UPDATE users SET refreshed = CURRENT_TIMESTAMP WHERE username = ?', $user['username']);
				
				// Log action
				Service::log('refreshing exams for user '.$user['username']);
				
				// Login at gateway
				if(!$this->controller->lsf->login($user['username'], $user['password'])) {
					Service::log('invalidated user '.$user['username']);
					return $this->controller->db->query('UPDATE users SET valid = FALSE WHERE username = ?', $user['username']);
				}

				// Read old state
				$old = new Collection\Exams($user['username']);
				$old->read($this->controller->db);
			
				// Write new state
				$new = new Collection\Exams($user['username']);
				$new->fetch($this->controller->lsf);
				$new->write($this->controller->db);
			
				// Logout at gateway
				$this->controller->lsf->logout();
				
				// Determine added exams
				$added = [];
				foreach($new->list() as $test) {
					foreach($old->list() as $compare) {
						if($compare['id'] == $test['id'] && $compare['try'] == $test['try']) continue 2;
					} $added[] = $test;
				}
				
				// Add message
				if(count($added) > 0) {
					$text = '<ul>';
					foreach($added as $exam) $text.= '<li>'.$exam['title'].'</li>';
					$text.= '</ul>';

                    Notification::sendNotification($this->controller->db,
                        $user['username'],
                        [
                            'title' => count($added) > 1 ? 'Neue Prüfungsergebnisse' : 'Neues Prüfungsergebnis',
                            'text' => $text,
                            'href' => '/exams',
                        ],
                        "exam");
				}
				
				// Log action
				return Service::log($new->length().' exams refreshed for user '.$user['username']);
			}
		}
		
		// Service idle
		return sleep(1);
	}
}
