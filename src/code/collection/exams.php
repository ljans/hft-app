<?php namespace Collection;
class Exams extends \Collection {
	private $user;
	
	// Constructor
	public function __construct($user) {
		$this->user = $user;
	}
	
	// Setup column indices
	private static $indices = [
		0 => 'id',
		1 => 'title',
		//2 => 'semester',
		3 => 'grade',
		4 => 'status',
		5 => 'cp',
		//6 => 'note',
		7 => 'try',
		8 => 'date'
	];
	
	// Parse fields
	private static function parse($index, $value) {
		switch($index) {
			case 'id': return $value;
			case 'title': return str_replace('Prüfungsvorleistung', 'PVL', $value);
			case 'grade': return $value == '0,0' || $value == '' ? NULL : floatval(str_replace(',', '.', $value));
			case 'status': return $value;
			case 'cp': return $value == '0,0' ? NULL : floatval(str_replace(',', '.', $value));
			case 'try': return $value == '' ? NULL : intval($value);
			case 'date': return $value == '' ? NULL : (new \DateTime($value))->format('Y-m-d');
		}
	}
	
	// Fetch exams
	public function fetch($gateway) {
		
		// Parse menu
		$link = $gateway->fetch($gateway::host.'?state=change&type=1&moduleParameter=studyPOSMenu&next=menu.vm&xml=menu')->query('//a[.="Notenspiegel"]');
		if($link->length == 0) throw new \FormatError('a{Notenspiegel}');
		
		// Parse graduations
		$graduation = $gateway->fetch($link[0]->getAttribute('href'))->query('//ul[@class="treelist"]/li[@class="treelist"][1]/a[@href]');
		if($graduation->length == 0) throw new \FormatError('ul.treelist > li.treelist:first-child a[href]');
		
		// Parse exams
		$this->list = [];
		foreach($gateway->fetch($graduation[0]->getAttribute('href'))->query('//table[2]/tr[position() > 2]') as $y => $row) {
			$this->list[$y] = [];
			
			// Parse exam details
			foreach($row->childNodes as $x => $col) {
				if(isset(self::$indices[$x])) {
					$index = self::$indices[$x];
					$this->list[$y][$index] = self::parse($index, trim(strip_tags($col->nodeValue)));
				}
			}
		}
		
		// Reverse order
		$this->list = array_reverse($this->list);
	}
	
	// Write exams
	public function write($db) {		
		$db->query('DELETE FROM exams WHERE user = ?', $this->user);
		foreach($this->list as $exam) $db->query('
			INSERT INTO exams (id, user, title, status, grade, cp, try, date) 
			VALUES (:id, :user, :title, :status, :grade, :cp, :try, :date)
		', $exam + ['user' => $this->user]);
	}
	
	// Read exams
	public function read($db) {
		$query = $db->query('SELECT id, title, status, grade, cp, try, date FROM exams WHERE user = ?', $this->user);
		$this->list = [];
		while($exam = $query->fetch()) $this->list[] = $exam;
	}
}