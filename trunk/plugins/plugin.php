<?php
	interface plugin {
		public function __construct($main);
		public function run(IRCMessage $msg);	
		public function help();
	}
?>