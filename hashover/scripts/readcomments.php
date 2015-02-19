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

	class ReadComments
	{
		public $data,
		       $commentlist,
		       $subfile_count = array(),
		       $total_count = 1,
		       $cmt_count = 1,
		       $show_count = '';

		public function __construct($setup)
		{
			$this->setup = $setup;

			// Instantiate necessary class data format class
			$data_class = 'Parse' . strtoupper($setup->data_format);
			$this->data = new $data_class($setup);

			// Query a list of comments
			$this->commentlist = $this->data->query();

			// Check for missing comments
			foreach (array_keys($this->commentlist) as $key) {
				$key_parts = explode('-', $key);

				// Start by checking for the first comment
				$cmt_tree = '';

				foreach ($key_parts as $reply) {
					// Then check for comments counting backward from the current
					for ($i = 1; $i <= $reply; $i++) {
						// Current level to check for
						$current = $cmt_tree . (!empty($cmt_tree) ? '-' : '') . $i;

						if (!isset($this->commentlist[$current])) {
							// List missing comment
							$this->commentlist[$current] = 'deleted';

							// Count missing comment
							$this->count_comments($current);
						}
					}

					// Add current reply level to tree
					$cmt_tree .= (!empty($cmt_tree) ? '-' : '') . $reply;
				}

				// Count comment
				$this->count_comments($key);
			}

			// Sort comments by their keys alphabetically in ascending order
			uksort($this->commentlist, 'strnatcasecmp');

			// Format comment count
			$prime_plural = ($this->cmt_count != 2) ? 1 : 0;
			$showing_cmts = $setup->text['showing_cmts'][$prime_plural];
			$this->show_count = str_replace('_NUM_', $this->cmt_count - 1, $showing_cmts);

			// Add reply count if there are any
			if ($this->total_count != $this->cmt_count) {
				$reply_plural = (abs($this->total_count - $this->cmt_count) > 1) ? 1 : 0;
				$count_replies = str_replace('_NUM_', $this->total_count - 1, $setup->text['count_replies'][$reply_plural]);
				$this->show_count .= ' (' . $count_replies . ')';
			}
		}

		// Read comments
		public function read($skipdeleted = false, $fullpath = false)
		{
			$comments = array();
			$url_regex = '(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)';

			foreach ($this->commentlist as $key => $comment) {
				if ($comment == 'deleted') {
					if ($skipdeleted) {
						continue;
					}

					$comments[$key]['status'] = 'deleted';
				} else {
					$comments[$key] = $this->data->read($comment, $fullpath);

					// Replace [img] tags with external image placeholder if enabled
					if ($this->setup->mode != 'javascript') {
						// Add HTML anchor tag to URLs (hyperlinks)
						$comments[$key]['body'] = preg_replace('/' . $url_regex . '([\s]{0,})/i', '<a href="\\1" target="_blank">\\1</a>', $comments[$key]['body']);

						// Replace [img] tags with hyperlinks
						$comments[$key]['body'] = preg_replace('/\[img\]<a.*?>' . $url_regex . '<\/a>\[\/img\]/i', '<a href="\\1" target="_blank">\\1</a>', $comments[$key]['body']);
					}
				}
			}

			return $comments;
		}

		// Count the comments
		public function count_comments($comment)
		{
			// Count replies
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

			// Count all comments
			$this->total_count++;
		}
	}

?>
