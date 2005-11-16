<?php

class plug_hello {
	private $main;

	// SAY HELLO
	public $sayHello = 1;
	protected $hello = array('Bonjour', 'Salut', 'rahhhhh', 'Hi', 'lo', 'lu', 'hello', 'yep', 'kikoo', 'yala', '\'lut', 'bien le bonjour');
	protected $haveSayHello = array();
	protected $alreadyInChan = array();
    protected $doNotSayHelloTo = array();

	public function __construct($main) {
		$this->main = $main;
	}
	public function start($IRCtext) {
		$this->hello($IRCtext);
		$this->NoHello($IRCtext);
	}

	// HELLO
	private function hello($IRCtext) {
 		$T = array();
		
		if(preg_match('`^:[^ ]+ 353 '.IRCConn::$myBotName.' = '.IRCConn::$channel.' :(.*?)\r?\n`', $IRCtext, $T)){
			$this->alreadyInChan = explode(' ', trim($T[1]));
			//print_r($T);
			
		}

		if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.IRCConn::$channel.' :((?:sa|\')?lut?|b(?:on)?j(?:ou)?r|hello|slt|hi|lo|re|yop|\'jour)(?: (.*?))?\r?\n`i', $IRCtext, $T)) {
			//perform
			// time 24hours to say hello again
			$cctime = 24*60*60;
			foreach($this->haveSayHello as $k =>  $v) {
				if(time() - $v > $cctime) {
					unset($this->haveSayHello[$k]);
				}
		#		print_r($this->haveSayHello);
			}

			if(!isset($this->haveSayHello[$T[1]]) && !in_array($T[1], $this->alreadyInChan)) {
				echo "HELLO";
				srand(date('s'));
				$rand = $this->hello[rand(0, count($this->hello)-1)];
				$this->haveSayHello[$T[1]] = time();
				if($this->sayHello == 1 && !in_array(strtolower($T[1]), $this->doNotSayHelloTo) ) {
					$this->main->MyConn->put('PRIVMSG '.IRCConn::$channel.'  '.$rand.' '.$T[1]);
				}
			}
		}

		if(preg_match('`^:(.*?)!.*?@.*? NICK :(.*?)\r?\n`i', $IRCtext, $T)) {

			if(isset($this->haveSayHello[$T[1]])) {
				$this->haveSayHello[$T[2]] = $this->haveSayHello[$T[1]];
				unset($this->haveSayHello[$T[1]]);
			}
			if(in_array($T[1], $this->alreadyInChan)) {
				$this->alreadyInChan[] = $T[1];
				unset($this->alreadyInChan[key(array_keys($this->alreadyInChan, $T[1]))]);
			#print_r($this->alreadyInChan);
				//$this->alreadyInChan[$T[2]] = $this->alreadyInChan[$T[1]];
			}
			//$this->put('PRIVMSG '.$this->channel.'  '.$rand.' '.$T[1]);
		}

		if(preg_match('`^:(.*?)!.*?@.*?  QUIT :(.*?)\r?\n`i', $IRCtext, $T)) {
			//unset($this->alreadyInChan[$T[1]]);
			unset($this->alreadyInChan[key(array_keys($this->alreadyInChan, $T[1]))]);
			#print_r($this->alreadyInChan);
		}

		if(preg_match('`^:(.*?)!.*?@.*? PART (.*?)\r?\n`i', $IRCtext, $T)) {
			//unset($this->alreadyInChan[$T[1]]);
			unset($this->alreadyInChan[key(array_keys($this->alreadyInChan, $T[1]))]);
			#print_r($this->alreadyInChan);
		}

	}
        
        private function NoHello($IRCtext) {
 			$T = array();
        	        
        	if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.preg_quote(IRCCOnn::$myBotName).' : *sayhello  *(1|yes|true|0|no|false) *\r?\n`', $IRCtext, $T)) {
			if(in_array($T[2], array('false', 'no', '0'))) {
				if(!in_array(strtolower($T[1]), $this->doNotSayHelloTo)) {
					$this->doNotSayHelloTo[] = strtolower(trim($T[1]));
					file_put_contents($this->doNotSayHelloToFile, implode("\n", $this->doNotSayHelloTo));
					$this->main->MyConn->put('NOTICE '.$T[1].' Je ne vous dirai désormais plus bonjour, vexé le moi !');
					//print_r($this->doNotSayHelloTo);
				}
			}elseif(in_array($T[2], array('true', 'yes', '1'))) {
				$TMP = array_keys($this->doNotSayHelloTo, strtolower($T[1]));
				//print_r($TMP);
				//echo '--------------';
				foreach($TMP as $v) {
					unset($this->doNotSayHelloTo[$v]);
				}
				file_put_contents($this->doNotSayHelloToFile, implode("\n", $this->doNotSayHelloTo));
				$this->main->MyConn->put('NOTICE '.$T[1].' Ahhhh c\'est gentil ca :)....');
				//print_r($this->doNotSayHelloTo);

			}
		}
        }
}

?>