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
		}
	}

	class ReadComments
	{
		public $setup;
		public $data;
		public $commentlist;
		public $threadCount = array ();
		public $totalCount = 1;
		public $primaryCount = 1;

		public
		function __construct (Setup $setup)
		{
			$this->setup = $setup;

			// Instantiate necessary class data format class
			$data_class = 'Parse' . strtoupper ($setup->dataFormat);
			$this->data = new $data_class ($setup);

			// Query a list of comments
			$this->commentlist = $this->data->query ();

			// Check for missing comments
			foreach ($this->commentlist as $key) {
				$key_parts = explode ('-', $key);
				$cmt_tree = '';

				foreach ($key_parts as $reply) {
					// Check for comments counting backward from the current
					for ($i = 1; $i <= $reply; $i++) {
						// Current level to check for
						$current = $cmt_tree;

						if (!empty ($cmt_tree)) {
							$current .= '-';
						}

						$current .= $i;

						if (isset ($this->commentlist[$current])) {
							continue;
						}

						// List missing comment
						$this->commentlist[$current] = 'deleted';

						// Count missing comment
						$this->countComments ($current);
					}

					// Add current reply level to tree
					if (!empty ($cmt_tree)) {
						$cmt_tree .= '-';
					}

					$cmt_tree .= $reply;
				}

				// Count comment
				$this->countComments ($key);
			}

			// Sort comments by their keys alphabetically in ascending order
			uksort ($this->commentlist, 'strnatcasecmp');
		}

		// Read comments
		public
		function read ($skipdeleted = false, $fullpath = false)
		{
			$comments = array ();

			foreach ($this->commentlist as $key => $comment) {
				if ($comment === 'deleted') {
					if ($skipdeleted) {
						continue;
					}

					$comments[$key]['status'] = 'deleted';
				} else {
					$read_comment = $this->data->read ($comment, $fullpath);

					// See if it read successfully
					if ($read_comment) {
						// If so, add the comment to output
						$comments[$key] = $read_comment;
					} else {
						// If not, consider it deleted
						$comments[$key]['status'] = 'deleted';
						continue;
					}
				}
			}

			return $comments;
		}

		// Count the comments
		protected
		function countComments ($comment)
		{
			// Count replies
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

			// Count all comments
			$this->totalCount++;
		}
	}

?>
