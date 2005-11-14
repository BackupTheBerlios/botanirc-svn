<?php

Class IRCLog {

	static $MaxSize;
	static $chan;
		
	public $LogsFile;
	public $LogsDir = 'logs';
	
	static private $instance = FALSE;

	private function __construct() {
		$this->LogsFile = date('Y-m-d') .'-'.self::$chan.'.log';
		if(!is_dir($this->LogsDir)) {
			@mkdir($this->LogsDir);
		}
	}
	static function Init($chan, $MaxSize ) {
		self::$MaxSize = $MaxSize;
		self::$chan = $chan;	
	}
	
	static function GetInstance() {
		if(!IRCLog::$instance) { IRCLog::$instance = new IRCLog(); }
		return IRCLog::$instance;
	}

	function Log($IRCtext) {
		$T = array();
		
		if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.self::$chan.' :(.*?)\r?\n`i', $IRCtext, $T)) {
			$log = '['.date('Y-m-d H:i:s').'] <'.$T[1].'> ' .$T[2];
			$this->Add($log);
		}
		if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.self::$chan.' :ACTION (.*?)\r?\n`i', $IRCtext, $T)) {
			$log = '['.date('Y-m-d H:i:s').'] * '.$T[1].' ' .$T[2];
			$this->Add($log);
		}
		if(preg_match('`^:(.*?)!.*?@.*? JOIN :(.*?)\r?\n`i', $IRCtext, $T)) {
			$log = '['.date('Y-m-d H:i:s').'] *** '.$T[1].' has joined ' .$T[2];
			$this->Add($log);
		}
		if(preg_match('`^:(.*?)!.*?@.*? PART '.self::$chan.' :(.*?)\r?\n`i', $IRCtext, $T)) {
			$log = '['.date('Y-m-d H:i:s').'] *** '.$T[1].' has left ' .$T[2];
			$this->Add($log);
		}
		if(preg_match('`^:(.*?)!.*?@.*? QUIT :(.*?)\r?\n`i', $IRCtext, $T)) {
			$log = '['.date('Y-m-d H:i:s').'] *** '.$T[1].' has left IRC ' .$T[2];
			$this->Add($log);
		}
		if(preg_match('`^:(.*?)!.*?@.*? NICK :(.*?)\r?\n`i', $IRCtext, $T)) {
			$log = '['.date('Y-m-d H:i:s').'] *** '.$T[1].' changed nick to ' .$T[2];
			$this->Add($log);
		}
		if(preg_match('`^:(.*?)!.*?@.*? KICK '.self::$chan.' ([^ ]+)`i', $IRCtext, $T)) {
			$log = '['.date('Y-m-d H:i:s').'] *** '.$T[1].' has kicked ' .$T[2];
			$this->Add($log);
		}
	}

	function Add($text, $isEvenement=false) {
		$this->LogsFile = date('Y-m-d') .'-'.self::$chan.'.log';
		$file = $this->LogsDir . '/' . $this->LogsFile;
		$handle = fopen($file, 'a+');
		fwrite($handle, $text ."\n");
		fclose($handle);
	}
}

?>