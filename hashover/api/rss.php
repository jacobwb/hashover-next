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

	// Use UTF-8 character set
	ini_set('default_charset', 'UTF-8');

	// Enable display of PHP errors
	ini_set('display_errors', true);
	error_reporting(E_ALL);

	// Move up a directory
	chdir('../');

	// Autoload class files
	function __autoload($classname) {
		$classname = strtolower($classname);

		if (!@include('./scripts/' . $classname . '.php')) {
			exit('<b>HashOver</b>: "' . $classname . '.php" file could not be included!');
		}
	}

	// Instantiate necessary classes
	$setup = new Setup('api');
	$statistics = new Statistics('php');
	$statistics->execution_start(); // Start statistics
	$read_comments = new ReadComments($setup);

	// Display error if the API is disabled
	if ($setup->api_status('rss') == 'disabled') {
		exit($setup->escape_output('<b>HashOver</b>: This API is not enabled.', 'single'));
	}

	// Read comments
	$comments = $read_comments->read(true);
	$rss_feed = '';

	// Parse comments
	foreach ($comments as $key => $comment) {
		$comment['perma'] = 'c' . str_replace('-', 'r', $key);
		$comment['date'] = strtotime(str_replace(' - ', ' ', $comment['date']));

		// Error handling
		if ($comment !== false) {
			$data_search = array('&', '<br>\n', '\n', '\r', '    ');
			$data_replace = array('&amp;', ' ', ' ', ' ', ' ');

			$comment['body'] = str_replace(array('\n', '\r'), ' ', $comment['body']);
			$title = $comment['name'] . ' : ';

			if (strlen($comment['body']) > 40) {
				$title .= substr($comment['body'], 0, 40) . '...';
			} else {
				$title .= $comment['body'];
			}

			$rss_feed .= "\t\t" . '<item>' . PHP_EOL;
			$rss_feed .= "\t\t\t" . '<title>' . htmlspecialchars(strip_tags(html_entity_decode($title))) . '</title>' . PHP_EOL;
			$rss_feed .= "\t\t\t" . '<name>' . htmlspecialchars(strip_tags(html_entity_decode($comment['name']))) . '</name>' . PHP_EOL;
			$rss_feed .= "\t\t\t" . '<description>' . str_replace(array('\n', '\r'), '', htmlspecialchars(strip_tags($comment['body'], '<br><a><b><i><u><s><blockquote><img>'))) . '</description>' . PHP_EOL;
			$rss_avatar = 'http://' . $setup->domain . '/' . $setup->root_dir . '/scripts/avatars.php?format=' . $setup->image_format . '&amp;size=' . $setup->icon_size;

			// Added avatar URLs to feed
			if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $comment['name'])) {
				$rss_avatar .= '&amp;username=' . $comment['name'];
			} else {
				if (preg_match('/(twitter.com\/[a-zA-Z0-9_@]{1,29}$)/i', $comment['website'])) {
					$web_username = preg_replace('/(.*?twitter\.com)\/([a-zA-Z0-9_]{1,20}$)/i', '\\2', $comment['website']);
					$rss_avatar .= '&amp;username=@' . $web_username;
				}
			}

			if (!empty($comment['email'])) {
				$rss_avatar .= '&amp;email=' . md5(strtolower(trim($setup->encryption->decrypt($comment['email'], $comment['encryption']))));
			} else {
				$rss_avatar = $setup->root_dir . '/images/' . $setup->image_format . 's/avatar.' . $setup->image_format;
			}

			$rss_feed .= "\t\t\t" . '<avatar>' . $rss_avatar . '</avatar>' . PHP_EOL;
			$rss_feed .= "\t\t\t" . '<likes>' . $comment['likes'] . '</likes>' . PHP_EOL;

			if ($setup->allows_dislikes == 'yes') {
				$rss_feed .= "\t\t\t" . '<dislikes>' . $comment['dislikes'] . '</dislikes>' . PHP_EOL;
			}

			$rss_feed .= "\t\t\t" . '<pubDate>' . date('D, d M Y H:i:s O', $comment['date']) . '</pubDate>' . PHP_EOL;
			$rss_feed .= "\t\t\t" . '<guid>' . $setup->metadata['url'] . '#' . $comment['perma'] . '</guid>' . PHP_EOL;
			$rss_feed .= "\t\t\t" . '<link>' . $setup->metadata['url'] . '#' . $comment['perma'] . '</link>' . PHP_EOL;
			$rss_feed .= "\t\t" . '</item>' . PHP_EOL;
		} else {
			// Substract from count if XML is invaild
			$read_comments->total_count--;
		}
	}

	// Tell browser this is XML/RSS
	header('Content-Type: application/xml');

	echo '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL;
	echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">', PHP_EOL;
	echo "\t", '<channel>', PHP_EOL;
	echo "\t\t", '<title>', $setup->metadata['title'], '</title>', PHP_EOL;
	echo "\t\t", '<link>', $setup->metadata['url'], '</link>', PHP_EOL;
	echo "\t\t", '<description>', str_replace('_NUM_', $read_comments->total_count - 1, ($read_comments->total_count == 1) ? $setup->text['showing_cmts'][0] : $setup->text['showing_cmts'][1]), '</description>', PHP_EOL;
	echo "\t\t", '<atom:link href="http://', $setup->domain, $_SERVER['PHP_SELF'], '?url=', $setup->metadata['url'], '" rel="self"></atom:link>', PHP_EOL;
	echo "\t\t", '<language>en-us</language>', PHP_EOL;
	echo "\t\t", '<ttl>40</ttl>', PHP_EOL;
	echo iconv('UTF-8', 'UTF-8//IGNORE', $rss_feed);
	echo "\t", '</channel>', PHP_EOL;
	echo '</rss>';

	$statistics->execution_end();

?>
