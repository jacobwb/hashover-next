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

	// Read and count comments
	class ParseSQL extends Database
	{
		public
		function query (array $files = array (), $auto = true)
		{
			$this->storageMode = 'sqlite';
			$results = $this->database->query ('SELECT id FROM \'' . $this->setup->threadDirectory . '\'');

			if ($results !== false) {
				$results->execute ();
				$fetchAll = $results->fetchAll (PDO::FETCH_NUM);
				$return_array = array ();

				for ($i = 0, $il = count ($fetchAll); $i < $il; $i++) {
					$return_array[($fetchAll[$i][0])] = (string) $fetchAll[$i][0];
					$this->countComments ($fetchAll[$i][0]);
				}

				return $return_array;
			} else {
				exit ($this->setup->escapeOutput ('SQL query fail!', 'single'));
			}
		}

		public
		function read ($id)
		{
			$result = $this->database->query (
				'SELECT'
				. ' body,'
				. ' status,'
				. ' date,'
				. ' name,'
				. ' password,'
				. ' login_id,'
				. ' email,'
				. ' encryption,'
				. ' email_hash,'
				. ' notifications,'
				. ' website,'
				. ' ipaddr,'
				. ' likes,'
				. ' dislikes'
				. ' FROM \'' . $this->setup->threadDirectory . '\''
				. ' WHERE id=\'' . $id . '\''
			);

			if ($result !== false) {
				return (array) $result->fetch (PDO::FETCH_ASSOC);
			} else {
				exit ($this->setup->escapeOutput ('SQL query fail!', 'single'));
			}

			return false;
		}

		public
		function save ($contents, $id, $editing = false)
		{
			if ($editing === true) {
				return $this->write ('update',
					array (
						'id' => $id,
						'body' => $contents['body'],
						'name' => $contents['name'],
						'password' => $contents['password'],
						'email' => $contents['email'],
						'encryption' => $contents['encryption'],
						'email_hash' => $contents['email_hash'],
						'notifications' => $contents['notifications'],
						'website' => $contents['website'],
						'likes' => $contents['likes'],
						'dislikes' => $contents['dislikes']
					)
				);
			} else {
				return $this->write ('insert', array_merge ($contents, array ('id' => $id)));
			}
		}

		public
		function delete ($id)
		{
			return $this->write ('delete',
				array (
					'id' => $id,
					'status' => 'deleted'
				)
			);
		}
	}

?>
