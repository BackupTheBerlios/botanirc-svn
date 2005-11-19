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
				/*
				 * supprime les caracteres de mise en forme d'irc
				 * (gras, souligné, etc...)
				 * 
				 * mais est-ce vraiment nécessaire?
				 * 
 				$search = array("`\x02(.*)\x02?`U",
 								//"`\x03[0-9]([\,][0-9])?(.*)\x03`U",
 								"`\x03(?:\d{0,2},?\d{0,2})(.*)\x03?`",
								"`\x16(.*)\x16?`U",
								"`\x1F(.*)\x1F?`U");
 				$replace = array('\1', '\1', '\1', '\1');
 				
 				$tmpmsg = preg_replace($search, $replace, $msg->msg);
 				*/
 				
 				$action = array(); 				
 				if(preg_match('`^\x01ACTION(.*)\x01$`', $msg->msg, $action)) {
					$txt = '*** ' . $msg->nick . $action[1];
 				} else {
					$txt = '<' . $msg->nick . '> ' . $msg->msg;
 				}
				
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