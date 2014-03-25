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

	// URL back to comment
	$kickback = $parse_url['path'] . ((!empty($parse_url['query'])) ? '?' . $parse_url['query'] : '');

	// Clean up name, set name cookie
	if (isset($_POST['name']) and trim($_POST['name'], ' ') != '' and $_POST['name'] != $text['nickname']) {
		$name = substr(ucwords(strtolower(str_replace($search, $replace, $_POST['name']))), 0, 30);

		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $admin_nickname and $_COOKIE['password'] != $admin_password)) {
				setcookie('name', $name, $expire, '/', str_replace('www.', '', $domain));
			}
		} else {
			if (!isset($_POST['delete'])) {
				setcookie('name', $name, $expire, '/', str_replace('www.', '', $domain));
			}
		}
	}

	// Set password cookie
	if (isset($_POST['password']) and (!empty($_POST['password']) and $_POST['password'] != $text['password'])) {
		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $admin_nickname and $_COOKIE['password'] != $admin_password)) {
				setcookie('password', str_replace('"', '&quot;', stripslashes($_POST['password'])), $expire, '/', str_replace('www.', '', $domain));
			}
		} else {
			if (!isset($_POST['delete'])) {
				setcookie('password', str_replace('"', '&quot;', stripslashes($_POST['password'])), $expire, '/', str_replace('www.', '', $domain));
			}
		}
	}

	// Default email headers
	$header = "From: $noreply_email\r\nReply-To: $noreply_email";

	// Clean up email, set email cookie
	if (isset($_POST['email']) and trim($_POST['email'], ' ') != '' and $_POST['email'] != $text['email']) {
		$email = str_replace($search, '', $_POST['email']);
		$header = (trim($_POST['email'], ' ') != '') ? "From: $email\r\nReply-To: $email" : $header;

		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $admin_nickname and $_COOKIE['password'] != $admin_password)) {
				setcookie('email', $email, $expire, '/', str_replace('www.', '', $domain));
			}
		} else {
			if (!isset($_POST['delete'])) {
				setcookie('email', $email, $expire, '/', str_replace('www.', '', $domain));
			}
		}
	}

	// Clean up web address, set website cookie
	if (isset($_POST['website']) and trim($_POST['website'], ' ') != '' and $_POST['website'] != $text['website']) {
		$website = str_replace($search, $replace, $_POST['website']);
		$website = (!preg_match('/htt[p|ps]:\/\//i', $website)) ? 'http://' . $website : $website;

		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $admin_nickname and $_COOKIE['password'] != $admin_password)) {
				setcookie('website', $website, $expire, '/', str_replace('www.', '', $domain));
			}
		} else {
			if (!isset($_POST['delete'])) {
				setcookie('website', $website, $expire, '/', str_replace('www.', '', $domain));
			}
		}
	}

	function sanitize($query) {
		$value = str_replace('../', '', $query);
		return $value;
	}

	function xml_sanitize($string) {
		$string = mb_convert_encoding(mb_convert_encoding($string, 'UTF-16', 'UTF-8'), 'UTF-8', 'UTF-16');
		$string = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '?', $string);
		return $string;
	}

	// Delete comment
	if (isset($_POST['delete']) and (isset($_POST['password']) and !empty($_POST['password']))) {
		if (file_exists($dir . '/' . $_POST['cmtfile'] . '.xml')) {
			$del_file = $dir . '/' . sanitize($_POST['cmtfile'] . '.xml');
			$get_pass = simplexml_load_file($del_file);
		} else {
			exit(header('Location: ' . $kickback . '#comments'));
		}

		// Check if password matches the one in the file
		if (md5(encrypt(stripslashes($_POST['password']))) == $get_pass->passwd or ($_COOKIE['name'] == $admin_nickname and $_COOKIE['password'] == $admin_password)) {
			unlink($del_file); // Delete the comment file
			setcookie('password', str_replace('"', '&quot;', $_POST['password']), $expire, '/', str_replace('www.', '', $domain));
			read_comments($dir, 'no'); // Read comments without output

			// Kick visitor back to comment
			if (file_exists($dir . '/' . ($_POST['cmtfile'] + 1) . '.xml') or file_exists($dir . '/' . $_POST['cmtfile'] . '-1.xml')) {
				exit(header('Location: ' . $kickback . '#c' . str_replace('-', 'r', $_POST['cmtfile'])));
			} else {
				setcookie('message', $text['cmt_deleted'], $expire, '/', str_replace('www.', '', $domain));
				exit(header('Location: ' . $kickback . '#comments'));
			}
		} else {
			exit(header('Location: ' . $kickback . '#c' . str_replace('-', 'r', $_POST['cmtfile'])));
		}
	}

	// Check trap fields
	if (!empty($_POST['summary'])) $is_spam = true;
	if (!empty($_POST['middlename'])) $is_spam = true;
	if (!empty($_POST['lastname'])) $is_spam = true;
	if (!empty($_POST['address'])) $is_spam = true;
	if (!empty($_POST['zip'])) $is_spam = true;

	// Check if a comment has been entered, clean comment, replace HTML, create hyperlinks
	if (!isset($is_spam) and isset($_POST['comment']) and !isset($_POST['delete'])) {
		if (isset($_POST['login'])) {
			setcookie('hashover-' . strtolower(str_replace(' ', '-', $name)), hash('ripemd160', xml_sanitize(trim($name, ' ')) . ((isset($_POST['password']) and !empty($_POST['password']) and $_POST['password'] != $text['password']) ? md5(encrypt(stripslashes($_POST['password']))) : '')), $expire, '/', str_replace('www.', '', $domain));
			setcookie('message', $text['logged_in'], $expire, '/', str_replace('www.', '', $domain));
			exit(header('Location: ' . $kickback . '#comments'));
		}

		if (!empty($_POST['comment']) and trim($_POST['comment'], " \r\n") != '' and (!preg_match('/' . str_replace(array('(', ')'), array('\(', '\)'), $text['comment_form']) . '/i', $_POST['comment']) and !preg_match('/' . str_replace(array('(', ')'), array('\(', '\)'), $text['reply_form']) . '/i', $_POST['comment']))) {
			// Characters to search for and replace with in comments
			$data_search = array('\\', '"', '<', '>', "\n\r", "\n", "\r", '  ', '&lt;b&gt;', '&lt;/b&gt;', '&lt;u&gt;', '&lt;/u&gt;', '&lt;i&gt;', '&lt;/i&gt;', '&lt;s&gt;', '&lt;/s&gt;', '&lt;pre&gt;', '&lt;/pre&gt;', '&lt;code&gt;', '&lt;/code&gt;', '&lt;ul&gt;', '&lt;/ul&gt;', '&lt;ol&gt;', '&lt;/ol&gt;', '&lt;li&gt;', '&lt;/li&gt;', '&lt;blockquote&gt;', '&lt;/blockquote&gt;');
			$data_replace = array('&#92;', '&quot;', '&lt;', '&gt;', '<br>', '', '<br>', ' &nbsp;', '<b>', '</b>', '<u>', '</u>', '<i>', '</i>', '<s>', '</s>', '<pre>', '</pre>', '<code>', '</code>', '<ul>', '</ul>', '<ol>', '</ol>', '<li>', '</li>', '<blockquote>', '</blockquote>');

			$clean_code = preg_replace('/(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)/i', '\\1 ', $_POST['comment']); // Add space to end of URLs to separate '&' characters from escaped HTML tags
			$clean_code = str_ireplace($data_search, $data_replace, preg_replace('/\n{2,}/', "\n\r\n", preg_replace('/^\s+$/m', '', rtrim($clean_code, " \r\n")))); // Escape HTML tags; remove trailing new lines
			$clean_code = preg_replace('/^(<br><br>)/', '', preg_replace('/(<br><br>)$/', '', preg_replace('/(<br>){2,}/i', '<br><br>', $clean_code))); // Remove repetitive and trailing HTML <br> tags

			// HTML tags to automatically close
			$tags = array('code', 'b', 'i', 'u', 's', 'li', 'pre', 'blockquote', 'ul', 'ol');
			$cleantags = array('blockquote', 'ul', 'ol');

			// Check if all allowed HTML tags have been closed, if not add them at the end
			for ($tc = '0'; $tc != count($tags); $tc++) {
				$open_tags = substr_count(strtolower(preg_replace('/<code>.*?<\/code>/i', '', $clean_code)), '<' . $tags[$tc] . '>');
				$close_tags = substr_count(strtolower(preg_replace('/<code>.*?<\/code>/i', '', $clean_code)), '</' . $tags[$tc] . '>');

				if ($open_tags != $close_tags) {
					while ($open_tags > $close_tags) {
						$clean_code .= '</' . $tags[$tc] . '>';
						$close_tags++;
					}

					while ($close_tags > $open_tags) {
						$clean_code = preg_replace('/' . str_replace('/', '\/', '</' . $tags[$tc] . '>') . '/i', '', $clean_code, 1);
						$close_tags--;
					}
				}

				if (in_array($tags[$tc], $cleantags)) {
					$clean_code = str_ireplace(array('<' . $tags[$tc] . '><br>', '</' . $tags[$tc] . '><br>'), array('<' . $tags[$tc] . '>\n', '</' . $tags[$tc] . '>\n'), $clean_code);
				}
			}

			$clean_code = str_ireplace(array('<code><br>', '<br></code>'), array('<code>', '</code>'), $clean_code);
			$clean_code = str_ireplace(array('<pre><br>', '<br></pre>'), array('<pre>', '</pre>'), $clean_code);
			$clean_code = preg_replace_callback('/(<code>)(.*?)(<\/code>){1,}/i', function($arr) { return '<code style="white-space: pre;">' . str_ireplace('&lt;br&gt;', '<br>', htmlspecialchars(preg_replace('/(<br>){1,}<img.*?title="(.*?)".*?>(<br>){1,}/', '\\2', preg_replace('/<\/?a(\s+.*?>|>)/', '', $arr[2])), null, null, false)) . $arr[3];}, $clean_code);
			$clean_code = preg_replace_callback('/(<pre>)(.*?)(<\/pre>){1,}/i', function($arr) { return $arr[1] . preg_replace('/(<br>){1,}<img.*?title="(.*?)".*?>(<br>){1,}/', '\\2', $arr[2]) . $arr[3];}, $clean_code);
			$clean_code = str_replace(array('<blockquote>\n<br>', '<br><br></blockquote>'), array('<blockquote>\n', '\n</blockquote>'), $clean_code);
			$clean_code = str_ireplace('</li><br>', '</li>\n', $clean_code);

			// Open comment template; prepare data
			$write_cmt = simplexml_load_file('template.xml');
			$write_cmt->name = xml_sanitize(trim($name, ' '));
			$write_cmt->passwd = (isset($_POST['password']) and !empty($_POST['password']) and $_POST['password'] != $text['password']) ? md5(encrypt(stripslashes($_POST['password']))) : '';
			$write_cmt->email = (isset($_POST['email']) and $_POST['email'] != $text['email']) ? str_replace('"', '&quot;', encrypt(stripslashes(xml_sanitize($email)))) : '';
			$write_cmt->website = (isset($website)) ? xml_sanitize(trim($website, ' ')) : '';
			$write_cmt->date = date('m/d/Y - g:ia');
			$write_cmt['likes'] = '0';
			$write_cmt['notifications'] = 'yes';
			$write_cmt['ipaddr'] = ($ip_addrs == 'yes') ? $_SERVER['REMOTE_ADDR'] : '';
			$write_cmt->body = xml_sanitize($clean_code); // Final comment body

			// Edit comment
			if (isset($_POST['edit']) and (isset($_POST['password']) and isset($_POST['cmtfile'])) and !isset($_POST['delete'])) {
				if (file_exists($dir . '/' . $_POST['cmtfile'] . '.xml')) {
					$edit_cmt = simplexml_load_file($dir . '/' . sanitize($_POST['cmtfile'] . '.xml'));

					// Check if password matches the one in the file
					if (md5(encrypt(stripslashes($_POST['password']))) == $edit_cmt->passwd or ($_COOKIE['name'] == $admin_nickname and $_COOKIE['password'] == $admin_password)) {
						// Write edited comment to file
						$edit_cmt->name = $write_cmt->name;
						if ($_COOKIE['name'] != $admin_nickname and $_COOKIE['password'] != $admin_password) $edit_cmt->email = $write_cmt->email;
						$edit_cmt->website = $write_cmt->website;
						$edit_cmt['notifications'] = (isset($_POST['notify']) and $_POST['notify'] == 'on') ? 'yes' : 'no';
						if ($_POST['password'] != $admin_password) $edit_cmt->passwd = $write_cmt->passwd;
						if ($clean_code != $edit_cmt->body) $edit_cmt->body = $write_cmt->body;
						$edit_cmt->asXML($dir . '/' . sanitize($_POST['cmtfile']) . '.xml');

						// Set "Password" and "Login" cookies
						setcookie('password', str_replace('"', '&quot;', stripslashes($_POST['password'])), $expire, '/', str_replace('www.', '', $domain));
						setcookie('hashover-' . strtolower(str_replace(' ', '-', $name)), hash('ripemd160', $write_cmt->name . $write_cmt->passwd), $expire, '/', str_replace('www.', '', $domain));
					}
				}

				// Kick visitor back to comment
				exit(header('Location: ' . $kickback . '#c' . str_replace('-', 'r', $_POST['cmtfile'])));
			}

			// Read comments without output
			read_comments($dir, 'no');

			// Rename file for reply
			if (isset($_POST['reply_to']) and !empty($_POST['reply_to'])) {
				if (!preg_match('/[a-zA-Z]/i', $_POST['reply_to']) and file_exists($dir . '/' . $_POST['reply_to'] . ".xml")) {
					// Set reply directory information & "cookie" for successful reply
					$reply_dir = $dir . '/' . $_POST['reply_to'] . '.xml';
					$cmt_file = $dir . '/' . $_POST['reply_to'] . '-' . $subfile_count["$reply_dir"] . '.xml';
					setcookie('replied', $_POST['reply_to'], $expire, '/', str_replace('www.', '', $domain));
				}
			} else {
				$cmt_file = $dir . '/' . $cmt_count . '.xml';
			}

			// Write comment to file
			if ($write_cmt->asXML(sanitize($cmt_file))) {
				chmod($cmt_file, 0600);

				// Send notification e-mails
				$permalink = 'c' . str_replace('-', 'r', basename($cmt_file, '.xml'));
				$from_email = (isset($_POST['email']) and !empty($_POST['email']) and $_POST['email'] != $text['email'] and $user_reply == 'yes') ? $name . ' <' . $email . '>' : $name;
				$reverse_datasearch = array('&quot;', '&lt;', '&gt;', '<br>\n', '\n', '<br>', '&nbsp;', '\r');
				$reverse_datareplace = array('"', '<', '>', PHP_EOL, PHP_EOL, "\r", '  ', "\r");
				$to_webmaster = '';

				// Notify commenter of reply
				if (isset($_POST['reply_to']) and !empty($_POST['reply_to'])) {
					if (!preg_match('/[a-zA-Z]/i', $_POST['reply_to']) and file_exists($dir . '/' . $_POST['reply_to'] . ".xml")) {
						$get_cmt = simplexml_load_file($dir . '/' . sanitize($_POST['reply_to'] . '.xml'));
						$to_commenter = "\nIn reply to:\n\n\t" . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($get_cmt->body)) . "\n\n";
						$to_webmaster = "\nIn reply to " . $get_cmt->name . ":\n\n\t" . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($get_cmt->body)) . "\n\n";
						$decryto = encrypt($get_cmt->email);

						if (!empty($decryto) and $decryto != $notification_email and $decryto != $email) {
							if ($get_cmt['notifications'] == 'yes') {
								if ($user_reply != 'yes') $header = "From: $noreply_email\r\nReply-To: $noreply_email";
								mail($decryto, $_SERVER['HTTP_HOST'] . ' - New Reply', "From $from_email:\n\n\t" . strip_tags(stripslashes($_POST['comment'])) . "\n\n$to_commenter----\nPermalink: $page_url" . '#' . $permalink . "\nPage: $page_url", $header);
							}
						}
					}
				}

				// Notify webmaster via e-mail
				if ($write_cmt->email != encrypt($notification_email)) {
					mail($notification_email, 'New Comment', "From $from_email:\n\n\t" . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($clean_code)) . "\n\n$to_webmaster----\nPermalink: $page_url" . '#' . $permalink . "\nPage: $page_url", $header);
				}

				// Set blank cookie for successful comment, kick visitor back to comment
				setcookie('replied', '', $expire, '/', str_replace('www.', '', $domain));
				setcookie('hashover-' . strtolower(str_replace(' ', '-', $name)), hash('ripemd160', $write_cmt->name . $write_cmt->passwd), $expire, '/', str_replace('www.', '', $domain));
				exit(header('Location: ' . $kickback . '#' . $permalink));
			} else {
				setcookie('message', $text['post_fail'], $expire, '/', str_replace('www.', '', $domain));
				exit(header('Location: ' . $kickback . '#comments'));
			}
		} else {
			// Set failed comment cookie
			setcookie('success', 'no', $expire, '/', str_replace('www.', '', $domain));

			// Set message cookie to comment or reply requirement notice
			if (isset($_POST['reply_to']) and !empty($_POST['reply_to'])) {
				setcookie('replied', $_POST['reply_to'], $expire, '/', str_replace('www.', '', $domain));
				setcookie('message', $text['reply_needed'], $expire, '/', str_replace('www.', '', $domain));
			} else {
				setcookie('message', $text['cmt_needed'], $expire, '/', str_replace('www.', '', $domain));
			}

			// Kick visitor back to comment form
			exit(header('Location: ' . $kickback . '#comments'));
		}
	}

?>
