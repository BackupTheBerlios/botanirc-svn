<?php
	class IRCChan {
		private $server	= null;
		
		public $name	= '';
		public $topic	= '';
		
		public $users	= array();
		
		public function __construct($server, $chan) {
			$this->server	= $server;
			$this->name		= $chan;
		}
		
		public function sendMsg($msg) {
			$this->server->put("PRIVMSG {$this->name} :$msg");	
		}
		
		public function sendAction($action) {
			$this->server->put("PRIVMSG {$this->name} :ACTION $msg");
		}
		
		public function addUser($nick, $mode) {
			$this->users[$nick] = $mode;
		}
		
		public function delUser($nick) {
			unset($this->users[$nick]);
		}
	}
?>
