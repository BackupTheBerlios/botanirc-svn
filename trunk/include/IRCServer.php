<?PHP
	Class IRCServer {
		// Static
		static public	$instance = array();
		static private	$MyIP = '';
		
		// temps avant déconnexion (secondes)
		public $socketTimeout = 280;
		
		// Socket handle
		protected $C;
		
		// Params
		public $address		= '';
		public $port		= 0;
		public $nick		= '';
		public $domain		= '';
		public $performs	= array();
		
		// *******
		public $chans		= null;
		public $users		= null;
		
		private function __construct() {
			$this->chans = new IRCChansCollection($this);
			$this->users = new IRCUsersCollection($this);
		}
		
		private function __destruct() {
			$this->disconnect();
		}
		
		public function Init($address, $port, $nick = 'PHPbot', $domain = 'none', $chans = '', $performs = '') {
			if(LOG) {
				$this->log = new Log(LOG_OUTPUT, 'Conn.' . $address, LOG_WRITE_TYPE);
			}
			
			$this->address	= $address;
			$this->port		= $port;
			$this->nick		= $nick;
			$this->domain	= $domain;
			$this->performs	= (array) $performs;
			
			if(LOG) $this->log->add(	array(	"Initialisation d'une nouvelle connexion",
													"Serveur: $address:$port",
													"Nick: {$this->nick}",
													"Domain: $domain",
													'Performs: ' . implode(', ', $this->performs),
												)
										);
		}
	
		private function newNick() {
			$this->nick .= '_';
		}
	
		public function get() {
			$content = trim(fgets($this->C, 1024));
			
			if($content != ''){
				if(LOG) $this->log->add("Message recu: <$content>");
				
				$msg = new IRCMessage($this->address, $content);
				if(LOG) $this->log->add('Objet Message: ' . print_r($msg, true));
				
				$method = 'on' . $msg->command;				
		 		if(method_exists($this, $method)) {
		 			$this->$method($msg);
		 		}
				
				return $msg;
			} else {
				return false;	
			}
		}
		
		/*
		 * 	fonction publique
		 */
	
		public function connect() {
			$errno	= 0;
			$errstr	= '';
			$this->C = @fsockopen($this->address, $this->port, $errno, $errstr, 10);
			stream_set_timeout($this->C, $this->socketTimeout);
			stream_set_blocking($this->C, 0);
	
			if(!$this->C) {
				if(LOG) $this->log->add('Impossible de se connecter au server ' . $this->address);
				
				return false;
			} else {
				// User
				$this->put('USER ' . $this->nick . ' ' . $this->nick . '@' . IRCServer::GetMyIP() . ' ' . $this->domain . ' :' . $this->nick);
		
				// Nick
				$this->put('NICK ' . $this->nick);
				
				return true;
			}
		}
		
		public function disconnect() {
			@fclose($this->C);
		}
	
		public function joinChan($chan) {
			$this->put('JOIN '.$chan);
		}
	
		public function partChan($chan) {
			$this->put('PART '.$chan);
		}
	
		public function put($command) {
			if(LOG) $this->log->add("Message envoyé: <$command>");
		
			fputs($this->C, $command . "\n");
		}
		
		private function parseCommand($command) {
			$search	= array('/msg',		'/query',	'/join');
			$replace= array('PRIVMSG',	'PRIVMSG',	'JOIN');
			
			$this->put(str_replace($search, $replace, $command));
		}

		/*
		 *	Commandes IRC de bases 
		 */
		
		// Welcome
		private function on001(IRCMessage $msg) {
			foreach($this->performs as $perform) {
				$this->parseCommand($perform);
			}
		}
		
		private function on332(IRCMessage $msg) {
			$this->chans->getChanByName($msg->chan)->topic = $msg->msg;
		}
		
		// Liste des users du chan qu'on vient de rejoindre
		private function on353(IRCMessage $msg) {
			$nicks = array();
			$nicks = split(' ', $msg->msg);
			
			foreach($nicks as $nick) {
				list($mode, $nick) = IRCUser::SeparateNickAndMode($nick);
				
				if($nick != $this->nick) {
					$this->chans->getChanByName($msg->chan)->addUser($nick, $mode);
					
					if(!$this->users->exists($nick)) {
						$this->users->addUser($nick);
					}
					$this->users->getUserByNick($nick)->addChan($msg->chan, $mode);
				}
			}
		}
	
		// Charactères illégaux ds le nick
		private function on432(IRCMessage $msg) {
			$this->nick = 'PHPboT';
			$this->put('NICK :' . $this->nick);
		}
	
		// Nick déjà utilisé
		private function on433(IRCMessage $msg) {			
			$this->put('PRIVMSG nickserv recover ' . $this->nick . ' ' .BOT_PWD);
			$this->put('NICK :' . $this->nick);
			$this->put('PRIVMSG nickserv identify '.BOT_PWD);
		}
		
		// PING
		private function onPING(IRCMessage $msg) {
			$this->put('PONG '.$msg->msg);
		}
		
		// JOIN
		private function onJOIN(IRCMessage $msg) {
			if(!$this->chans->exists($msg->chan)) {
				$this->chans->addChan($msg->chan);
			}
			
			if($this->nick != $msg->nick) {
				$this->chans->getChanByName($msg->chan)->addUser($msg->nick, '');
				
				if(!$this->users->exists($msg->nick)) {
					$this->users->addUser($msg->nick);
				}
				$this->users->getUserByNick($msg->nick)->addChan($msg->chan, '');					
			}
		}
		
		// PART
		private function onPART(IRCMessage $msg) {
			if($this->nick == $msg->nick) {
				foreach($this->chans->getChanByName($msg->chan)->users as $nick) {
					$this->users->getUserByNick($nick)->delChan($msg->chan);
				}
				
				$this->chans->delChan($msg->chan);
			} else {
				$msg->userquit = $this->users->getUserByNick($msg->nick);
				
				$this->users->getUserByNick($msg->nick)->delChan($msg->chan);					
			}
		}
		
		// QUIT
		private function onQUIT(IRCMessage $msg) {
			$msg->olduser = $this->users->getUserByNick($msg->nick);
			
			foreach($this->users->getUserByNick($msg->nick)->chans as $chan => $mode) {
				$this->chans->getChanByName($chan)->delUser($msg->nick);
			}
			$this->users->delUser($msg->nick);
		}

		// CTCP
		private function onPRIVMSG(IRCMessage $msg) {			
 			if($msg->to == $this->nick) { 				
 				if(preg_match("`^\x01(VERSION|USERINFO|CLIENTINFO)\x01$`", $msg->msg)) {
					$this->put('NOTICE ' . $msg->nick . ' PHPboT version ' . $this->main->version.' - PHP '.phpversion().' -- par Matt.Rixx');
				
 				} elseif (preg_match("`(PING)`", $msg->msg)) {
 					$this->put('NOTICE ' . $msg->nick . " PING\1" . time() . "\1");

 				} elseif (preg_match("`(TIME)`", $msg->msg)) {
 					$this->put('NOTICE ' . $msg->nick . ' ' . date('Y-m-d H:i:s'));
 				}
 			}
		}
	
		// KICK
		private function onKICK(IRCMessage $msg) {
			if($msg->to == $this->nick) {
				foreach($this->chans->getChanByName($msg->chan)->users as $nick) {
					$this->users->getUserByNick($nick)->delChan($msg->chan);
				}
				
				$this->chans->delChan($msg->chan);
				
				sleep(1);
				$this->joinChan($msg->chan);
				$this->put('PRIVMSG ' . $msg->chan . ' :Merci ' . $msg->nick . ' !');
			} else {
				$msg->olduser = $this->users->getUserByNick($msg->nick);
				
				$this->users->getUserByNick($msg->to)->delChan($msg->chan);
			}
		}
		
		// ERROR
		private function onERROR(IRCMessage $msg) {
			$this->disconnect();
			sleep(3);
			$msg = null;
		}
	
		// NICK
		private function onNICK(IRCMessage $msg) {
			if($msg->nick == $this->nick) {
				//TODO: gérer le changement de nick du bot
			} else {
				$msg->olduser = $this->users->getUserByNick($msg->nick);
			
				foreach($this->users->getUserByNick($msg->nick)->chans as $chan => $mode) {
					$this->chans->getChanByName($chan)->delUser($msg->nick);
					list($mode, $nick) = IRCUser::SeparateNickAndMode($msg->nick);
					$this->chans->getChanByName($chan)->addUser($nick, $mode);
				}
				
				$this->users->getUserByNick($msg->nick)->nick = $msg->to;
			}
		}
		
		/*
		 *	Fonctions statiques
		 */
		
		static function GetMyIP() {
			if(IRCServer::$MyIP == '') {
				IRCServer::$MyIP = file_get_contents('http://tools.mattlab.com/ip/');
			}
			return IRCServer::$MyIP;
		}
		
		static function GetInstance($address) {
			if(!IRCServer::$instance[$address]) {
				IRCServer::$instance[$address] = new IRCServer();
			}
			return IRCServer::$instance[$address];
		}
	}
?>