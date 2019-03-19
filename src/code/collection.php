<?php abstract class Collection {
	protected $list;
	
	public abstract function fetch($gateway);
	public abstract function write($db);
	
	public function list() {
		return $this->list;
	}
	
	public function length() {
		return count($this->list);
	}
}