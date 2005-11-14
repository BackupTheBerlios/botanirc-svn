<?php
	interface plugin {
		public function __construct($main);
		public function start(IRCMessage $msg);	
		public function help();
	}
?>