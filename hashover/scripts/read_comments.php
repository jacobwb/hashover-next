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

	// Read, count, and create deleted comment note
	function read_comments($dir, $check) {
		global $root_dir, $show_cmt, $cmt_count, $total_count, $subfile_count, $deleted_files, $domain, $script_query, $template, $icon_size;
		$files = array();

		// Read directory contents, put filenames in array, count files
		foreach (glob($dir . '/*.xml', GLOB_NOSORT) as $file) {
			$files[basename($file, '.xml')] = $file;
			$subfile_count[$file] = '0';
			$total_count++;

			if (!preg_match('/-/', basename($file, '.xml'))) {
				$cmt_count++;
			}
		}

		// Sort files ascending alphabetically
		uksort($files, 'strnatcasecmp');

		foreach ($files as $file) {
			$cmt_tree = '';

			foreach (explode('-', basename($file, '.xml')) as $reply) {
				for ($i = 1; $i <= $reply; $i++) {
					if (!in_array($dir . '/' . $cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i) . '.xml', $files)) {
						if (!in_array($dir . '/' . $cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i) . '.xml', $deleted_files)) {
							deletion_notice($dir . '/' . $cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i) . '.xml', $show_cmt, $check); // Display notice
							$deleted_files[] = $dir . '/' . $cmt_tree . ((!empty($cmt_tree) ? '-' : '') . $i) . '.xml';
						}
					}
				}

				$cmt_tree .= ((!empty($cmt_tree) ? '-' : '')) . $reply;
			}

			// Check whether to generate output
			if (!empty($check) and $check == 'yes' or $check == 'true') {
				$show_cmt = parse_comments($file, $show_cmt, 'yes');
			}

			// Count comment
			if (preg_match('/-/', basename($file, '.xml'))) {
				$thread = $dir . '/' . basename($file, '-' . end(explode('-', basename($file)))) . '.xml';
				$subfile_count["$thread"] = (isset($subfile_count["$thread"])) ? $subfile_count["$thread"] + 1 : 1;
			}

			$subfile_count["$file"]++; // Count comment
		}
	}

?>
