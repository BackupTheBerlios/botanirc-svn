<?php
	class IRCMessage {
		// adresse du server où a eu lieu la commande
		public $address	= '';
		
		// Message brut
		public $raw		= '';
		
		// user (nick!usernam@host) ou serveur
		public $from	= '';
		
		// nick!usernam@host
		public $nick	= '';
		public $username= '';
		public $host	= '';
		
		// action de la commande
		public $command	= '';
		
		// chan où a eu lieu la commande
		public $chan	= '';
		
		// nick destinataire de la commande
		public $to		= '';
		
		// commande
		public $msg		= '';
		
		// info user qui est parti
		public $olduser;
		
		
		public function __construct($address, $rawmsg) {			
			$this->address	= $address;
			$this->raw		= $rawmsg;

			$T = array();
			
			//:El_Newser!n=microtom@lns-bzn-32-82-254-16-254.adsl.proxad.net NICK :microtom
			
			if(preg_match('`^:(.*?)!(.*)?@(.*)? ([^ :]+) ([^ :]+) ([^ :]+) :(.*)$`', $this->raw, $T)) {
				$this->nick		= $T[1];
				$this->username	= $T[2];
				$this->host		= $T[3];
				$this->from		= $this->nick . '!' . $this->username . '@' . $this->host;
				
				$this->command	= $T[4];
				$this->chan		= $T[5];
				$this->to		= $T[6];
				$this->msg		= $T[7];
				
			} elseif(preg_match('`^:(.*?)!(.*)?@(.*)? ([^ :]+) ([^ :]+) :(.*)$`', $this->raw, $T)) {
				$this->nick		= $T[1];
				$this->username	= $T[2];
				$this->host		= $T[3];
				$this->from		= $this->nick . '!' . $this->username . '@' . $this->host;
				
				$this->command	= $T[4];
				
				if(substr($T[5], 0, 1) == '#') {
					$this->chan	= $T[5];
				} else {
					$this->to	= $T[5];					
				}
				
				$this->msg = $T[6];
				
			} elseif(preg_match('`^:(.*?)!(.*)?@(.*)? ([^ :]+) :?(.*)$`', $this->raw, $T)) {
				$this->nick		= $T[1];
				$this->username	= $T[2];
				$this->host		= $T[3];
				$this->from		= $this->nick . '!' . $this->username . '@' . $this->host;
				
				$this->command	= $T[4];
				
				if(substr($T[5], 0, 1) == '#') {
					$this->chan	= $T[5];
				} else {
					if($this->command == 'NICK') {
						$this->to = $T[5];
					} else {
						$this->msg	= $T[5];
					}					
				}
				
			} elseif (preg_match('`^:([^ :]+) ([0-9]{3}) ([^ :]+) (= )?([^ ]+) :(.*)$`', $this->raw, $T)) {
				$this->from		= $T[1];
				$this->command	= $T[2];
				$this->nick		= $T[3];
				if(substr($T[5], 0, 1) == '#') {
					$this->chan	= $T[5];
				} else {
					$this->to	= $T[5];					
				}
				$this->msg		= $T[6];
							
			} elseif (preg_match('`^:([^ :]+) ([0-9]{3}) ([^ :]+) :(.*)$`', $this->raw, $T)) {
				$this->from		= $T[1];
				$this->command	= $T[2];
				$this->to		= $T[3];
				$this->msg		= $T[4];
							
			} elseif (preg_match('`^:([^ :]+) ([^ :]+) ([^ :]+) (.*)$`', $this->raw, $T)) {
				$this->from		= $T[1];
				$this->command	= $T[2];
				
				if(substr($T[3], 0, 1) == '#') {
					$this->chan	= $T[3];
				} else {
					$this->to	= $T[3];					
				}
				
				$this->msg		= $T[4];

			} elseif (preg_match("`^([^ :]+) :(.*)$`i", $this->raw, $T)) {
				$this->command	= $T[1];
				$this->from		= $this->msg = $T[2];
			}
		}
	}
?>