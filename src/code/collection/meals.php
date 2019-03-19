<?php namespace Collection;
class Meals extends \Collection {
	
	// Fetch meals
	public function fetch($gateway) {
		$this->list = [];
		$result = $gateway->fetch($gateway::host);
		foreach($result['Mensa Stuttgart-Mitte'] as $date => $menu) {
			
			// Collect data
			foreach($menu as $index => $meal) $this->list[] = [
				'date' => $date,
				'title' => $meal['meal'].(strlen($meal['description']) > 0 ? ', ' : '').$meal['description'],
				'photo' => 'https://sws2.maxmanager.xyz/assets/'.(empty($meal['foto']) ? 'fotos/musikhochschule/Speisefotos/0-1/27816947m_dummy_speisen.jpg' : $meal['foto']),
				'price' => floatval(str_replace(',', '.', $meal['price1'])),
				'additives' => str_replace(',', ', ', $meal['additives'])
			];
		}
	}
	
	// Write meals
	public function write($db) {
		$db->query('DELETE FROM meals');
		foreach($this->list as $meal) $db->query('
			INSERT INTO meals (date, title, photo, price, additives) 
			VALUES (:date, :title, :photo, :price, :additives)
		', $meal);	
	}
}