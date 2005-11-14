<?php
	include 'include/define.php';
	
	include 'config.php';
	
	function __autoload($class) {
		$file	= 'include/' . $class.'.php';
		$plugin	= 'plugins/' . $class.'.php';
		
		if(is_readable($file)) {
			include_once $file;
		}elseif(is_readable($plugin)) {
			include_once $plugin;
		}
	}
	
	$bot = new IRCMain($cfg);
	
	foreach($plugins as $plugin) {
		$bot->LoadPlugin($plugin);
	}
	
	$bot->run();
?>
