<?php namespace Collection;
class Subjects extends \Collection {
	
	// Fetch subjects
	public function fetch($gateway) {
		$this->list = [];
		
		// Parse subjects
		$view = $gateway->fetch($gateway::host.'?state=verpublish&publishContainer=stgPlanList');
		foreach($view->query('//table/tbody/tr') as $index => $row) {
			
			// Parse subject details
			$name = $view->query('td[1]', $row)->item(0)->nodeValue;
			$href = $view->query('td[2]/a', $row)->item(0)->getAttribute('href');
			preg_match('/k_parallel\.parallelid=([0-9]+)&k_abstgv\.abstgvnr=([0-9]+)/', $href, $id);
			
			// List subject
			$this->list[] = [
				'id' => $id[2],
				'parallelid' => $id[1],
				'name' => $name
			];
		}
	}
	
	// Write subjects
	public function write($db) {
		
		// Add new subject or update existing
		foreach($this->list as $subject) $db->query('
			INSERT INTO subjects (id, parallelid, name) 
			VALUES (:id, :parallelid, :name) 
			ON DUPLICATE KEY UPDATE parallelid = :parallelid, name = :name
		', $subject);
		
		// Delete removed subjects
		$placeholders = implode(', ', array_fill(0, $this->length(), '?'));
		$db->query('DELETE FROM subjects WHERE id NOT IN ('.$placeholders.')', array_column($this->list, 'id'));
	}
}