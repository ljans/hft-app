<?php namespace Collection;
class Courses extends \Collection {
	private $subject;
	
	// Constructor
	public function __construct($subject) {
		$this->subject = $subject;	
	}
	
	// Fetch courses
	public function fetch($gateway) {
		$this->list = [];
		
		// Parse courses
		$view = $gateway->fetch($gateway::host.'?state=wplan&act=stg&show=liste&P.Print&k_abstgv.abstgvnr='.$this->subject['id'].'&k_parallel.parallelid='.$this->subject['parallelid']);
		foreach($view->query('//table/tr[position() > 1]/td[2]/a') as $link) {
			
			// Parse course details
			preg_match('/publishid=([0-9]+)/', $link->getAttribute('href'), $id);
			$this->list[] = [
				'id' => $id[1],
				'subject' => $this->subject['id'],
				'title' => $link->nodeValue
			];
		}
	}
	
	// Write courses
	public function write($db) {
		
		// Add new courses or update existing
		foreach($this->list as $course) $db->query('
			INSERT INTO courses (subject, id, title) 
			VALUES (:subject, :id, :title) 
			ON DUPLICATE KEY UPDATE title = :title
		', $course);
	
		// Delete removed courses
		if($this->length() > 0) {
			$placeholders = implode(', ', array_fill(0, $this->length(), '?'));
			$reference = array_column($this->list(), 'id');
			$reference[] = $this->subject['id'];
			$db->query('DELETE FROM courses WHERE id NOT IN ('.$placeholders.') AND subject = ?', $reference);
		} else $db->query('DELETE FROM courses WHERE subject = ?', $this->subject['id']);
	}
}