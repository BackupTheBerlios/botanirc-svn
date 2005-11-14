<?php

class plug_BotSpeek {
	private $main;
	private $funcArray;
	private $plugHelp = array();
	private $magicnumber = 000;

	public function __construct($main) {
		$this->main = $main;
	}
	public function start($IRCtext) {
		$this->BotSpeek($IRCtext);
	}

	// BotSpeek
	private function BotSpeek($IRCtext) {
 		$T = array();

		if(preg_match('`!BotSpeek '.$this->magicnumber.' /notice (.+)`i', $this->main->Plist['plug_main_tools']->msg, $T)) {
			$this->main->Plist['plug_main_tools']->Notice( IRCConn::$channel , ' '.$T[1] );
			return TRUE;
		}

		if(preg_match('`!BotSpeek '.$this->magicnumber.' /me (.+)`i', $this->main->Plist['plug_main_tools']->msg, $T)) {
			#$this->main->Plist['plug_main_tools']->sendMsg( IRCConn::$channel , '/me '.$T[1] );
			$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , '/me '.$T[1] );
			return TRUE;
		}

		if(preg_match('`!BotSpeek '.$this->magicnumber.' (.+)`i', $this->main->Plist['plug_main_tools']->msg, $T)) {
			#$this->main->MyConn->put('PRIVMSG '.IRCConn::$channel.'  http://php.net/'.$T[1]);
			$this->main->Plist['plug_main_tools']->sendMsg( IRCConn::$channel , ' '.$T[1] );
			return TRUE;
		}
	}

}

?>