<?php
	class IRCUser {
		private $server	= null;
		
		public $nick	= '';
		public $username= '';
		public $host	= '';
		
		public $chans	= array();
		
		public function __construct($server, $nick) {
			$this->server	= $server;
			$this->nick		= $nick;
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
				$this->server->users->delUser($this->nick);
			}
		}
		
		/*
		 * 	Static 
		 */
		 
		static function SeparateNickAndMode($nick) {
			//$car1 = substr($nick, 0, 1);
			$modes = array('@', '+');
			
			if(in_array($nick[0],  $modes)) {
				$mode = $nick[0];
				$nick = substr($nick, 1);
			} else {
				$mode = '';	
			}
			
			return array($mode, $nick);
		}
	}
?>
