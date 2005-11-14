<?php
	class Log {
		private $output		= '';
		private $name		= '';
		private $write_type	= '';
		
		private $timestamp_format = 'd/m/Y - H:i:s';
		
		public function __construct($output, $name, $write_type = '') {
			$this->output	= $output;
			$this->name		= $name;
			
			switch($write_type) {
				case 'overwrite':
					$this->write_type = 'w';
				break;
				
				default:
				case 'continue':
					$this->write_type = 'a';
				break;
			}
			
			//$this->add('Début de session');
		}
		
		public function __destruct() {
			if($this->output == 'file') {
				fclose($this->fp);
			}
			
			//$this->add('Fin de session');
		}
		
		public function add($txt) {
			$time = date($this->timestamp_format);
			
			$file = $this->name . '.log';
			
			if($this->output == 'file') {
				if(!($this->fp = fopen($file, $this->write_type))) {
					die("Ouverture du fichier $file impossible.");
				}
			}
						
			$txt = (array) $txt;
			
			$sep = '';
			foreach($txt as $ligne) {
				$temp = $time . ': ' . $sep . $ligne . "\n";
				
				if($this->output == 'file') {
					fwrite($this->fp, $temp);
				} elseif($this->output == 'console') {
					echo $this->name . ' > ' . $temp;
				}
				
				$sep = '  ';
			}
		}
	}
?>