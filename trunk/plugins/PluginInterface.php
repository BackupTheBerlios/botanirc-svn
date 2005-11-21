<?php
	interface PluginInterface {
		public function __construct($main);
		public function run(IRCMessage $msg);
	}
?>