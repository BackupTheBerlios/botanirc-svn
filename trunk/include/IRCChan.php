<?php
	/*
	 * This file is part of BotanIRC.
	 * Ce fichier fait partie de BotanIRC.
	 */
	 
	/**
	 * Objet IRCChan.
	 * 
	 * @license http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
	 * @copyright Copyright &copy; 2005, Renaud LITTOLFF
	 * @author Renaud LITTOLFF <matt.rixx@mattlab.com>
	 * @version 1.0
	 */
	class IRCChan {
		
		/*
		 * Propriétés
		 */
		 
		/**
		 * Serveur du chan.
		 * 
		 * @access private
		 * @var object contient un objet IRCServer.
		 */
		private $server	= null;
		
		/**
		 * Nom du chan.
		 * 
		 * @var string
		 */
		public $name	= '';
		
		/**
		 * Topic du chan.
		 * 
		 * @var string
		 */
		public $topic	= '';
		
		/**
		 * Liste des users du chan.
		 * 
		 * @var array
		 */		
		public $users	= array();


		
		/*
		 * Méthodes
		 */
		
		/**
		 * Constructeur.
		 * 
		 * @param object $server Objet IRCServer du chan.
		 * $param string $chan Nom du chan.
		 */	
		public function __construct($server, $chan) {
			$this->server	= $server;
			$this->name		= $chan;
		}
		
		/**
		 * Envoi un message sur le chan.
		 * 
		 * @param string $msg Message à envoyer.
		 */		
		public function sendMsg($msg) {
			$this->server->put("PRIVMSG {$this->name} :$msg");	
		}
		
		/**
		 * Envoi une action sur le chan (/me blabla).
		 * 
		 * @param string $action Texte de l'action à envoyer.
		 */	
		public function sendAction($action) {
			$this->server->put("PRIVMSG {$this->name} :ACTION $msg");
		}
		
		/**
		 * Ajoute un user au chan.
		 * 
		 * @param string $nick Nick de l'user.
		 * @param string $mode Mode de l'user (@=op, +=voice).
		 */	
		public function addUser($nick, $mode) {
			$this->users[$nick] = $mode;
		}
		
		/**
		 * Supprime un user du chan.
		 * 
		 * @param string $nick Nick de l'user à supprimer.
		 */	
		public function delUser($nick) {
			unset($this->users[$nick]);
		}
	}
?>
