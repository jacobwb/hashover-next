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

	// Function for adding deletion notice to output
	function deletion_notice($file, $variable, $check) {
		global $dir, $variable, $show_cmt, $array_count, $indention, $icon_fmt, $root_dir, $mode, $text, $subfile_count, $total_count, $cmt_count, $icons, $icon_size;

		// Check whether to generate output
		if (!empty($check) and $check == 'yes' or $check == 'true') {
			// Generate permalink
			$del_permatext = (end(explode('-', basename($file, '.xml'))));

			if (preg_match('/-/', basename($file, '.xml'))) {
				$del_permalink = 'c' . str_replace('-', 'r', basename($file, '.xml'));
				$del_permalink = basename($del_permalink, end(explode('r', $del_permalink))) . (end(explode('r', $del_permalink)));
			} else {
				$del_permalink = 'c' . (basename($file, '.xml'));
			}

			// Calculate CSS padding for reply indention
			if (($del_dashes = substr_count(basename($file), '-')) != '0' and $check == 'yes') {
				$del_indent = ($del_dashes >= 1) ? (($icon_size + 4) * $del_dashes) + 16 : ($icon_size + 20) * $del_dashes;
			} else {
				$del_indent = '0';
			}

			if ($icons == 'yes') {
				$icon_fmt = '<img width="' . $icon_size . '" height="' . $icon_size . '" src="' . $root_dir . 'images/delicon.png" alt="#' . $del_permatext . '" align="left">';
			} else {
				$icon_fmt = '<a href="#' . $del_permalink . '" title="Permalink">#' . $del_permatext . '</a>&nbsp;';
			}

			if ($mode == 'php') {
				$variable["$array_count"]['permalink'] = $del_permalink;
				$variable["$array_count"]['cmtclass'] = ((preg_match('/r/', $del_permalink)) ? 'cmtdiv reply' : 'cmtdiv');
				$variable["$array_count"]['indent'] = (($indention == 'right') ? '16px ' . $del_indent . 'px 12px 0px' : '16px 0px 12px ' . $del_indent . 'px');
				$variable["$array_count"]['deletion_notice'] = '<span class="cmtnote cmtnumber">' . $icon_fmt . '</span><div style="height: ' . $icon_size . 'px;" class="cmtbubble">' . PHP_EOL . '<b class="cmtnote cmtfont">' . $text['del_note'] . '</b>' . PHP_EOL . '</div>' . PHP_EOL;
				$array_count++;
				if (preg_match('/-/', basename($file, '.xml'))) $total_count++;
			} else {
				$show_cmt .= "\t" . '{' . PHP_EOL;
				$show_cmt .= "\t\t" . 'permalink: \'' . $del_permalink . '\',' . PHP_EOL;
				$show_cmt .= "\t\t" . 'cmtclass: \'' . ((preg_match('/r/', $del_permalink)) ? 'cmtdiv reply' : 'cmtdiv') . '\',' . PHP_EOL;
				$show_cmt .= "\t\t" . 'indent: \'' . (($indention == 'right') ? '16px ' . $del_indent . 'px 12px 0px' : '16px 0px 12px ' . $del_indent . 'px') . '\',' . PHP_EOL;
				$show_cmt .= "\t\t" . 'deletion_notice: \'<span class="cmtnote cmtnumber">' . $icon_fmt . '</span><div style="height: ' . $icon_size . 'px;" class="cmtbubble">\n<b class="cmtnote cmtfont">' . $text['del_note'] . '</b>\n</div>\n\'' . PHP_EOL;
				$show_cmt .= "\t" . '},' . PHP_EOL . PHP_EOL;
				if (preg_match('/-/', basename($file, '.xml'))) $total_count++;
			}
		}

		// Count deleted comment
		if (preg_match('/-/', basename($file, '.xml'))) {
			$thread = $dir . '/' . basename($file, '-' . end(explode('-', basename($file)))) . '.xml';
			$subfile_count["$thread"]++;
		} else {
			$total_count++;
			$cmt_count++;
		}
	}

?>
