<?php
	class mirclog implements plugin {
		public $LogsFile;
		public $LogsDir = 'logs';
		
		public $main;
		
		
		
		public function __construct($main) {
			$this->main = $main;
		}
		
		public function run(IRCMessage $msg) {
			$method = 'on' . $msg->command;				
	 		if(method_exists($this, $method)) {
	 			$this->$method($msg);
	 		}
		}
		
		private function addLog($chan, $msg) {
			$msg = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
			
			$this->LogsFile = date('Y-m-d') . '-' . $chan . '.log';
			$file = $this->LogsDir . '/' . $this->LogsFile;
			$fp = fopen($file, 'a+');
			fwrite($fp, $msg ."\n");
			fclose($fp);
		}
			

		/*
		 *	Commandes IRC 
		 */
		

		// PRIVMSG
		private function onPRIVMSG(IRCMessage $msg) {			
 			if(!empty($msg->chan)) {
				$txt = '<' . $msg->nick . '> ' . $msg->msg;
				$this->addLog($msg->chan, $txt);
 			}
		}
		
		// JOIN
		private function onJOIN(IRCMessage $msg) {
			$txt = '*** ' . $msg->nick . ' has joined ' . $msg->chan;
			$this->addLog($msg->chan, $txt);
		}
		
		// PART
		private function onPART(IRCMessage $msg) {
			$txt = '*** ' . $msg->nick . ' has left ' . $msg->chan;
			$this->addLog($msg->chan, $txt);
		}
		
		// QUIT
		private function onQUIT(IRCMessage $msg) {
			foreach($msg->olduser->chans as $chan => $mode) {
				$txt = '*** ' . $msg->nick . ' has left IRC (' . $msg->msg . ')';
				$this->addLog($chan, $txt);
			}
		}
		
		// NICK
		private function onNICK(IRCMessage $msg) {
			foreach($msg->olduser->chans as $chan => $mode) {
				$txt = '*** ' . $msg->nick . ' changed nick to ' . $msg->to;
				$this->addLog($chan, $txt);
			}
		}
	
		// KICK
		private function onKICK(IRCMessage $msg) {
			$txt = '*** ' . $msg->nick . ' has kicked ' . $msg->to;
			$this->addLog($msg->chan, $txt);
		}
		
		public function help(){}
	}
?>