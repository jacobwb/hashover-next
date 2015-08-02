<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		} else {
			exit ('<b>HashOver</b>: This is a class file.');
		}
	}

	// Database! Database! Just living in the database! Wow! Wow!
	class Database
	{
		public $database;
		public $setup;
		public $threadCount = array ();
		public $totalCount = 1;
		public $primaryCount = 1;
		public $storageMode;

		public
		function __construct (Setup $setup)
		{
			$this->setup = $setup;
			$this->storageMode = $setup->databaseType;

			try {
				if ($setup->databaseType === 'sqlite') {
					$this->database = new PDO ('sqlite:' . $setup->rootDirectory . '/pages/' . $setup->databaseName . '.sqlite');
				} else {
					$sql_connection = 'host=' . $setup->databaseHost . ';'
					                . 'dbname=' . $setup->databaseName . ';'
					                . 'charset=' . $setup->databaseCharset;

					$this->database = new PDO (
						$setup->databaseType . ':' . $sql_connection,
						$setup->databaseUser,
						$setup->databasePassword
					);
				}
			} catch (PDOException $error) {
				exit ($setup->escapeOutput ($error->getMessage (), 'single'));
			}

			// Statement for creating initial comment tread table
			$create = 'CREATE TABLE IF NOT EXISTS \'' . $setup->threadDirectory . '\' '
			        . '(id TEXT PRIMARY KEY,'
			        . ' body TEXT,'
			        . ' status TEXT,'
			        . ' date TEXT,'
			        . ' name TEXT,'
			        . ' password TEXT,'
			        . ' login_id TEXT,'
			        . ' email TEXT,'
			        . ' encryption TEXT,'
			        . ' email_hash TEXT,'
			        . ' notifications TEXT,'
			        . ' website TEXT,'
			        . ' ipaddr TEXT,'
			        . ' likes TEXT,'
			        . ' dislikes TEXT)';

			// Execute statement
			$this->database->exec ($create);
		}

		public
		function write ($action, array $array)
		{
			switch ($action) {
				// Action for posting a comment
				case 'insert': {
					$query = 'INSERT INTO \'' . $this->setup->threadDirectory . '\' VALUES '
					       . '(:id,'
					       . ' :body,'
					       . ' :status,'
					       . ' :date,'
					       . ' :name,'
					       . ' :password,'
					       . ' :login_id,'
					       . ' :email,'
					       . ' :encryption,'
					       . ' :email_hash,'
					       . ' :notifications,'
					       . ' :website,'
					       . ' :ipaddr,'
					       . ' 0,'
					       . ' 0)';

					break;
				}

				// Action for deleting a comment
				case 'delete': {
					if ($this->setup->userDeletionsUnlink === true) {
						$query = 'DELETE FROM \'' . $this->setup->threadDirectory . '\' WHERE id=:id';
					} else {
						$query = 'UPDATE \'' . $this->setup->threadDirectory . '\' ';
						$query .= 'SET status=:status WHERE id=:id';
					}

					break;
				}

				// Action for editing a comment
				case 'update': {
					$query = 'UPDATE \'' . $this->setup->threadDirectory . '\' '
					       . 'SET'
					       . ' body=:body,'
					       . ' name=:name,'
					       . ' password=:password,'
					       . ' email=:email,'
					       . ' encryption=:encryption,'
					       . ' email_hash=:email_hash,'
					       . ' notifications=:notifications,'
					       . ' website=:website,'
					       . ' likes=:likes,'
					       . ' dislikes=:dislikes'
					       . ' WHERE id=:id';

					break;
				}
			}

			try {
				$prepare = $this->database->prepare ($query);
				$prepare->execute ($array);

				return true;
			} catch (PDOException $error) {
				exit ($this->setup->escapeOutput ($error->getMessage (), 'single'));
			}

			return false;
		}

		public
		function countComments ($comment)
		{
			// Count all comments
			if (strpos ($comment, '-') !== false) {
				$file_parts = explode ('-', $comment);
				$thread = basename ($comment, '-' . end ($file_parts));

				if (isset ($this->threadCount[$thread])) {
					$this->threadCount[$thread]++;
				} else {
					$this->threadCount[$thread] = 1;
				}
			} else {
				// Count top level comments
				$this->primaryCount++;
			}

			// Count replies
			if (isset ($this->threadCount[$comment])) {
				$this->threadCount[$comment]++;
			} else {
				$this->threadCount[$comment] = 1;
			}

			$this->totalCount++;
		}
	}

?>
