<?php
/*!
 * Luniverse Elements v1.7
 * PHP micro-templating engine
 * Licensed under the MIT license
 * Copyright (c) 2019 Lukas Jans
 * https://github.com/luniverse/elements-php
 */
class Elements {
	
	// Enclosing delimiters
	public static $open = '{{', $close = '}}';
	
	// Include path
	public static $path = '';
	
	// Global data
	public static $data;
	
	// Render direct input
	public static function render($template, $data=NULL) {
		
		// Use stored data if nothing is provided
		if(is_null($data)) $data = self::$data;
		
		// Include partials
		$template = self::renderPartials($template, $data);
		
		// Render sections and variables recursive
		$template = self::renderElement($template, $data);
		
		// Return cleaned template
		return self::clean($template);
	}
	
	// Render from file
	public static function renderFile($name='index', $data=NULL) {
		return self::render(file_get_contents(self::$path.$name.'.html'), $data);
	}
	
	// Check whether data is a hash
	private static function is_hash($data) {
		
		// Native object
		if(is_object($data)) return true;
		
		// Array with string keys
		if(is_array($data) && count(array_filter(array_keys($data), 'is_string')) > 0) return true;
		return false;	
	}
	
	// Clean comments and whitespace
	private static function clean($template) {
		$template = preg_replace('/'.self::$open.'!.+?'.self::$close.'/s', '', $template);
		return str_replace(["\r", "\n", "\t"], '', $template);
	}
	
	// Render item
	private static function renderElement($template, $element, $context=[]) {
		
		// Call lambda
		if(is_callable($element)) return call_user_func($element, $template);
		
		// Render content with hash
		if(self::is_hash($element)) return self::renderRecursive($template, $element);
		
		// Render list
		if(is_array($element)) return self::renderList($template, $element);
		
		// Adopt rendered content with local placeholder
		return self::renderRecursive($template, ['.' => $element]);
	}
	
	// Recursive renderer
	private static function renderRecursive($template, $data) {
		$template = self::renderVariables($template, $data);
		$template = self::renderSections($template, $data);
		return $template;
	}
	
	// Map list on template
	private static function renderList($template, $list) {
		$result = '';
		foreach($list as $element) $result.= self::renderElement($template, $element, $list);
		return $result;
	}
	
	// Render sections like {{#|^key}}content{{/key}}
	private static function renderSections($template, $data) {
		
		// Match regex
		$regex = '/'.self::$open.'(\^|#)(.+?)'.self::$close.'(.+?)'.self::$open.'\/\2'.self::$close.'/s';
		return preg_replace_callback($regex, function($match) use ($data) {
			
			// Decompose match
			list($raw, $type, $key, $content) = $match;
			
			// Determine value
			if(is_array($data) && array_key_exists($key, $data)) $value = $data[$key];
			if(is_object($data) && property_exists($data, $key)) $value = $data->$key;

			// Render value for normal section with non-empty value
			if($type == '#' && !empty($value)) return self::renderElement($content, $value, $data);

			// Adopt content for inverted section with empty value
			if($type == '^' && empty($value)) return self::renderRecursive($content, $data);
			
			// (Skip content in other cases)
		}, $template);
	}
	
	// Render variables like {{key.sub}}	
	private static function renderVariables($template, $data, $prefix='') {

		// Render only size of lists
		if(!self::is_hash($data)) $data = ['length' => count($data)];

		// Iterate over data
		foreach($data as $key => $value) {
			
			// Render scalar value
			if(is_scalar($value)) $template = str_replace(self::$open.$prefix.$key.self::$close, $value, $template);
			
			// Render next dimension of hash with prefix
			elseif(!is_null($value)) $template = self::renderVariables($template, $value, $prefix.$key.'.');
		}
		return $template;
	}
	
	// Render partials like {{> form}}
	private static function renderPartials($template, $data) {
		
		// Match regex
		$regex = '/'.self::$open.'> ?(.+?)'.self::$close.'/';
		return preg_replace_callback($regex, function($match) use ($data) {
			
			// Include rendered partial
			return self::renderFile('_'.$match[1], $data);
		}, $template);
	}
}