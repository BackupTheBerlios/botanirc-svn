<?php

class plug_main_tools {
	private $main;
	
	public $type = FALSE;
	public $from = FALSE;
	public $msg = FALSE;
	
	
	public function __construct($main) {
		$this->main = $main;		
	}
	public function start($IRCtext) {
		$this->type = FALSE;
		$this->from = FALSE;
		$this->msg = FALSE;
		$this->PrivMsg($IRCtext);
		$this->PublicMsg($IRCtext);
		
		if ($this->msg) {
			$out = preg_replace('/[=~\/\:()"\$+*_@]/',' ',$this->msg );
			if ( @$out=iconv("UTF-8", "ISO-8859-15", $out."\n") ) {
				echo $out;
			} else {
				$this->main->MyLog->Log('erreur iconv : '.$out);
			}
		}
	}
	
	//isPRIVATE
	private function PrivMsg($IRCtext) {
 		$T = array();
		
		if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.IRCConn::$myBotName.' :(.*?)([ ]*?)\r?\n`', $IRCtext, $T)) {
			$this->type = 'PRIVATE';
			$this->from = $T[1];
			$this->msg = $T[2];
			return array( 'from' => $T[1], 'msg' => $T[2] );
		}
		return FALSE;
	}
	
	//isPUBLIC
	private function PublicMsg($IRCtext) {
 		$T = array();
		
		if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.IRCConn::$channel.' :(.*?)([ ]*?)\r?\n`', $IRCtext, $T)) {
			$this->type = 'PUBLIC';
			$this->from = $T[1];
			$this->msg = $T[2];
			return array( 'from' => $T[1], 'msg' => $T[2] );
		}
		return FALSE;
	}
	
	public function sendMsg($to,$msg) {
		$this->main->MyConn->put('PRIVMSG '.$to.' '.$msg);
		return TRUE;
	}
	
	public function Notice($to,$msg) {
		$this->main->MyConn->put('NOTICE '.$to.' '.$msg);
		return TRUE;
	}
	
	public function Action($to,$msg) {
		$this->main->MyConn->put('ACTION '.$to.' '.$msg);
		return TRUE;
	}
	
	public function help() {
		#return "aide sur le main_tools\n avec un saut de ligne\nooo:)";
	}
}

?>