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

		// Check if database type is SQLite
		if ($this->databaseType === 'sqlite') {
			// If so, construct SQLite file name
			$file = sprintf ('%s/%s.sqlite',
				$setup->commentsRoot, $this->databaseName
			);

			// Instantiate an SQLite data object
			$this->database = new \PDO ('sqlite:' . $file);

			// And change file permissions
			@chmod ($file, 0600);
		} else {
			// If not, create SQL server connection statement
			$connection = implode (';', array (
				'host=' . $this->databaseHost,
				'port=' . $this->databasePort,
				'dbname=' . $this->databaseName,
				'charset=' . $this->databaseCharset
			));

			// And create SQL server data object
			$this->database = new \PDO (
				// PDO driver and connection details
				$this->databaseType . ':' . $connection,

				// Database user as configured
				$this->databaseUser,

				// Database password as configured
				$this->databasePassword,

				// We want the number of found (matched) rows,
				// not the number of changed rows
				array (
					\PDO::MYSQL_ATTR_FOUND_ROWS => true
				)
			);
		}
	}

	// Prepares and executes an SQL statement
	protected function executeStatement ($statement, $data = null)
	{
		// Prepare statement
		$prepare = $this->database->prepare ($statement);

		// Check if prepare was successful
		if ($prepare !== false) {
			// If so, attempt to execute statement
			$execute = $prepare->execute ($data);

			// And return statement object if execute was successful
			if ($execute !== false) {
				return $prepare;
			}
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

		// Create a statement using specific columns
		foreach ($columns as $name => $value) {
			// Decide type based on value type
			$type = is_numeric ($value) ? 'INTEGER' : 'TEXT';

			// And add column to statement
			$statement[] = sprintf ('`%s` %s', $name, $type);
		}

		return $statement;
	}

	// Reads and returns specific metadata from database
	public function readMeta ($name, $thread = 'auto', $global = false)
	{
		// Get thread
		$thread = $this->getCommentThread ($thread);

		// Prepared data for statement execution
		$prepared = array (
			'domain' => $this->setup->website
		);

		// Choose statement for supported metadata
		switch ($name) {
			// Latest comments
			case 'latest-comments': {
				// Initial statement
				$statements = array (
					'SELECT * FROM `comments`',
					'WHERE (status IS NULL OR status="approved")',
					'AND domain=:domain'
				);

				// Check if we are getting metadata from multiple threads
				if ($global === false) {
					// If so, add thread condition to statement
					$statements[] = 'AND thread=:thread';

					// And add thread to prepared data
					$prepared['thread'] = $thread;
				}

				// Sort comments by date
				$statements[] = 'ORDER BY `date` DESC';

				// Limit comments to configured maximum
				$statements[] = 'LIMIT ' . $this->setup->latestMax;

				break;
			}

			// All others, just try to read as-is
			default: {
				// Initial statement
				$statements = array (
					sprintf ('SELECT * FROM `%s`', $name),
					'WHERE domain=:domain'
				);

				// Check if we are getting metadata from multiple threads
				if ($global === false) {
					// Add thread condition to statement
					$statements[] = 'AND thread=:thread';

					// And add thread to prepared data
					$prepared['thread'] = $thread;
				}

				break;
			}
		}

		// Convert statements array into string
		$statement = implode (' ', $statements);

		// Query statement
		$results = $this->executeStatement ($statement, $prepared);

		// Check if the query was successful
		if ($results !== false) {
			// If so, attempt to get all metadata
			$fetch_all = $results->fetchAll (\PDO::FETCH_ASSOC);

			// Check if metadata read successfully
			if (!empty ($fetch_all)) {
				// If so, return first for if metadata is page info
				if ($name === 'page-info') {
					return $fetch_all[0];
				}

				// Otherwise, return all metadata
				return $fetch_all;
			}
		}

		return false;
	}

	// Creates comment table if it doesn't exist
	protected function createTable ($name, array $columns)
	{
		// Statement for creating an initial table
		$statement = sprintf (
			'CREATE TABLE IF NOT EXISTS `%s` (%s)',
			$name, implode (', ', $columns)
		);

		// Execute statement
		$created = $this->executeStatement ($statement);

		// Throw exception on failure
		if ($created === false) {
			throw new \Exception (sprintf (
				'Failed to create "%s" table!', $name
			));
		}
	}

	// Get formatted string of array keys
	protected function formatKeys (array $data, $format, $glue = ', ')
	{
		// Initial formatted output
		$formatted = array ();

		// Add each formatted array key
		foreach (array_keys ($data) as $key) {
			$formatted[] = str_replace ('%s', $key, $format);
		}

		// And convert formatted array to string
		$statement = implode ($glue, $formatted);

		return $statement;
	}

	// Saves metadata to specific metadata JSON file
	public function saveMeta ($name, array $data, $thread = 'auto')
	{
		// Get thread
		$thread = $this->getCommentThread ($thread);

		// Add website domain and thread to data
		$data = array_merge (array (
			'domain' => $this->setup->website,
			'thread' => $thread
		), $data);

		// Get metadata table creation statements
		$creation_statement = $this->creationArray ($data);

		// Attempt to create metadata table
		$this->createTable ($name, $creation_statement);

		// Update data in specific columns
		$save = implode (' ', array (
			sprintf ('UPDATE `%s`', $name),
			'SET ' . $this->formatKeys ($data, '%s=:%s'),
			'WHERE domain=:domain',
			'AND thread=:thread'
		));

		// Execute statement
		$saved = $this->executeStatement ($save, $data);

		// Check if we failed to update any rows
		if ($saved !== false and $saved->rowCount () === 0) {
			// If so, create metadata column names list
			$column_names = $this->formatKeys ($data, '`%s`');

			// Create metadata column values list
			$column_values = $this->formatKeys ($data, ':%s');

			// Insert data into specific columns
			$save = sprintf (
				'INSERT INTO `%s` (%s) VALUES (%s)',
				$name, $column_names, $column_values
			);

			// Execute statement
			$saved = $this->executeStatement ($save, $data);
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

		// Add website domain and thread to data
		$data = array_merge ($data, array (
			'domain' => $this->setup->website,
			'thread' => $thread
		));

		// Decide on an action
		switch ($action) {
			// Action for posting a comment
			case 'insert': {
				// Construct SQL statement
				$query = sprintf (
					// Insertion statement
					'INSERT INTO `comments` VALUES (%s)',

					// Get list of table columns
					$this->formatKeys ($this->commentsTable, ':%s')
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
					'UPDATE `comments`',
					'SET ' . $columns,
					'WHERE domain=:domain',
					'AND thread=:thread',
					'AND comment=:comment'
				));

				break;
			}

			// Action for deleting a comment
			case 'delete': {
				// Check if we're actually deleting the comment
				if ($alt === true) {
					// If so, use delete statement
					$query = implode (' ', array (
						'DELETE FROM `comments`',
						'WHERE domain=:domain',
						'AND thread=:thread',
						'AND comment=:comment'
					));
				} else {
					// If not, use status update statement
					$query = implode (' ', array (
						'UPDATE `comments`',
						'SET status=:status',
						'WHERE domain=:domain',
						'AND thread=:thread',
						'AND comment=:comment'
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
		// Create comments table creation statements
		$statement = $this->creationArray ($this->commentsTable);

		// Create initial comments if it doesn't exist
		$this->createTable ('comments', $statement);
	}

	// Queries unique rows as of a specific column an array
	protected function queryColumn ($column)
	{
		// Select unique thread names
		$results = $this->executeStatement (sprintf (
			'SELECT DISTINCT `%s` FROM `comments`', $column
		));

		// Check if query was successful
		if ($results !== false) {
			// If so, fetch all rows in column
			$fetch_all = $results->fetchAll (\PDO::FETCH_ASSOC);

			// Return column as array
			return array_column ($fetch_all, $column);
		}

		// Otherwise, return an empty array
		return array ();
	}

	// Queries an array of websites
	public function queryWebsites ()
	{
		return $this->queryColumn ('domain');
	}

	// Queries an array of comment threads
	public function queryThreads ()
	{
		return $this->queryColumn ('thread');
	}

	// These methods are not necessary in SQL
	public function addLatestComment () {}
	public function removeFromLatest () {}
}
