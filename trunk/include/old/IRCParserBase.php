 <?php

 class IRCParserBase {
 	
 	private $chan;
 	private $myBotName;
 	public $IRCConn;
 	public $Log;
 	
 	// WALL
    public $wallURL = 'http://phpdebutant.org/article19.php';
    // WALL n° X -> [X] will be replaced by the value
    // ex : !wall 123 -> http://site.com/wall[X].php -> http://site.com/wall123.php
    public $wallX = 'http://phpdebutant.org/wall[X].php';
    // SAY HELLO
    public $sayHello = 1;
    protected $hello = array('Bonjour', 'Salut', 'rahhhhh', 'Hi', 'lo', 'lu', 'hello', 'yep', 'kikoo', 'yala', '\'lut', 'bien le bonjour');
    protected $haveSayHello = array();
    protected $alreadyInChan = array();
    //   ..;    
    public $currentCommand = '';
 	
 	public function __contruct() {
 	}
 	
 	public function Listen($IRCtext) {
 		
 		# Base
 		$this->Ok($IRCtext);
 		$this->Deco($IRCtext);
 		$this->Pong($IRCtext);
 		$this->kick($IRCtext);
 		$this->NickUsed($IRCtext);
 		$this->IllegalChNick($IRCtext);
 		$this->CTCP($IRCtext);
 		
 		# Help
 		$this->help($IRCtext);
 		
 		# Addon
 		$this->wall($IRCtext);
 		$this->php($IRCtext);
 		$this->Mysql($IRCtext);
 		$this->Google($IRCtext);
 		
 	}

 	// HELP
 	private function help($IRCtext) {
 		$T = array();
 		
 		if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.$this->IRCConn->chan.' :!help([ ]*?)\r?\n`', $IRCtext, $T)) {
 			$this->IRCConn->put('PRIVMSG '.$T[1].'  !help -> Cette aide - Notice des commandes du bot');
 			$this->IRCConn->put('PRIVMSG '.$T[1].'  !wall -> URL vers THE WalL');
 			$this->IRCConn->put('PRIVMSG '.$T[1].'  !wall X -> URL vers le WalL n° X');
 			$this->IRCConn->put('PRIVMSG '.$T[1].'  !php fonction -> URL vers la documentation de la fonction sur php.net');
 			$this->IRCConn->put('PRIVMSG '.$T[1].'  !mysql votre recherche -> Résultat de votre recherche sur mysql.com');
 			$this->IRCConn->put('PRIVMSG '.$T[1].'  !google mots clés -> recherche avec google');
 		}
 	}

 	// PHP
 	private function php($IRCtext) {
 		$T = array();
 		
 		if(preg_match('`:[^ ]+ PRIVMSG '.$this->IRCConn->chan.' :!php ([-_[:alnum:]]+)([ ]*?)\r?\n`', $IRCtext, $T)) {
 			$this->IRCConn->put('PRIVMSG '.$this->IRCConn->chan.'  http://php.net/'.$T[1]);
 		}
 	}
 	
	// WALL (RESPECT ORDER)
	private function Wall($IRCtext) {
 		$T = array();
 		
        if(preg_match('`:[^ ]+ PRIVMSG '.$this->IRCConn->chan.' :(?:.*?)?!wall( [0-9]+?)?([ ]*?).*?\r?\n`i', $IRCtext, $T)) {
            if($T[1] != '') {
                $this->put('PRIVMSG '.$this->IRCConn->chan.' ' . str_replace('[X]', trim($T[1]), $this->wallX) );
            } else {
                $this->put('PRIVMSG '.$this->IRCConn->chan.'  Le WalL ::   ' . $this->wallURL);
            }
            #continue;
        }
	}

 	// MySQL
	private function Mysql($IRCtext) {
 		$T = array();
 		
	 	if(preg_match('`:[^ ]+ PRIVMSG '.$this->IRCConn->chan.' :!mysql (.*?)\r?\n`', $IRCtext, $T)) {
	 		$T[1] = trim($T[1]);
	 		if($T[1] != '') {
	 			$search = $this->formatSearch($T[1]);
	 			$url = 'http://www-fr.mysql.com/search/?q='.$search.'&lang=fr&doc=1';
	 			$this->IRCConn->put('PRIVMSG '.$this->IRCConn->chan.'  ' . $url);
	 		}
	 	}
	}
	
 	// GOOGLE
 	private function Google($IRCtext) {
 		$T = array();
 		
	 	if(preg_match('`:[^ ]+ PRIVMSG '.$this->IRCConn->chan.' :!google (.*?)\r?\n`', $IRCtext, $T)) {
	 		$T[1] = trim($T[1]);
	 		if($T[1] != '') {
	 			$search = $this->formatSearch($T[1]);
	 			$url = 'http://google.fr/search?num=30&hl=fr&ie=UTF-8&newwindow=1&q='.$search;
	 			$this->IRCConn->put('PRIVMSG '.$this->IRCConn->chan.' ' . $url);
	 		}
	 	}
 	}

 	// KICK
 	private function kick($IRCtext) {
 		$T = array();
 		
	 	if(preg_match('`^:(.*?)!.*?@.*? KICK '.$this->IRCConn->chan.' ([^ ]+)`', $IRCtext, $T)) {
	 		if($T[2] == $this->myBotName) {
	 			$this->log('KICK : Le bot a été kické par ' .$T[1]);
	 			sleep(1);
	 			$this->joinChan($this->IRCConn->chan);
	 			$this->IRCConn->put('PRIVMSG '.$this->IRCConn->chan.' :Merci '.$T[1].' !');
	 		}
	 	}
 	}
	
 	// Illegals characters in Nickname
 	private function IllegalChNick ($IRCtext) {
	 	if(preg_match('`^:[^ ]+ 432 (.*?)\r?\n`', $IRCtext)){
	 		$this->IRCConn->myBotName = 'XboT';
	 		$this->IRCConn->put('NICK :'.$this->IRCConn->myBotName);
	 		$this->log('NICK : Caractères interdits - NEW NICK : ' . $this->IRCConn->myBotName);
	 	}
 	}

 	// Nick already in use
 	private function NickUsed($IRCtext) {
	 	if(preg_match('`^:[^ ]+ 433 (.*?)\r?\n`', $IRCtext)){
	 		$this->IRCConn->newNick();
	 		$this->IRCConn->put('NICK :'.$this->IRCConn->myBotName);
	 		$this->log('NICK : Nick déjà utilisé - NEW NICK : ' . $this->IRCConn->myBotName);
	 	}
 	}

 	// CTCP
 	
 	private function CTCP($IRCtext) {
 		$T = array();
 		
	 	if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.preg_quote($this->IRCConn->myBotName, '`').'(.*?)(VERSION|USERINFO|CLIENTINFO)`', $IRCtext, $T)) {
	 		$this->IRCConn->put('NOTICE '.$T[1].' XboT version '.$this->IRCConn->botVersion.' - PHP  '.phpversion().' -- par Fabrice Lezoray ( http://classes.scriptsphp.fr/ )');
	 	}
	 	if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.preg_quote($this->IRCConn->myBotName, '`').'(.*?)PING (.*?)\r?\n`', $IRCtext, $T)) {
	 		$this->IRCConn->put('NOTICE '.$T[1]." PING\1".time()."\1");
	 	}
	 	if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.preg_quote($this->IRCConn->myBotName,'`').'(.*?)(TIME)`', $IRCtext, $T)) {
	 		$this->IRCConn->put('NOTICE '.$T[1].' '.date('Y-m-d H:i:s'));
	 	}
 	}

 	// DECO
 	private function Deco($IRCtext) {
	 	if(preg_match("`^ERROR :(Closing Link: )?(.*)\r?$`i", $IRCtext)) {
	 		@fclose($this->IRCConn->C);
	 		sleep(3);
	 		$this->Log->Add('DECONEXION : ERROR');
	 		die('Closing Link'."\n");
	 	}
 	}

 	// OK
 	private function Ok($IRCtext) {
	 	if(preg_match("`^:[^ ]+ 001 .*?\r?\n`", $IRCtext)) {
	 		$this->IRCConn->joinChan($this->IRCConn->chan);
	 	}
 	}

 	// PONG
 	private function Pong($IRCtext) {
 		$T = array();
 		
	 	if(preg_match("`^PING :(.*)\r?\n`", $IRCtext, $T)) {
	 		$this->IRCConn->put('PONG '.$T[1]);
	 	}
 	}
 	
    // HELLO
    private function Hello($IRCtext) {	
 		$T = array();

        if(preg_match('`^:[^ ]+ 353 '.$this->IRCConn->myBotName.' = '.$this->IRCConn->chan.' :(.*?)\r?\n`', $IRCtext, $T)){
            $this->alreadyInChan = explode(' ', trim($T[1]));
            //print_r($T);
            print_r($this->alreadyInChan);
        }
        
        if(preg_match('`^:(.*?)!.*?@.*? PRIVMSG '.$this->IRCConn->chan.' :((?:sa|\')?lut?|b(?:on)?j(?:ou)?r|hello|slt|hi|lo|re|\'jour)(?: (.*?))?\r?\n`i', $IRCtext, $T)) {
            
            //perform
            // time 24hours to say hello again
            $cctime = 24*60*60;
            foreach($this->haveSayHello as $k =>  $v) {
                if(time() - $v > $cctime) {
                    unset($this->haveSayHello[$k]);
                }
            }
            
            if(!isset($this->haveSayHello[$T[1]]) && !in_array($T[1], $this->alreadyInChan)) {
                srand(date('s'));
                $rand = $this->hello[rand(0, count($this->hello)-1)];
                $this->haveSayHello[$T[1]] = time();
                if($this->sayHello == 1) {
                    $this->put('PRIVMSG '.$this->IRCConn->chan.'  '.$rand.' '.$T[1]);
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
                print_r($this->alreadyInChan);
                //$this->alreadyInChan[$T[2]] = $this->alreadyInChan[$T[1]];
            }
            //$this->put('PRIVMSG '.$this->chan.'  '.$rand.' '.$T[1]);
        }
        
        if(preg_match('`^:(.*?)!.*?@.*?  QUIT :(.*?)\r?\n`i', $IRCtext, $T)) {
            //unset($this->alreadyInChan[$T[1]]);
            unset($this->alreadyInChan[key(array_keys($this->alreadyInChan, $T[1]))]);
            print_r($this->alreadyInChan);
        }
        
        if(preg_match('`^:(.*?)!.*?@.*? PART (.*?)\r?\n`i', $IRCtext, $T)) {
            //unset($this->alreadyInChan[$T[1]]);
            unset($this->alreadyInChan[key(array_keys($this->alreadyInChan, $T[1]))]);
            print_r($this->alreadyInChan);
        }
        
    }
 	
    // Misc. Function 	
    protected function formatSearch($search) {
        return preg_replace('`[ ]+`', '+', $search);
    }
 	
 }
?>