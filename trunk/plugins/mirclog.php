<?php
	class mirclog implements PluginInterface {
		public $LogsFile;
		public $LogsDir = 'logs';
		
		public $main;
		
		// tableau contenant les chans pour lesquels la session a déjà commencé
		// clef = chan
		// valeur = fichier
		// exemple: '#mon_chan' => '2005-12-21-#mon_chan.log'
		private $sessions = array();
		
		
		
		public function __construct($main) {
			$this->main = $main;
		}
		
		public function __destruct() {
			foreach($this->sessions as $file) {
				$txt = 'Session Close: ' . date('D M d H:i:s Y');
				
				$fp = fopen($file, 'a+');
				fwrite($fp, $txt ."\n");
				fclose($fp);
			}
		}
		
		public function run(IRCMessage $msg) {
			$method = 'on' . $msg->command;				
	 		if(method_exists($this, $method)) {
	 			$this->$method($msg);
	 		}
		}
		
		private function addLog($chan, $msg) {
			// horodatage du message à écrire
			$msg = '[' . date('H:i:s') . '] ' . $msg;
			
			// création du nom de fichier en fonction de la date du jour
			// et du nom du chan
			$this->LogsFile = date('Y-m-d') . '-' . $chan . '.log';
			$file = $this->LogsDir . '/' . $this->LogsFile;
			
			// ouverture du fichier
			$fp = fopen($file, 'a+');
			
			if(!isset($this->sessions[$chan])) {
				// si on a jamais écris pour ce chan (1er lancement du bot)
				$txt = 'Session Start: ' . date('D M d H:i:s Y');
				fwrite($fp, $txt ."\n");
				$this->sessions[$chan] = $file;
			} elseif($this->sessions[$chan] != $file) {
				// si on change de fichier de log
				$txt = 'Session Time: ' . date('D M d H:i:s Y');
				fwrite($fp, $txt ."\n");
				$this->sessions[$chan] = $file;
			}
			
			// écriture du message
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
			if($msg->nick == $this->main->servers[$msg->address]->nick) {
				$txt = '*** Now talking in ' . $msg->chan;
				$this->addLog($msg->chan, $txt);
			} else {
				$txt = '*** ' . $msg->nick . ' (' . $msg->from . ') has joined ' . $msg->chan;
				$this->addLog($msg->chan, $txt);
			}
		}
		
		// PART
		private function onPART(IRCMessage $msg) {
			$txt = '*** ' . $msg->nick . ' (' . $msg->from . ') has left ' . $msg->chan;
			$this->addLog($msg->chan, $txt);
			
			if($msg->nick == $this->main->servers[$msg->address]->nick) {
				unset($this->sessions[$msg->chan]);
			}
		}
		
		// QUIT
		private function onQUIT(IRCMessage $msg) {
			foreach($msg->olduser->chans as $chan => $mode) {
				$txt = '*** ' . $msg->nick . ' (' . $msg->from . ') has left IRC (' . $msg->msg . ')';
				$this->addLog($chan, $txt);
			
				if($msg->nick == $this->main->servers[$msg->address]->nick) {
					unset($this->sessions[$chan]);
				}
			}
		}
		
		// NICK
		private function onNICK(IRCMessage $msg) {
			foreach($msg->olduser->chans as $chan => $mode) {
				$txt = '*** ' . $msg->nick . ' is now known as ' . $msg->to;
				$this->addLog($chan, $txt);
			}
		}
	
		// KICK
		private function onKICK(IRCMessage $msg) {
			$txt = '*** ' . $msg->nick . ' has kicked ' . $msg->to;
			$this->addLog($msg->chan, $txt);
		}
		
		// MODE
		private function onMODE(IRCMessage $msg) {
			$txt = '*** ' . $msg->nick . ' sets mode: ' . $msg->msg;
			$this->addLog($msg->chan, $txt);
		}
		
		public function help(){}
	}
?>