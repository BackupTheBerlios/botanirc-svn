<?php
	class phpman implements PluginInterface {
		private $phpsite	= 'http://fr.php.net/';
		
		private $main = null;
		
		public function __construct($main) {
			$this->main = $main;
		}
		
		public function run(IRCMessage $msg) {
			$T = array();
			if(preg_match('`^!(?:php|phpman) ([0-9a-zA-Z\-_]+)`', $msg->msg, $T)) {
				
				$function = strtolower($T[1]);
				
				$page = file_get_contents($this->phpsite . $function);
				$page = str_replace(array("\n", "\r"), '', $page);
					
				if(preg_match('`' . $function. '</H1><DIVCLASS="refnamediv"><ANAME="(?:[A-Z0-9]+)"></A><P>(.*)</P>(.*)</DIV>`U', $page, $T)) {
					$php_version = trim($T[1]);
					$description = str_replace(array('&nbsp;', '  '), ' ', $T[2]);
					
					preg_match('`<H2>Description</H2>(.*)<BR>`U', $page, $T);
					$signature = strip_tags($T[1]);
					$signature = str_replace($function, IRC_FONT_BOLD . $function . IRC_FONT_BOLD, $signature);
					
					$this->main->servers[$msg->address]->chans->getChanByName($msg->chan)->sendMsg($description);
					$this->main->servers[$msg->address]->chans->getChanByName($msg->chan)->sendMsg($signature);
					$this->main->servers[$msg->address]->chans->getChanByName($msg->chan)->sendMsg($php_version . ' - ' . $this->phpsite . $function);
				} else {
					$this->main->servers[$msg->address]->chans->getChanByName($msg->chan)->sendMsg(IRC_FONT_BOLD . 'PHPMAN:' . IRC_FONT_BOLD . " '" . $function . "' not found");					
				}
			}
		}
		
		public function help(){}
	}
?>
