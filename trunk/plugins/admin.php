<?php
	class admin implements PluginInterface {
		private $main;
		
		public function __construct($main) {
			$this->main = $main;
		}
		
		public function run(IRCMessage $msg) {
			if($msg->command == 'PRIVMSG' and trim($msg->to) == $this->main->servers[$msg->address]->nick) {
				$T = array();
				preg_match('`^!(.*)(?: )?(.*)?$`', $msg->msg, $T);
				print_r($T);
				switch($T[1]) {
					case 'quit':
						die('Quit: ' . $msg->nick);
					break;
					
					case 'reboot':
						die('Reboot: ' . $msg->nick);
					break;
				}
			}
		}
			
		public function help(){}
	}
?>