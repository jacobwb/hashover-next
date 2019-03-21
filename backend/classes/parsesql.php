<?php namespace HashOver;

// Copyright (C) 2010-2019 Jacob Barkdull
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


class ParseSQL extends Database
{
	// Database statement for adding a new comment
	protected $insert = array (
		'domain' => null,
		'thread' => null,
		'comment' => null,
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

	// Database statement for updating an existing comment
	protected $update = array (
		'domain' => null,
		'thread' => null,
		'comment' => null,
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
		// Construct parent class
		parent::__construct ($setup);

		// Do different things for different database types
		switch ($this->databaseType) {
			// SQLite
			case 'sqlite': {
				// Throw exception if SQLite extensions aren't loaded
				$setup->extensionsLoaded (array (
					'PDO', 'pdo_sqlite', 'sqlite3'
				));

				break;
			}

			// MySQL
			case 'mysql': {
				// Throw exception if MySQL extensions aren't loaded
				$setup->extensionsLoaded (array (
					'PDO', 'pdo_mysql'
				));

				break;
			}
		}
	}

	// Returns an array of comments
	public function query ()
	{
		// Initial comments to return
		$comments = array ();

		// SQL statement to query comments by
		$statement = implode (' ', array (
			'SELECT `comment` FROM `comments`',
			'WHERE domain=:domain',
			'AND thread=:thread'
		));

		// Query comments using the statement
		$results = $this->executeStatement ($statement, array (
			'domain' => $this->setup->website,
			'thread' => $this->setup->threadName
		));

		// Check if we received any comments
		if ($results !== false) {
			// If so, fetch them all
			$fetch_all = $results->fetchAll (\PDO::FETCH_NUM);

			// Run through comments
			for ($i = 0, $il = count ($fetch_all); $i < $il; $i++) {
				// Get comment key
				$key = $fetch_all[$i][0];

				// Add key to comments
				$comments[$key] = (string)($key);
			}
		}

		return $comments;
	}

	// Reads a comment from database
	public function read ($comment, $thread = 'auto')
	{
		// Construct column portion of SQL statement
		$columns = array (
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
		);

		// SQL statement to get columns from database
		$statement = implode (' ', array (
			'SELECT ' . implode (', ', $columns),
			'FROM `comments`',
			'WHERE domain=:domain',
			'AND thread=:thread',
			'AND comment=:comment'
		));

		// Query columns using statement
		$result = $this->executeStatement ($statement, array (
			'domain' => $this->setup->website,
			'thread' => $this->setup->threadName,
			'comment' => $comment
		));

		// Return columns as array if successful
		if ($result !== false) {
			// Fetch all comments
			$fetch_all = $result->fetch (\PDO::FETCH_ASSOC);

			// Return as array if comments exist
			if (!empty ($fetch_all)) {
				return (array) $fetch_all;
			}
		}

		return false;
	}

	// Prepares a query statement
	protected function prepareQuery ($comment, array $contents, array $defaults)
	{
		// Merge ID into default statement
		$query = array_merge ($defaults, array (
			'comment' => $comment
		));

		// Merge selective contents into default statement
		foreach ($contents as $key => $value) {
			if (array_key_exists ($key, $defaults)) {
				$query[$key] = $value;
			}
		}

		return $query;
	}

	// Saves a comment into database
	public function save ($comment, array $contents, $editing = false, $thread = 'auto')
	{
		// Decide action based on if comment is being edited
		$action = ($editing === true) ? 'update' : 'insert';

		// Prepare a query statement
		$query = $this->prepareQuery ($comment, $contents, $this->$action);

		// Attempt to write comment to database
		$status = $this->write ($action, $thread, $query);

		return $status;
	}

	// Deletes a comment from database
	public function delete ($comment, $delete = false)
	{
		// Initial query statement is comment ID
		$query = array ('comment' => $comment);

		// Only change status to deleted if told to
		if ($delete !== true) {
			$query['status'] = 'deleted';
		}

		// Attempt to write comment changes to database
		$status = $this->write ('delete', 'auto', $query, $delete);

		return $status;
	}
}
