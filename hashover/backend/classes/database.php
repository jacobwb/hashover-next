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


// Database! Database! Just living in the database! Wow! Wow!
class Database extends Secrets
{
	protected $setup;
	protected $database;

	// Initial comment data
	protected $commentsTable = array (
		'domain' => '',
		'thread' => '',
		'comment' => '',
		'body' => '',
		'status' => '',
		'date' => '',
		'name' => '',
		'password' => '',
		'login_id' => '',
		'email' => '',
		'encryption' => '',
		'email_hash' => '',
		'notifications' => '',
		'website' => '',
		'ipaddr' => '',
		'likes' => 0,
		'dislikes' => 0
	);

	public function __construct (Setup $setup)
	{
		// Store parameters as properties
		$this->setup = $setup;

		try {
			// Check if database type is SQLite
			if ($this->databaseType === 'sqlite') {
				// If so, construct SQLite file name
				$file = sprintf ('%s/%s.sqlite',
					$setup->commentsDirectory, $this->databaseName
				);

				// Instantiate an SQLite data object
				$this->database = new \PDO ('sqlite:' . $file);
			} else {
				// If not, create SQL server connection statement
				$connection = implode (';', array (
					'host=' . $this->databaseHost,
					'dbname=' . $this->databaseName,
					'charset=' . $this->databaseCharset
				));

				// And create SQL server data object
				$this->database = new \PDO (
					$this->databaseType . ':' . $connection,
					$this->databaseUser,
					$this->databasePassword
				);
			}
		} catch (\PDOException $error) {
			throw new \Exception ($error->getMessage ());
		}
	}

	// Prepares and executes an SQL statement
	protected function executeStatement ($statement, $data = null)
	{
		try {
			// Prepare statement
			$prepare = $this->database->prepare ($statement);

			// Attempt to execute statement
			if ($prepare !== false) {
				return $prepare->execute ($data);
			}
		} catch (\PDOException $error) {
			throw new \Exception ($error->getMessage ());
		}

		return false;
	}

	// Returns a given thread or thread from setup
	protected function getCommentThread ($thread)
	{
		// Return thread from setup if thread is auto
		if ($thread === 'auto') {
			return $this->setup->threadName;
		}

		// Otherwise, return given thread
		return $thread;
	}

	// Creates table creation statement from array
	protected function creationArray (array $columns)
	{
		// Initial statement
		$statement = array ();

		// Check if the array is associative
		if (array_keys ($columns) !== range (0, count ($columns) - 1)) {
			// If so, create a statement using specific columns
			foreach ($columns as $name => $value) {
				// Decide type based on value type
				$type = is_numeric ($value) ? 'INTEGER' : 'TEXT';

				// And add column to statement
				$statement[] = sprintf ('`%s` %s', $name, $type);
			}
		} else {
			// If not, create statement using generic "items" column
			$statement[] = '`items` TEXT';
		}

		return $statement;
	}

	// Gets the appropriate metadata table name
	protected function getMetaTable ($name, $thread, $global)
	{
		// Check if we're getting metadata for a specific thread
		if ($global !== true) {
			// If so, use the thread's table
			if ($thread === 'auto') {
				$table = $this->setup->threadName . '/metadata';
			} else {
				$table = $thread . '/metadata';
			}
		} else {
			// If not, use the global metadata table
			$table = 'hashover-metadata';
		}

		// Final table name
		$table .= '/' . $name;

		return $table;
	}

	// Gets items column entries as array
	protected function getItems (array $rows)
	{
		// Initial items to return
		$items = array ();

		// Run through each item row
		foreach ($rows as $row) {
			$items[] = $row['items'];
		}

		return $items;
	}

	// Reads and returns specific metadata from database
	public function readMeta ($name, $thread = 'auto', $global = false)
	{
		// Metadata table
		$metadata_table = $this->getMetaTable ($name, $thread, $global);

		// Query statement array
		$statement = 'SELECT * FROM `' . $metadata_table . '`';

		// Query statement
		$results = $this->database->query ($statement);

		// Check if the query was successful
		if ($results !== false) {
			// If so, attempt to get all metadata
			$fetch_all = $results->fetchAll (\PDO::FETCH_ASSOC);

			// Check if we got the metadata
			if ($fetch_all !== false and isset ($fetch_all[0])) {
				// Return only the items column if present
				if (isset ($fetch_all[0]['items'])) {
					return $this->getItems ($fetch_all);
				}

				// Otherwise return the first row
				return $fetch_all[0];
			}
		}

		return false;
	}

	// Creates comment table if it doesn't exist
	protected function createTable ($name, array $columns)
	{
		// Statement for creating an initial table
		$statement = implode (' ', array (
			'CREATE TABLE IF NOT EXISTS `' . $name . '`',
			'(' . implode (', ', $columns) . ')'
		));

		// Execute statement
		$created = $this->executeStatement ($statement);

		// Throw exception on failure
		if ($created === false) {
			throw new \Exception (sprintf (
				'Failed to create "%s" table!', $name
			));
		}
	}

