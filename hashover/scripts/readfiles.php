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
	class ReadFiles
	{
		public $storage_format,
		       $setup,
		       $metadata = array();

		public function __construct($setup)
		{
			$this->setup = $setup;
			$this->storage_format = 'flat-file';
			$metadata_file = $this->setup->dir . '/.metadata';
			$update_metadata = false;

			// Read exist metadata file if one exists
			if (file_exists($metadata_file)) {
				$json_metadata = json_decode(file_get_contents($metadata_file), true);
				$this->setup->metadata = array_merge($this->setup->metadata, $json_metadata);
			}

			// Check if comment thread directory exists
			if ($setup->mode != 'api') {
				if (file_exists(dirname($metadata_file)) and is_writable(dirname($metadata_file))) {
					// Check whether the page and metadata URLs differ
					if ($this->setup->metadata['title'] != $setup->page_title) {
						$this->setup->metadata['title'] = $setup->page_title;
						$update_metadata = true;
					}

					// Check whether the page and metadata titles differ
					if ($this->setup->metadata['url'] != $setup->page_url) {
						$this->setup->metadata['url'] = $setup->page_url;
						$update_metadata = true;
					}

					// Update metadata if the data has changed
					if ($update_metadata == true) {
						if ($this->save_metadata($this->setup->metadata, $metadata_file) == false) {
							exit($setup->escape_output('<b>HashOver</b>: Failed to create metadata file.', 'single'));
						}
					}
				}
			}
		}

		// Save comment metadata
		public function save_metadata(array $data, $file)
		{
			if (defined('JSON_PRETTY_PRINT')) {
				$json = str_replace('    ', "\t", json_encode($data, JSON_PRETTY_PRINT));
			} else {
				$json = str_replace('    ', "\t", json_encode($data));
			}

			return file_put_contents($file, $json);
		}

		// Read directory contents, put filenames in array, count files
		public function load_files($extension, array $files = array(), $auto = true)
		{
			$comments = array();

			if ($auto) {
				$files = glob($this->setup->dir . '/*.' . $extension, GLOB_NOSORT);
			}

			foreach ($files as $file) {
				$key = basename($file, '.' . $extension);
				$comments[$key] = (string) $key;
			}

			return $comments;
		}
	}

?>
