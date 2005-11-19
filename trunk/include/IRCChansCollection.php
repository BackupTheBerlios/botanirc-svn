<?php
	/*
	 * This file is part of BotanIRC.
	 * Ce fichier fait partie de BotanIRC.
	 */
	 
	/**
	 * Objet IRCChansCollection: Collection de chans (Objet IRCChan).
	 * 
	 * @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
	 * @copyright Copyright &copy; 2005, Renaud LITTOLFF
	 * @author Renaud LITTOLFF <matt.rixx@mattlab.com>
	 * @version 1.0
	 */
	class IRCChansCollection {
		
		/*
		 * Propriétés
		 */
		 
		/**
		 * Serveur des chans.
		 * 
		 * @access private
		 * @var object contient un objet IRCServer.
		 */
		private $server	= null;
		
		/**
		 * Chans de cette collection.
		 * 
		 * @access private
		 * @var array Tableau d'objet IRCChan.
		 */		
		private $chans	= array();
		
		
				
		/*
		 * Méthodes
		 */
		
		/**
		 * Constructeur.
		 * 
		 * @param object $server Objet IRCServer du chan.
		 */
		public function __construct($server) {
			$this->server = $server;
		}
		
		/**
		 * Ajoute un chan à la collection.
		 * 
		 * @param string $chan
		 */
		public function addChan($chan) {
			$this->chans[$chan] = new IRCChan($this->server, $chan);
		}
		
		/**
		 * Supprime un chan de la collection.
		 * 
		 * @param string $chan
		 */
		public function delChan($chan) {
			unset($this->chans[$chan]);
		}
		
		/**
		 * Retourne un chan (objet IRCChan).
		 * 
		 * @param string $chan
		 * @return IRCChan
		 */
		public function getChanByName($chan) {
			return $this->exists($chan) ? $this->chans[$chan] : false;	
		}
		
		/**
		 * Vérifie l'existance d'un chan.
		 * 
		 * @param string $chan
		 * @return boolean
		 */
		public function exists($chan) {
			return isset($this->chans[$chan]);	
		}
		
		/**
		 * Renvoi la liste de tous les chans.
		 * 
		 * @return array
		 */
		public function getListAllChans() {
			return array_keys($this->chans);	
		}
	}
?>