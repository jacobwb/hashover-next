<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


// Read and count comments
class ParseSQL extends Database
{
	protected $insert = array (
		'id' => null,
		'body' => null,
		'status' => null,
		'date' => null,
		'name' => null,
		'password' => null,
		'login_id' => null,
		'email' => null,
		'encryption' => null,
		'email_hash' => null,
		'notifications' => null,
		'website' => null,
		'ipaddr' => null,
		'likes' => null,
		'dislikes' => null
	);

	protected $update = array (
		'id' => null,
		'body' => null,
		'status' => null,
		'name' => null,
		'password' => null,
		'email' => null,
		'encryption' => null,
		'email_hash' => null,
		'notifications' => null,
		'website' => null,
		'likes' => null,
		'dislikes' => null
	);

	public function __construct (Setup $setup)
	{
		parent::__construct ($setup);

		// Throw exception if the SQL extension isn't loaded
		switch ($setup->databaseType) {
			case 'sqlite': {
				$setup->extensionsLoaded (array (
					'pdo_sqlite',
					'sqlite3'
				));

				break;
			}

			case 'mysql': {
				$setup->extensionsLoaded (array (
					'pdo_mysql'
				));

				break;
			}
		}
	}

	public function query (array $files = array (), $auto = true)
	{
		$statement = 'SELECT `id` FROM `' . $this->setup->threadName . '`';
		$results = $this->database->query ($statement);

		if ($results !== false) {
			$fetch_all = $results->fetchAll (\PDO::FETCH_NUM);
			$return_array = array ();

			for ($i = 0, $il = count ($fetch_all); $i < $il; $i++) {
				$key = $fetch_all[$i][0];
				$return_array[$key] = (string)($key);
			}

			return $return_array;
		}

		return false;
	}

	public function read ($id, $thread = 'auto')
	{
		$thread = $this->getCommentThread ($thread);

		$columns = implode (', ', array (
			'`body`',
			'`status`',
			'`date`',
			'`name`',
			'`password`',
			'`login_id`',
			'`email`',
			'`encryption`',
			'`email_hash`',
			'`notifications`',
			'`website`',
			'`ipaddr`',
			'`likes`',
			'`dislikes`'
		));

		$statement  = 'SELECT ' . $columns . ' ';
		$statement .= 'FROM `' . $thread . '` ';
		$statement .= 'WHERE id=\'' . $id . '\'';

		$result = $this->database->query ($statement);

		if ($result !== false) {
			return (array) $result->fetch (\PDO::FETCH_ASSOC);
		}

		return false;
	}

	protected function prepareQuery ($id, array $contents, array $defaults)
	{
		$query = array_merge ($defaults, array ('id' => $id));

		foreach ($contents as $key => $value) {
			if (array_key_exists ($key, $defaults)) {
				$query[$key] = $value;
			}
		}

		return $query;
	}

	public function save ($id, array $contents, $editing = false, $thread = 'auto')
	{
		$thread = $this->getCommentThread ($thread);
		$action = ($editing === true) ? 'update' : 'insert';
		$query = $this->prepareQuery ($id, $contents, $this->$action);
		$status = $this->write ($action, $thread, $query);

		return $status;
	}

	public function delete ($id, $delete = false)
	{
		$query = array ('id' => $id);

		if ($delete !== true) {
			$query['status'] = 'deleted';
		}

		$status = $this->write ('delete', 'auto', $query, $delete);

		return $status;
	}
}
