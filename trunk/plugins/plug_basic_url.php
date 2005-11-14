<?php

class plug_basic_url {
	private $main;

	public function __construct($main) {
		$this->main = $main;
	}
	public function start($IRCtext) {
		$this->reply($IRCtext);
	}
	
	// HELP
	private function reply($IRCtext) {
 		$reg = array();

		if( preg_match('`!mysql ([^ ]+)`',$this->main->Plist['plug_main_tools']->msg, $reg ) ) {
			$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'http://www.mysql.com/search/index.php?q='.urlencode($reg[1]).'&lang=fr' );
		}
	
		/*
		if( preg_match('`!google ((com|fr) )?([^ ]+)`',$this->main->Plist['plug_main_tools']->msg, $reg ) ) {
			$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'http://www.google.'.urlencode($reg[2]).'/search?hl=fr&ie=UTF-8&q='.urlencode($reg[3]).'&btnG=Recherche+Google&meta=' );
		}
		*/
	}
	
	public function help() {
		return " !mysql votre recherche -> Résultat de votre recherche sur mysql.com\n";
	}

}

?>