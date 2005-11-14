<?php
	class IRCUser {
		private $server	= null;
		
		public $nick	= '';
		public $username= '';
		public $host	= '';
		
		public $chans	= array();
		
		public function __construct($server, $chan) {
			$this->server	= $server;
			$this->name		= $chan;
		}
		
		public function sendMsg($msg) {
			$this->server->put("PRIVMSG {$this->nick} :$msg");	
		}
		
		public function addChan($chan, $mode) {
			$this->chans[$chan] = $mode;
		}
		
		public function delChan($chan) {
			unset($this->chans[$chan]);
			if(count($this->chans) == 0) {
				$this->server->users->delUsers($this->nick);
			}
		}
		
		/*
		 * 	Static 
		 */
		 
		static function SeparateNickAndMode($nick) {
			$car1 = substr($nick, 0, 1);
			
			if($car1 == '@' or $car1 == '+' or $car1 == '#') {
				$mode = $car1;
				$nick = substr($nick, 1);
			} else {
				$mode = '';	
			}
			
			return array($mode, $nick);
		}
	}
?>
