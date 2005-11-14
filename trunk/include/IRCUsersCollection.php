<?php
	class IRCUsersCollection {
		private $server	= null;
		
		private $users	= array();
		
		public function __construct($server) {
			$this->server = $server;
		}
		
		public function addUser($nick) {
			$this->users[$nick] = new IRCUser($this->server, $nick);
		}
		
		public function delUser($nick) {
			unset($this->users[$nick]);
		}
		
		public function getUserByNick($nick) {
			return $this->exists($nick) ? $this->users[$nick] : false;	
		}
		
		public function exists($nick) {
			return isset($this->users[$nick]);	
		}
		
		public function getListAllUsers() {
			return array_keys($this->users);	
		}
	}
?>