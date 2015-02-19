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

	// Database! Database! Just living in the database! Wow! Wow!
	class Database
	{
		public $open_db,
		       $db_table,
		       $setup,
		       $subfile_count = array(),
		       $total_count = 1,
		       $cmt_count = 1,
		       $storage_format;

		public function __construct($setup)
		{
			$this->db_table = str_replace('-', '_', $setup->ref_path);
			$this->setup = $setup;

			try {
				if ($setup->dbtype == 'sqlite') {
					$this->open_db = new PDO('sqlite:./pages/' . $setup->dbname . '.sqlite');
				} else {
					$this->open_db = new PDO('mysql:host=' . $setup->dbhost . ';dbname=' . $setup->dbname . ';charset=utf8', $setup->dbuser, $setup->dbpass);
				}
			} catch (PDOException $error) {
				exit($setup->escape_output($error->getMessage(), 'single'));
			}

			// Create comment tread table
			$create = 'CREATE TABLE IF NOT EXISTS ' . $this->db_table . '
				  (
					id TEXT PRIMARY KEY,
					name TEXT,
					password TEXT,
					email TEXT,
					encryption TEXT,
					website TEXT,
					date TEXT,
					body TEXT,
					likes TEXT,
					dislikes TEXT,
					notifications TEXT,
					ipaddr TEXT
				  )';

			$this->open_db->exec($create);
		}

		public function write_db($action, array $array)
		{
			switch ($action) {
				case 'insert': {
					$query = 'INSERT INTO ' . $this->db_table . '
						  VALUES (
						  	:id,
						  	:name,
						  	:password,
						  	:email,
						  	:encryption,
						  	:website,
						  	:date,
						  	:body,
						  	:likes,
						  	:dislikes,
						  	:notifications,
						  	:ipaddr
						  )';

					break;
				}

				case 'delete': {
					$query = 'DELETE FROM ' . $this->db_table . ' WHERE id=:id';
					break;
				}

				case 'update': {
					$query = 'UPDATE ' . $this->db_table . '
					          SET name=:name,
					              password=:password,
					              email=:email,
					              encryption=:encryption,
					              website=:website,
					              body=:body,
					              notifications=:notifications
					          WHERE id=:id';

					break;
				}
			}

			try {
				$prepare = $this->open_db->prepare($query);
				$prepare->execute($array);
				return true;
			} catch (PDOException $error) {
				exit($this->setup->escape_output($error->getMessage(), 'single'));
			}

			return false;
		}

		public function count_comments($comment)
		{
			// Count all comments
			if (strpos($comment, '-') !== false) {
				$file_parts = explode('-', $comment);
				$thread = basename($comment, '-' . end($file_parts));

				if (isset($this->subfile_count["$thread"])) {
					$this->subfile_count["$thread"]++;
				} else {
					$this->subfile_count["$thread"] = 1;
				}
			} else {
				// Count top level comments
				$this->cmt_count++;
			}

			// Count replies
			if (isset($this->subfile_count["$comment"])) {
				$this->subfile_count["$comment"]++;
			} else {
				$this->subfile_count["$comment"] = 1;
			}

			$this->total_count++;
		}
	}

?>
