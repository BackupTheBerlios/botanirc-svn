<?php 
class plug_reload implements plugin {

	private $main;
	private $password;

	public function __construct($main) {
		$this->main = $main;
		$this->password = BOT_PWD;
	}

	public function start($IRCtxt) {
		$this->reload($IRCtxt);
	}
	
	public function help() {
		
	}

	private function reload($IRCtext) {
 		$T = array();
		
		if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.preg_quote(IRCConn::$myBotName, '`').'(.*?)'.$this->password.' (RELOAD)`i', $IRCtext, $T)) {
			die();
		}
	}
}
?>