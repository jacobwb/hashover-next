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
	public $setup;
	public $storageMode;
	public $database;

	public function __construct (Setup $setup)
	{
		$this->setup = $setup;
		$this->storageMode = $this->databaseType;

		try {
			if ($this->databaseType === 'sqlite') {
				$sqlite_file = $setup->commentsDirectory . '/' . $this->databaseName . '.sqlite';
				$this->database = new \PDO ('sqlite:' . $sqlite_file);
			} else {
				$sql_connection = implode (';', array (
					'host=' . $this->databaseHost,
					'dbname=' . $this->databaseName,
					'charset=' . $this->databaseCharset
				));

				$this->database = new \PDO (
					$this->databaseType . ':' . $sql_connection,
					$this->databaseUser,
					$this->databasePassword
				);
			}
		} catch (\PDOException $error) {
			throw new \Exception ($error->getMessage ());
		}
	}

	public function getCommentThread ($thread)
	{
		return ($thread !== 'auto') ? $thread : $this->setup->threadName;
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

	// Get items column entries as array
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

	// Read and return specific metadata from JSON file
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

	// Create table creation statement from array
	protected function creationStatement (array $columns)
	{
		$statement = array ();

		// Check if the array is associative
		if (array_keys ($columns) !== range (0, count ($columns) - 1)) {
			// If so, create a statement using specific columns
			foreach ($columns as $name => $value) {
				$type = is_numeric ($value) ? 'INTEGER' : 'TEXT';
				$statement[] = sprintf ('`%s` %s', $name, $type);
			}
		} else {
			// If not, create statement using generic "items" column
			$statement[] = '`items` TEXT';
		}

		return $statement;
	}

	// Prepare and execute an SQL statement
	protected function executeStatement ($statement, $data = null)
	{
		try {
			// Prepare statement
			$prepare = $this->database->prepare ($statement);

			// Attempt to execute statement
			if ($prepare !== false) {
				return $prepare->execute ($data);
			}

			return $prepare;

		} catch (\PDOException $error) {
			throw new \Exception ($error->getMessage ());
		}
	}

	// Create comment table if it doesn't exist
	protected function createTable ($name, array $columns)
	{
		// Statement for creating initial comment thread table
		$statement  = 'CREATE TABLE IF NOT EXISTS `' . $name . '` ';
		$statement .= '(' . implode (', ', $columns) . ')';

		// Execute statement
		$created = $this->executeStatement ($statement);

		// Throw exception on failure
		if ($created === false) {
			throw new \Exception (sprintf (
				'Failed to create table "%s"',
				$this->setup->threadName
			));
		}

		return true;
	}

	// Create table query statement from array
	protected function queryStatement (array $columns)
	{
		$statement = array ();

		foreach ($columns as $name => $value) {
			$statement[] = ':' . $name;
		}

		return $statement;
	}

	// Delete all rows from a given table
	protected function deleteAllRows ($table)
	{
		// Deletion statement
		$statement = 'DELETE FROM `' . $table . '`';

		// Execute statement
		$deleted = $this->executeStatement ($statement);

		// Throw exception on failure
		if ($deleted === false) {
			throw new \Exception ('Failed to delete existing metadata!');
		}
	}

	// Save metadata to specific metadata JSON file
	public function saveMeta ($name, array $data, $thread = 'auto', $global = false)
	{
		// Metadata table
		$metadata_table = $this->getMetaTable ($name, $thread, $global);

		// Create metadata table creation statement
		$creation_statement = $this->creationStatement ($data);

		// Attempt to create metadata table
		$this->createTable ($metadata_table, $creation_statement);

		// Delete existing data from database
		$this->deleteAllRows ($metadata_table);

		// Check if the array is associative
		if (array_keys ($data) !== range (0, count ($data) - 1)) {
			// If so, create metadata columns insertion statement array
			$columns = $this->queryStatement ($data);

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
			throw new \Exception ('Failed to save metadata!');
		}
	}

	public function write ($action, $thread, array $array, $alt = false)
	{
		$thread = $this->getCommentThread ($thread);

		switch ($action) {
			// Action for posting a comment
			case 'insert': {
				$columns = implode (', ', array (
					':id',
					':body',
					':status',
					':date',
					':name',
					':password',
					':login_id',
					':email',
					':encryption',
					':email_hash',
					':notifications',
					':website',
					':ipaddr',
					':likes',
					':dislikes'
				));

				$query  = 'INSERT INTO `' . $thread . '` ';
				$query .= 'VALUES (' . $columns . ')';

				break;
			}

			// Action for editing a comment
			case 'update': {
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

				$query  = 'UPDATE `' . $thread . '` ';
				$query .= 'SET ' . $columns . ' ';
				$query .= 'WHERE id=:id';

				break;
			}

			// Action for deleting a comment
			case 'delete': {
				// Check if we're actually deleting the comment
				if ($alt === true) {
					// If so, use delete statement
					$query  = 'DELETE FROM `' . $thread . '` ';
					$query .= 'WHERE id=:id';
				} else {
					// If not, use status update statement
					$query  = 'UPDATE `' . $thread . '` ';
					$query .= 'SET status=:status ';
					$query .= 'WHERE id=:id';
				}

				break;
			}
		}

		// Execute statement
		$queried = $this->executeStatement ($query, $array);

		// Throw exception on failure
		if ($queried === false) {
			throw new \Exception ('Failed to write to database!');
		}

		return true;
	}

	// Create comment thread if it doesn't exist
	public function checkThread ()
	{
		$thread = $this->setup->threadName;

		return $this->createTable ($thread, array (
			'`id` TEXT',
			'`body` TEXT',
			'`status` TEXT',
			'`date` TEXT',
			'`name` TEXT',
			'`password` TEXT',
			'`login_id` TEXT',
			'`email` TEXT',
			'`encryption` TEXT',
			'`email_hash` TEXT',
			'`notifications` TEXT',
			'`website` TEXT',
			'`ipaddr` TEXT',
			'`likes` INTEGER',
			'`dislikes` INTEGER'
		));
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

		// Query statement
		$results = $this->database->query ($statement);

		// Return threads if successful
		if ($results !== false) {
			$fetch_all = $results->fetchAll (\PDO::FETCH_ASSOC);
			$threads = array ();

			foreach ($fetch_all as $table) {
				$threads[] = $table['name'];
			}

			return $threads;
		}

		return false;
	}
}
