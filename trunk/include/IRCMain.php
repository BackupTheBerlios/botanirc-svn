<?php
	class IRCMain {
		public $servers = array();
		public $plugins = array();
		
		// BOT version
		static $version = 0.5;
	
		public function __construct($cfg) {			
			foreach($cfg as $server => $params) {
				$this->servers[$server] = IRCServer::GetInstance($server);
				$this->servers[$server]->init(	$server, $params['port'],
												$params['nick'], $params['domain'],
												$params['chans'], $params['performs']);
				if(!$this->servers[$server]->connect()) {
					unset($this->servers[$server]);
				}
			}
		}
	
		public function run() {						
			while (true) {
				foreach($this->servers as $address => $server) {
					$msg = $server->get();

					if($msg === null) {
						if(!$server->connect()) {
							unset($this->servers[$address]);
							break;
						}

					} elseif ($msg !== false) {					
						foreach ($this->plugins as $plugin_name => $plugin) {							
							$plugin->run($msg);
						}
					}
					
					unset($msg);
				}
				
				if(count($this->servers) == 0) {
					die('Déconnexion globale');	
				}
			}
		}
	
		public function LoadPlugin($plugin_name) {
			if (!array_key_exists($plugin_name, $this->plugins)) {
				$this->plugins[$plugin_name] = new $plugin_name($this);
			}
		}
		
		public function UnloadPlugin($plugin_name) {
			if (array_key_exists($plugin_name, $this->plugins)) {
				unset($this->plugins[$plugin_name]);
			}
		}
	}
?>