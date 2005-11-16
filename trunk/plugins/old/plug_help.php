<?php

class plug_help {
	private $main;
	private $funcArray;
	private $plugHelp = array();

	public function __construct($main) {
		$this->main = $main;
		$this->loadPHPDoc();
		$this->makePluginHelp();
	}
	public function start($serveur, $IRCtext) {
		$this->sendHelp($IRCtext);
		$this->loadPluginSyntax();
		$this->unloadPluginSyntax();
		$this->listPluginSyntax();
		$this->PHPDoc($IRCtext);
	}

	// HELP
	private function sendHelp($IRCtext) {
		if( trim($this->main->Plist['plug_main_tools']->msg) == '!help' ) {
			#echo "->helptest!\n";
			$this->main->MyConn->put('PRIVMSG '.$this->main->Plist['plug_main_tools']->from.'  !help -> Cette aide - Notice des commandes du bot');
			//$this->main->MyConn->put('PRIVMSG '.$this->main->Plist['plug_main_tools']->from.'  !wall -> URL vers THE WalL');
			//$this->main->MyConn->put('PRIVMSG '.$this->main->Plist['plug_main_tools']->from.'  !wall X -> URL vers le WalL n° X');
			$this->main->MyConn->put('PRIVMSG '.$this->main->Plist['plug_main_tools']->from.'  !php fonction -> URL vers la documentation de la fonction sur php.net');
			#$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'Testage' );
			$this->sendPluginHelp($this->main->Plist['plug_main_tools']->from);
		}
	}

	// Help PHP
	private function PHPDoc($IRCtext) {
 		$T = array();
		
		if(preg_match('`!php +(\w+)( *)`i', $this->main->Plist['plug_main_tools']->msg, $T)) {
			$T[1] = str_replace(array('::' , '->', '__'), array('-', '-', ''), $T[1]);
			if(in_array($T[1], $this->funcArray)) {
				$this->main->MyConn->put('PRIVMSG '.IRCConn::$channel.'  http://php.net/'.$T[1]);
			}
		}
	}

	private function loadPHPDoc() {
		$xmlFunctionsListFromCVS = 'http://cvs.php.net/co.php/phpdoc/funcindex.xml';
		$xmlFunctionsListFromLocalhost = 'phpfunctions.xml';
		if($FL= simplexml_load_file($xmlFunctionsListFromLocalhost)) {
			foreach($FL->index->indexdiv as $func) {
				foreach($func->indexentry as $function) {
					$this->funcArray[] = str_replace(array('::' , '->', '__'), array('-', '-', ''), (string)$function->primaryie->function);
				}
			}
		}
	}

	public function makePluginHelp() {
		$this->plugHelp = array();
		foreach ($this->main->Plist as $Plug) {
			if ( method_exists ( $Plug, 'help') ) {
				$this->plugHelp[] = $Plug->help();
			}
		}
	}

	private function sendPluginHelp($to) {
		#var_dump( $this->plugHelp);
		$linee = '--------------------------------------';
		foreach ($this->plugHelp as $val ) {
			$txt = explode ( "\n", $val);
			foreach ( $txt as $line ) {
				$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , $line );
			}	
			if(!empty($txt)) {
				$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , $linee );
			}
		}
	}

	public function help() {
		//return "!help :l'aide.";
	}


	# chargement dynamique des modules
	function loadPluginSyntax() {
 		$reg = array();
		
		if( preg_match('`!addplug ('.BOT_PWD.') ([^ ]+)`',$this->main->Plist['plug_main_tools']->msg, $reg ) ) {
			$this->loadPlugin($reg[2]);
		}
	}

	private function loadPlugin($name) {
		$path = './plugin/'.$name.'.php';
		if (!file_exists($path)) {
			$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'Plugin '.$name.' non disponible.' );
			return FALSE;
		}
		if (array_key_exists ( $name, $this->main->Plist )) {
			$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'Plugin '.$name.' deja chargé.' );
			return FALSE;
		}
		require_once($path);
		$this->main->AddPlug($name);
		$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'Plugin '.$name.' chargé' );
	}

	# dechargement dynamique des modules
	function unloadPluginSyntax() {
 		$reg = array();
		
		if( preg_match('`!ulplug ('.BOT_PWD.') ([^ ]+)`',$this->main->Plist['plug_main_tools']->msg, $reg ) ) {
			$this->unloadPlugin($reg[2]);
		}
	}

	private function unloadPlugin($name) {
		if (!array_key_exists ( $name, $this->main->Plist ) ) {
			$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'Plugin '.$name.' non chargé.' );
			return FALSE;
		}
		$this->main->UnloadPlug($name);
		$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'Plugin '.$name.' déchargé' );
	}

	# list des plugin
	function listPluginSyntax() {
		if( preg_match('`!plug`',$this->main->Plist['plug_main_tools']->msg ) ) {
			$this->listPlugin();
			#$this->loadedPlugin();
		}
	}


	#liste des modules disponibles
	private function listPlugin() {
		$dc = get_declared_classes();
		foreach ( $dc as $val ) {
			if (strpos($val,'plug_')===0) {
				$out = "Module $val";
				if (array_key_exists ( $val, $this->main->Plist ) ) {
					$out .= ", chargé.";
				} else {
					$out .= ", disponible.";
				}
				$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , $out );
			}
		}

	}

	#liste des modules chargés
	private function loadedPlugin() {
		foreach ( $this->main->Plist as $key=>$val ) {
			$this->main->Plist['plug_main_tools']->sendMsg( $this->main->Plist['plug_main_tools']->from , 'module :'.$key.' chargé' );
		}
	}
}

?>