	// Creates table query statement from array
	protected function prepareArray (array $columns)
	{
		// Initial query statement
		$statement = array ();

		// Add each column to statement
		foreach ($columns as $name => $value) {
			$statement[] = ':' . $name;
		}

		return $statement;
	}

	// Deletes all rows from a given table
	protected function deleteAllRows ($table)
	{
		// Deletion statement
		$statement = 'DELETE FROM `' . $table . '`';

		// Execute statement
		$deleted = $this->executeStatement ($statement);

		// Throw exception on failure
		if ($deleted === false) {
			throw new \Exception (
				'Failed to delete existing metadata!'
			);
		}
	}

	// Saves metadata to specific metadata JSON file
	public function saveMeta ($name, array $data, $thread = 'auto', $global = false)
	{
		// Metadata table
		$metadata_table = $this->getMetaTable ($name, $thread, $global);

		// Create metadata table creation statement
		$creation_statement = $this->creationArray ($data);

		// Attempt to create metadata table
		$this->createTable ($metadata_table, $creation_statement);

		// Delete existing data from database
		$this->deleteAllRows ($metadata_table);

		// Check if the array is associative
		if (array_keys ($data) !== range (0, count ($data) - 1)) {
			// If so, create metadata columns insertion statement array
			$columns = $this->prepareArray ($data);

			// Insert data into specific columns
			$save  = 'INSERT INTO `' . $metadata_table . '` ';
			$save .= 'VALUES (' . implode (', ', $columns) . ')';

			// Execute statement
			$saved = $this->executeStatement ($save, $data);
		} else {
			// If not, insert each item individually
			$save  = 'INSERT INTO `' . $metadata_table . '` ';
			$save .= 'VALUES (:items)';

			// Insert each item individually
			for ($i = 0, $il = count ($data); $i < $il; $i++) {
				// Execute statement
				$saved = $this->executeStatement ($save, array (
					'items' => $data[$i]
				));

				// Stop on any failures
				if ($saved === false) {
					break;
				}
			}
		}

		// Throw exception on failure
		if ($saved === false) {
			throw new \Exception (
				'Failed to save metadata!'
			);
		}
	}

	// Writes new or changed content to database
	public function write ($action, $thread, array $data, $alt = false)
	{
		// Get thread
		$thread = $this->getCommentThread ($thread);

		// Decide on an action
		switch ($action) {
			// Action for posting a comment
			case 'insert': {
				// Get comments table columns as query array
				$columns = $this->prepareArray ($this->commentsTable);

				// Construct SQL statement
				$query = sprintf (
					'INSERT INTO `%s` VALUES (%s)',
					$thread, implode (', ', $columns)
				);

				break;
			}

			// Action for editing a comment
			case 'update': {
				// Columns to query
				$columns = implode (', ', array (
					'body=:body',
					'status=:status',
					'name=:name',
					'password=:password',
					'email=:email',
					'encryption=:encryption',
					'email_hash=:email_hash',
					'notifications=:notifications',
					'website=:website',
					'likes=:likes',
					'dislikes=:dislikes'
				));

				// Construct SQL statement
				$query = implode (' ', array (
					'UPDATE `' . $thread . '`',
					'SET ' . $columns,
					'WHERE id=:id'
				));

				break;
			}

			// Action for deleting a comment
			case 'delete': {
				// Check if we're actually deleting the comment
				if ($alt === true) {
					// If so, use delete statement
					$query = implode (' ', array (
						'DELETE FROM `' . $thread . '`',
						'WHERE id=:id'
					));
				} else {
					// If not, use status update statement
					$query = implode (' ', array (
						'UPDATE `' . $thread . '`',
						'SET status=:status',
						'WHERE id=:id'
					));
				}

				break;
			}
		}

		// Execute statement
		$queried = $this->executeStatement ($query, $data);

		// Throw exception on failure
		if ($queried === false) {
			throw new \Exception (
				'Failed to write to database!'
			);
		}

		return true;
	}

	// Check if comments table exists
	public function checkThread ()
	{
		// Get thread
		$thread = $this->setup->threadName;

		// Create comments table creation statements
		$statement = $this->creationArray ($this->commentsTable);

		// Create initial comments if it doesn't exist
		$this->createTable ($thread, $statement);
	}

	// Queries a list of comment threads
	public function queryThreads ()
	{
		// Database name
		$name = $this->databaseName;

		// Check if database type if SQLite
		if ($this->databaseType === 'sqlite') {
			// If so, use SQLite statement
			$statement  = 'SELECT * FROM sqlite_master ';
			$statement .= 'WHERE type=\'table\'';
		} else {
			// If not, use MySQL statement
			$statement  = 'SELECT * FROM INFORMATION_SCHEMA.TABLES ';
			$statement .= 'WHERE TABLE_TYPE=\'BASE TABLE\' ';
			$statement .= 'AND TABLE_SCHEMA=\'' . $name . '\'';
		}

		// Execute statement
		$results = $this->database->query ($statement);

		// Check if query was successful
		if ($results !== false) {
			// If so, fetch all threads
			$fetch_all = $results->fetchAll (\PDO::FETCH_ASSOC);

			// Return threads column
			return array_column ($fetch_all, 'name'));
		}

		return false;
	}
}
