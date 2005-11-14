<?php
	class IRCChansCollection {
		private $server	= null;
		
		private $chans	= array();
		
		public function __construct($server) {
			$this->server = $server;
		}
		
		public function addChan($chan) {
			$this->chans[$chan] = new IRCChan($this->server, $chan);
		}
		
		public function delChan($chan) {
			unset($this->chans[$chan]);
		}
		
		public function getChanByName($chan) {
			return $this->exists($chan) ? $this->chans[$chan] : false;	
		}
		
		public function exists($chan) {
			return isset($this->chans[$chan]);	
		}
		
		public function getListAllChans() {
			return array_keys($this->chans);	
		}
	}
?>