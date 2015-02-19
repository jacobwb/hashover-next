<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	// Read and count comments
	class ParseSQL extends Database
	{
		public function query(array $files = array(), $auto = true)
		{
			$this->storage_format = 'sqlite';
			$results = $this->open_db->query('SELECT id FROM ' . $this->db_table);

			if ($results) {
				$results->execute();
				$fetchAll = $results->fetchAll(PDO::FETCH_NUM);
				$return_array = array();

				for ($i = 0, $il = count($fetchAll); $i < $il; $i++) {
					$return_array[($fetchAll[$i][0])] = (string) $fetchAll[$i][0];
					$this->count_comments($fetchAll[$i][0]);
				}

				return $return_array;
			} else {
				exit($this->setup->escape_output('SQL query fail!', 'single'));
			}
		}

		public function read($id)
		{
			$result = $this->open_db->query('
				SELECT  name,
					password,
					email,
					encryption,
					website,
					date,
					body,
					likes,
					dislikes,
					notifications,
					ipaddr
				FROM ' . $this->db_table . '
				WHERE id="' . $id . '"'
			);

			if ($result) {
				$comment = $result->fetch(PDO::FETCH_ASSOC);
			} else {
				exit($this->setup->escape_output('SQL query fail!', 'single'));
			}

			return array_merge($this->setup->data_template, (array) $comment);
		}

		public function save($contents, $id, $editing = false)
		{
			if ($editing == true) {
				return $this->write_db('update',
					array(
						'id' => $id,
						'name' => $contents['name'],
						'password' => $contents['password'],
						'email' => $contents['email'],
						'encryption' => $contents['encryption'],
						'website' => $contents['website'],
						'body' => $contents['body'],
						'notifications' => $contents['notifications']
					)
				);
			} else {
				return $this->write_db('insert',
					array(
						'id' => $id,
						'name' => $contents['name'],
						'password' => $contents['password'],
						'email' => $contents['email'],
						'encryption' => $contents['encryption'],
						'website' => $contents['website'],
						'date' => $contents['date'],
						'body' => $contents['body'],
						'likes' => $contents['likes'],
						'dislikes' => $contents['dislikes'],
						'notifications' => $contents['notifications'],
						'ipaddr' => $contents['ipaddr']
					)
				);
			}
		}

		public function delete($id)
		{
			return $this->write_db('delete', array('id' => $id));
		}
	}

?>
