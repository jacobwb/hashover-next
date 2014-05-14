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
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	// Read and count comments
	class ReadComments extends HashOver {
		public $hashover;
		public $comments = array();
		public $top_likes = array();
		public $show_count = '';
		public $subfile_count = array();
		public $total_count = 1;
		public $cmt_count = 1;

		// Read directory contents, put filenames in array, count files
		public function read($output = false) {
			switch ($this->setting['data_format']) {
				default: exit($this->escape_output('<b>HashOver:</b> Unsupported data format!', 'single')); break;

				case ($this->setting['data_format'] == 'xml' or $this->setting['data_format'] == 'json'):
					foreach (glob($this->dir . '/*.' . $this->setting['data_format'], GLOB_NOSORT) as $file) {
						$key = basename($file, '.' . $this->setting['data_format']);
						$this->comments[$key]['file'] = $file;

						if ($output == true) {
							$this->comments[$key]['permalink'] = 'c' . str_replace('-', 'r', $key);

							if (preg_match('/-/', $key)) {
								$file_parts = explode('-', $key);
								$this->comments[$key]['permatext'] = end($file_parts);
							} else {
								$this->comments[$key]['permatext'] = $key;
							}

							// Calculate CSS padding for reply indention
							if (($dashes = substr_count(basename($file), '-')) != '0') {
								$this->comments[$key]['indent'] = ($dashes >= 1) ? (($this->setting['icon_size'] + 4) * $dashes) + 16 : ($this->setting['icon_size'] + 20) * $dashes;
							} else {
								$this->comments[$key]['indent'] = '0';
							}

							// Load XML or JSON data
							if ($this->setting['data_format'] == 'xml') {
								libxml_use_internal_errors(true);
								$read_comment = @simplexml_load_file($file);

								if ($read_comment !== false) {
									$this->comments[$key] = array_merge($this->comments[$key], (array)$read_comment);
									$this->comments[$key] = array_merge($this->comments[$key], $this->comments[$key]['@attributes']);
									$this->comments[$key]['@attributes'] = null;
								}
							} else {
								$read_comment = @json_decode(file_get_contents($file), true);

								if ($read_comment !== false) {
									$this->comments[$key] = array_merge($this->comments[$key], (array)$read_comment);
								}
							}
						}

						$this->count_comments($key);
					}

					break;

				case 'sqlite':
					// Fix me!
					exit('Not implemented yet!');
					break;
			}

			// Sort files ascending alphabetically
			if ($output == true) {
				uksort($this->comments, 'strnatcasecmp');
			}

			$this->show_count = ($this->cmt_count - 1) . ' Comment' . (($this->cmt_count != 2) ? 's' : '');

			if ($this->total_count != $this->cmt_count) {
				$this->show_count .= ' (' . ($this->total_count - 1) . ' counting repl';
				$this->show_count .= (abs($this->total_count - $this->cmt_count) > 1) ? 'ies)' : 'y)';
			}
		}

		public function count_comments($comment) {
			// Count all comments
			if (preg_match('/-/', $comment)) {
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
