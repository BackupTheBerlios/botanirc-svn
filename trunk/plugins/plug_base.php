<?php
	class plug_base implements plugin {
		
		private $main;
		
		private $address	= '';
		private $curServer	= '';
		
		
		public function __construct($main) {
			if(LOG) {
				$this->log = new Log(LOG_OUTPUT, 'Plug_base', LOG_WRITE_TYPE);
			}
			
			$this->main = $main;
		}
		
		public function start(IRCMessage $msg) {
			if(LOG) {
				$this->log->add('Serveur: ' . $msg->adress);
				$this->log->add('Raw: <' . $msg->raw . '>');	
			}
			$this->address		= $msg->address;
			$this->curServer	= &$this->main->servers[$this->address];
			
			$method = 'cmd' . $msg->command;
	 		if(method_exists($this, $method)) {
	 			$this->$method($msg);
	 		}	 		
		}
		
		private function cmd001(IRCMessage $msg) {
			foreach($this->curServer->chans as $chan) {
				$this->curServer->joinChan($chan);
			}
			// TODO: récupérer la liste des users du chan !	
		}
		
		private function cmdPING(IRCMessage $msg) {
			$this->curServer->put('PONG '.$msg->msg);
		}

		// CTCP
		private function cmdPRIVMSG(IRCMessage $msg) {			
 			if($msg->to == $this->curServer->nick) {
 				if(LOG) $this->log->add($msg->msg);
 				
 				if(preg_match("`(VERSION|USERINFO|CLIENTINFO)`", $msg->msg)) {
					$this->curServer->put('NOTICE ' . $msg->nick . ' PHPboT version ' . $this->main->version.' - PHP '.phpversion().' -- par Matt.Rixx');
				
 				} elseif (preg_match("`(PING)`", $msg->msg)) {
 					$this->curServer->put('NOTICE ' . $msg->nick . " PING\1" . time() . "\1");

 				} elseif (preg_match("`(TIME)`", $msg->msg)) {
 					$this->curServer->put('NOTICE ' . $msg->nick . ' ' . date('Y-m-d H:i:s'));
 				}
 			}
		}
	
		// KICK
		private function cmdKICK(IRCMessage $msg) {
			if($msg->to == $this->curServer->nick) {
				sleep(1);
				$this->curServer->joinChannel($msg->chan);
				$this->curServer->put('PRIVMSG ' . $msg->chan . ' :Merci ' . $msg->nick . ' !');
			}
		}
	
		// Charctères illégaux ds le nick
		private function cmd432(IRCMessage $msg) {
			$this->curServer->nick = 'PHPboT';
			$this->curServer->put('NICK :' . $this->curServer->nick);
		}
	
		// Nick déjà utilisé
		private function cmd433(IRCMessage $msg) {			
			$this->curServer->put('PRIVMSG nickserv recover ' . $this->curServer->nick . ' ' .BOT_PWD);
			$this->curServer->put('NICK :' . $this->curServer->nick);
			$this->curServer->put('PRIVMSG nickserv identify '.BOT_PWD);
		}
		
		/*
		private function reloadNick($IRCtext) {
			if($this->main->servers[$this->currentServer]->nick != BOT_NICK) {
				//$this->main->servers[$this->currentServer]->conn->put('NICK :'.BOT_NICK);
			}
		}
		*/
		public function help() {
		}
	}
?>