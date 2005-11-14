<?php 
class plug_google implements plugin {

	private $main;
	public $WsdlUri = 'http://api.google.com/GoogleSearch.wsdl';
	public $LicenceKey = 'yourGoogleLicenseKey';
	public $Client = '';

	public function __construct($main) {
		$this->main = $main;
	}

	public function start($IRCtxt) {
		$this->google($IRCtxt);
	}

	public function help() {
		$msg = '!google mots clés : recherche sur Google';
		return $msg;
	}

	private function google($IRCtext) {
 		$T = array();
		
		if(preg_match('`!google (.*)`i', $IRCtext, $T)) {
			$O = $this->Search($T[1]);
			$i = 0;
			$this->main->MyConn->put('PRIVMSG '.IRCConn::$channel.'  '.$T[1] );
			foreach($O->resultElements as $key => $val) {			
				//echo utf8_decode($val->URL);
				$this->main->MyConn->put('PRIVMSG '.IRCConn::$channel.'  '.utf8_decode($val->URL) );
				if($i == 2) break;
				$i++;
			}
		}
	}

	public function Search($search , $start=0, $maxresults=5, $filter=false, $restrict='', $safesearch=false , $lang='') {
		// Limitation du a l API
		if($maxresults > 10) {
			$maxresults = 10;
		}

		$params = array(
		'key' => $this->LicenceKey,
		'q' => utf8_encode($search),
		'start' => (int)$start,
		'maxResults' => (int)$maxresults,
		'filter' => (boolean)$filter,
		'restrict' => $restrict,
		'safeSearch' => (boolean)$safesearch,
		'lr' => $lang,
		'ie' => '',
		'oe' => ''
		);
		try {
			$this->Client = new SoapClient($this->WsdlUri);
		} catch (SoapFault $fault) {
			return $fault;
		}
		try {
			$O =  $this->Client->__call("DoGoogleSearch", $params);
		} catch (SoapFault $fault) {
			return $fault;
		}
		return $O;
	}
}
?>