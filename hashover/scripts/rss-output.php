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

	// Set feed title
	if (!empty($_GET['title'])) {
		$title = $_GET['title'];
	} else {
		$title = str_replace('www.', '', $this->setting['domain']) . ((basename($this->dir) == 'pages') ? '' : ': ' . str_replace('-', ' ', basename($this->dir)));

		if (!empty($_GET['rss'])) {
			$matches = array();
			preg_match('/<title>(.+)<\/title>/', file_get_contents($_GET['rss']), $matches);

			if (!empty($matches[1])) {
				header('Location: .' . $_SERVER['PHP_SELF'] . '?rss=' . $_GET['rss'] . '&title=' . $matches[1]);
			}
		}
	}

	// Read directory contents if conditions met
	if (file_exists($this->dir) and !empty($_GET['rss'])) {
		// Read comment files into array; convert date into UNIX timestamp
		foreach (glob($this->dir . '/*.xml', GLOB_NOSORT) as $file) {
			static $files = array();
			static $ac = 0;

			$files[$ac] = simplexml_load_file($file);
			$files[$ac]['date'] = strtotime(str_replace(array('- ', 'am', 'pm'), array('', ' AM PST', ' PM PST'), $files[$ac]->date));
			$files[$ac]['file'] = $file;
			$ac++;
		}

		// Sort by comment creation date
		usort($files, function ($a, $b) {
			return ($b['date'] - $a['date']);
		});

		foreach ($files as $rss_cmt) {
			static $rss_feed = '';

			// Error handling
			if ($rss_cmt !== false) {
				$data_search = array('&', '<br>\n', '\n', '\r', '    ');
				$data_replace = array('&amp;', ' ', ' ', ' ', ' ');

				$permalink = 'c' . str_replace('-', 'r', basename($rss_cmt['file'], '.xml'));
				$rss_feed .= "\t\t" . '<item>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<title>' . htmlspecialchars(strip_tags(html_entity_decode($rss_cmt->name))) . ' : ';
				$rss_feed .= ((strlen($rss_cmt->body) > 40) ? htmlspecialchars(strip_tags(html_entity_decode(substr(str_replace(array('\n', '\r'), ' ', $rss_cmt->body), 0, 40)))) . '...' : str_replace(array('\n', '\r'), ' ', htmlspecialchars(strip_tags(html_entity_decode($rss_cmt->body))))) . '</title>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<nickname>' . htmlspecialchars(strip_tags(html_entity_decode($rss_cmt->name))) . '</nickname>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<description>' . str_replace(array('\n', '\r'), '', htmlspecialchars(strip_tags($rss_cmt->body, '<br><a><b><i><u><s><blockquote><img>'))) . '</description>' . PHP_EOL;
				$rss_avatar = 'http://' . $this->setting['domain'] . $this->setting['root_dir'] . 'scripts/avatars.php?format=' . $this->setting['image_format'] . '&amp;size=' . $this->setting['icon_size'];

				// Added avatar URLs to feed
				if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $rss_cmt->name)) {
					$rss_avatar .= '&amp;username=' . $rss_cmt->name;
				} else {
					if (preg_match('/(twitter.com\/[a-zA-Z0-9_@]{1,29}$)/i', $rss_cmt->website)) {
						$web_username = preg_replace('/(.*?twitter\.com)\/([a-zA-Z0-9_]{1,20}$)/i', '\\2', $rss_cmt->website);
						$rss_avatar .= '&amp;username=@' . $web_username;
					}
				}

				if (!empty($rss_cmt->email)) {
					$rss_avatar .= '&amp;email=' . md5(strtolower(trim($this->encryption->decrypt($rss_cmt->email, $rss_cmt->encryption))));
				} else {
					$rss_avatar = $this->setting['root_dir'] . 'images/' . $this->setting['image_format'] . 's/avatar.' . $this->setting['image_format'];
				}

				$rss_feed .= "\t\t\t" . '<avatar>' . $rss_avatar . '</avatar>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<likes>' . $rss_cmt['likes'] . '</likes>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<pubDate>' . date('D, d M Y H:i:s O', (int)$rss_cmt->date) . '</pubDate>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<guid>' . $_GET['rss'] . '#' . $permalink . '</guid>' . PHP_EOL;
				$rss_feed .= "\t\t\t" . '<link>' . $_GET['rss'] . '#' . $permalink . '</link>' . PHP_EOL;
				$rss_feed .= "\t\t" . '</item>' . PHP_EOL;
			} else {
				// Substract from count if XML is invaild
				$this->total_count--;
			}
		}
	} else {
		$rss_feed = "\t\t" . '<item>' . PHP_EOL;
		$rss_feed .= "\t\t\t" . '<title>Please choose a comment thread via page URL.</title>' . PHP_EOL;
		$rss_feed .= "\t\t\t" . '<description>For example: http://' . str_replace('www.', '', $this->setting['domain']) . '/article.html</description>' . PHP_EOL;
		$rss_feed .= "\t\t" . '</item>' . PHP_EOL;
	}

	header('Content-Type: application/xml');
	echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
	echo '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . PHP_EOL;
	echo "\t" . '<channel>' . PHP_EOL;
	echo "\t\t" . '<title>' . $title . '</title>' . PHP_EOL;
	echo "\t\t" . '<link>' . $_GET['rss'] . '</link>' . PHP_EOL;
	echo "\t\t" . '<description>' . $this->text['showing_cmts'] . ' ' . ($this->total_count - 1) . ' Comments</description>' . PHP_EOL;
	echo "\t\t" . '<atom:link href="http://' . $this->setting['domain'] . $_SERVER['PHP_SELF'] . '?rss=' . str_replace(array('?', '&'), array('%3F', '&amp;'), $_GET['rss']) . '&amp;title=' . $title . '" rel="self"></atom:link>' . PHP_EOL;
	echo "\t\t" . '<language>en-us</language>' . PHP_EOL;
	echo "\t\t" . '<ttl>40</ttl>' . PHP_EOL;
	echo iconv('UTF-8', 'UTF-8//IGNORE', $rss_feed);
	echo "\t" . '</channel>' . PHP_EOL;
	exit('</rss>');

?>
