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

	$kickback = $this->parse_url['path'] . ((!empty($this->parse_url['query'])) ? '?' . $this->parse_url['query'] : ''); // URL back to comment

	// Characters to be removed from name, email, and website fields
	$search = array('<', '>', "\n", "\r", "\t", '&nbsp;', '&lt;', '&gt;', '"', "'", '\\');
	$replace = array('', '', '', '', '', '', '', '', '&quot;', '&#39;', '');

	// Clean up name, set name cookie
	if (isset($_POST['name']) and trim($_POST['name'], ' ') != '') {
		$name = substr(ucwords(strtolower(str_replace($search, $replace, $_POST['name']))), 0, 30);

		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $this->admin_nickname and $_COOKIE['password'] != $this->admin_password)) {
				$cookies->set('name', $name);
			}
		} else {
			if (!isset($_POST['delete'])) {
				$cookies->set('name', $name);
			}
		}
	} else {
		$name = $this->setting['default_name'];
	}

	// Set password cookie
	if (!empty($_POST['password'])) {
		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $this->admin_nickname and $_COOKIE['password'] != $this->admin_password)) {
				$cookies->set('password', str_replace('"', '&quot;', $_POST['password']));
			}
		} else {
			if (!isset($_POST['delete'])) {
				$cookies->set('password', str_replace('"', '&quot;', $_POST['password']));
			}
		}
	}

	// Default email headers
	$header = "From: " . $this->setting['noreply_email'] . "\r\nReply-To: " . $this->setting['noreply_email'];

	// Clean up email, set email cookie
	if (isset($_POST['email']) and trim($_POST['email'], ' ') != '') {
		$email = str_replace($search, '', $_POST['email']);
		$header = (trim($_POST['email'], ' ') != '') ? "From: $email\r\nReply-To: $email" : $header;

		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $this->admin_nickname and $_COOKIE['password'] != $this->admin_password)) {
				$cookies->set('email', $email);
			}
		} else {
			if (!isset($_POST['delete'])) {
				$cookies->set('email', $email);
			}
		}
	}

	// Clean up web address, set website cookie
	if (isset($_POST['website']) and trim($_POST['website'], ' ') != '') {
		$website = str_replace($search, $replace, $_POST['website']);
		$website = (!preg_match('/htt[p|ps]:\/\//i', $website)) ? 'http://' . $website : $website;

		if (isset($_POST['edit'])) {
			if (!isset($_POST['delete']) and ($_COOKIE['name'] != $this->admin_nickname and $_COOKIE['password'] != $this->admin_password)) {
				$cookies->set('website', $website);
			}
		} else {
			if (!isset($_POST['delete'])) {
				$cookies->set('website', $website);
			}
		}
	}

	function xml_sanitize($string) {
		$string = mb_convert_encoding(mb_convert_encoding($string, 'UTF-16', 'UTF-8'), 'UTF-8', 'UTF-16');
		$string = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '?', $string);
		return $string;
	}
	
	foreach (array('cmtfile', 'reply_to') as $post_file) {
		if (isset($_POST[$post_file])) {
			if (!in_array($_POST[$post_file], array_keys($this->comments))) {
				$cookies->set('message', $this->text['cmt_needed']);
				exit(header('Location: ' . $kickback . '#comments'));
			}
		}
	}

	// Delete comment
	if (isset($_POST['delete']) and !empty($_POST['password'])) {
		if (file_exists($this->dir . '/' . $_POST['cmtfile'] . '.' . $this->setting['data_format'])) {
			$del_file = $this->dir . '/' . $_POST['cmtfile'] . '.' . $this->setting['data_format'];

			switch ($this->setting['data_format']) {
				default: exit($this->escape_output('<b>HashOver:</b> Unsupported data format!', 'single')); break;

				case 'xml':
					$get_pass = simplexml_load_file($del_file);
					break;

				case 'json':
					$get_pass = json_decode(file_get_contents($del_file));
					break;
			}
		} else {
			exit(header('Location: ' . $kickback . '#comments'));
		}

		// Check if password matches the one in the file
		if ($this->encryption->verify_hash($_POST['password'], $get_pass->passwd) or ($_COOKIE['name'] == $this->admin_nickname and $_COOKIE['password'] == $this->admin_password)) {
			unlink($del_file); // Delete the comment file
			$cookies->set('password', str_replace('"', '&quot;', $_POST['password']));
			$file_parts = explode('-', $_POST['cmtfile']);

			// Kick visitor back to comment
			if (file_exists($this->dir . '/' . (basename($_POST['cmtfile'], '-' . end($file_parts)) . end($file_parts) + 1) . '.' . $this->setting['data_format']) or file_exists($this->dir . '/' . $_POST['cmtfile'] . '-1.' . $this->setting['data_format'])) {
				exit(header('Location: ' . $kickback . '#c' . str_replace('-', 'r', $_POST['cmtfile'])));
			}

			$cookies->set('message', $this->text['cmt_deleted']);
		} else {
			$cookies->set('message', $this->text['post_fail']);
		}

		exit(header('Location: ' . $kickback . '#comments'));
	}

	// Check trap fields
	if (!empty($_POST['summary'])) $is_spam = true;
	if (!empty($_POST['middlename'])) $is_spam = true;
	if (!empty($_POST['lastname'])) $is_spam = true;
	if (!empty($_POST['address'])) $is_spam = true;
	if (!empty($_POST['zip'])) $is_spam = true;

	// Check if a comment has been entered, clean comment, replace HTML, create hyperlinks
	if (isset($_POST['comment']) and !isset($_POST['delete'])) {
		// Set login cookie; kick visitor back
		if (isset($_POST['login'])) {
			$cookies->set('hashover-' . strtolower(str_replace(' ', '-', $name)), hash('ripemd160', xml_sanitize(trim($name, ' ')) . ((isset($_POST['email']) and $_POST['email'] != $this->text['email'] and filter_var($email, FILTER_VALIDATE_EMAIL)) ? $_POST['email'] : '')));
			$cookies->set('message', $this->text['logged_in']);
			exit(header('Location: ' . $kickback . '#comments'));
		}

		// Block for filing trap fields
		if (isset($is_spam)) {
			exit('<b>HashOver:</b> You are blocked!');
		} else {
			$spam_check = new SpamCheck($_SERVER['REMOTE_ADDR']);

			// Check user's IP address against stopforumspam.com
			if ($this->setting['spam_check_modes'] == 'both') {
				if ($spam_check->{$this->setting['spam_database']}()) {
					exit('<b>HashOver:</b> You are blocked!');
				}
			} else {
				if ($this->setting['spam_check_modes'] == $this->mode) {
					if ($spam_check->{$this->setting['spam_database']}()) {
						exit('<b>HashOver:</b> You are blocked!');
					}
				}
			}
		}

		// Check if a comment was posted
		if (!empty($_POST['comment']) and trim($_POST['comment'], " \r\n") != '') {
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
			for ($tc = 0; $tc < count($tags); $tc++) {
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
			$write_cmt = simplexml_load_file('.' . $this->setting['root_dir'] . 'template.xml');
			$write_cmt->name = xml_sanitize(trim($name, ' '));
			$write_cmt->passwd = (!empty($_POST['password'])) ? $this->encryption->create_hash($_POST['password']) : '';
			$encryption_keys = $this->encryption->encrypt(xml_sanitize($email));
			$write_cmt->email = (isset($_POST['email']) and filter_var($email, FILTER_VALIDATE_EMAIL)) ? $encryption_keys['encrypted'] : '';
			$write_cmt->encryption = (!empty($_POST['email'])) ? $encryption_keys['keys'] : '';
			$write_cmt->website = (isset($website)) ? xml_sanitize(trim($website, ' ')) : '';
			$write_cmt->date = date('m/d/Y - g:ia');
			$write_cmt['likes'] = '0';
			$write_cmt['notifications'] = 'yes';
			$write_cmt['ipaddr'] = ($this->setting['ip_addrs'] == 'yes') ? $_SERVER['REMOTE_ADDR'] : '';
			$write_cmt->body = xml_sanitize($clean_code); // Final comment body

			// Edit comment
			if (isset($_POST['edit']) and (isset($_POST['password']) and isset($_POST['cmtfile'])) and !isset($_POST['delete'])) {
				if (file_exists($this->dir . '/' . $_POST['cmtfile'] . '.' . $this->setting['data_format'])) {
					switch ($this->setting['data_format']) {
						default: exit($this->escape_output('<b>HashOver:</b> Unsupported data format!', 'single')); break;

						case 'xml':
							$edit_cmt = simplexml_load_file($this->dir . '/' . $_POST['cmtfile'] . '.xml');
							break;

						case 'json':
							$write_cmt = (object)(array)$write_cmt;
							$edit_cmt = json_decode(file_get_contents($this->dir . '/' . $_POST['cmtfile'] . '.json'));
							break;
					}

					// Check if password matches the one in the file
					if ($this->encryption->verify_hash($_POST['password'], $edit_cmt->passwd) or ($_COOKIE['name'] == $this->admin_nickname and $_COOKIE['password'] == $this->admin_password)) {
						// Write edited comment to file
						$edit_cmt->name = $write_cmt->name;
						if ($_COOKIE['name'] != $this->admin_nickname and $_COOKIE['password'] != $this->admin_password) $edit_cmt->email = $write_cmt->email;
						$edit_cmt->website = $write_cmt->website;

						switch ($this->setting['data_format']) {
							default: exit($this->escape_output('<b>HashOver:</b> Unsupported data format!', 'single')); break;

							case 'xml':
								$edit_cmt['notifications'] = (isset($_POST['notify']) and $_POST['notify'] == 'on') ? 'yes' : 'no';
								break;

							case 'json':
								$edit_cmt->notifications = (isset($_POST['notify']) and $_POST['notify'] == 'on') ? 'yes' : 'no';
								break;
						}

						if ($_POST['password'] != $this->admin_password) $edit_cmt->passwd = $write_cmt->passwd;
						if ($clean_code != $edit_cmt->body) $edit_cmt->body = $write_cmt->body;

						switch ($this->setting['data_format']) {
							default: exit($this->escape_output('<b>HashOver:</b> Unsupported data format!', 'single')); break;

							case 'xml':
								$edit_cmt->asXML($this->dir . '/' . $_POST['cmtfile'] . '.xml');
								break;

							case 'json':
								file_put_contents($this->dir . '/' . $_POST['cmtfile'] . '.json', preg_replace('/{(\s+)}/', '""', json_encode($edit_cmt, JSON_PRETTY_PRINT)));
								break;
						}

						// Set "Password" and "Login" cookies; kick visitor back to comment(s)
						$cookies->set('password', str_replace('"', '&quot;', $_POST['password']));
						$cookies->set('hashover-' . strtolower(str_replace(' ', '-', $name)), hash('ripemd160', $write_cmt->name . $_POST['email']));
						exit(header('Location: ' . $kickback . '#c' . str_replace('-', 'r', $_POST['cmtfile'])));
					} else {
						$cookies->set('message', $this->text['post_fail']);
						exit(header('Location: ' . $kickback . '#comments'));
					}
				}
			}

			// Rename file for reply
			if (!empty($_POST['reply_to'])) {
				if (file_exists($this->dir . '/' . $_POST['reply_to'] . '.' . $this->setting['data_format'])) {
					// Set reply directory information & "cookie" for successful reply
					$cmt_file = $this->dir . '/' . $_POST['reply_to'] . '-' . $this->subfile_count[$_POST['reply_to']] . '.' . $this->setting['data_format'];
					$cookies->set('replied', $_POST['reply_to']);
				}
			} else {
				$cmt_file = $this->dir . '/' . $this->cmt_count . '.' . $this->setting['data_format'];
			}

			// Write comment to file
			switch ($this->setting['data_format']) {
				default: exit($this->escape_output('<b>HashOver:</b> Unsupported data format!', 'single')); break;

				case 'xml':
					if (!file_exists($cmt_file) and !$write_cmt->asXML($cmt_file)) $post_failure = true;
					break;

				case 'json':
					$write_json = (array)$write_cmt;
					$write_json = array_merge((array)$write_json, (array)$write_json['@attributes']);
					unset($write_json['@attributes']);

					if (!file_exists($cmt_file) and !file_put_contents($cmt_file, preg_replace('/{(\s+)}/', '""', json_encode((array)$write_json, JSON_PRETTY_PRINT)))) {
						$post_failure = true;
					}
					break;
			}

			if (!$post_failure) {
				chmod($cmt_file, 0600);

				// Send notification e-mails
				$permalink = 'c' . str_replace('-', 'r', basename($cmt_file, '.' . $this->setting['data_format']));
				$from_email = (!empty($_POST['email']) and $_POST['email'] != $this->text['email'] and $this->setting['user_reply'] == 'yes') ? $name . ' <' . $email . '>' : $name;
				$reverse_datasearch = array('&quot;', '&lt;', '&gt;', '<br>\n', '\n', '<br>', '&nbsp;', '\r');
				$reverse_datareplace = array('"', '<', '>', PHP_EOL, PHP_EOL, "\r", '  ', "\r");
				$to_webmaster = '';

				// Notify commenter of reply
				if (!empty($_POST['reply_to'])) {
					if (file_exists($this->dir . '/' . $_POST['reply_to'] . '.' . $this->setting['data_format'])) {
						$get_cmt = simplexml_load_file($this->dir . '/' . $_POST['reply_to'] . '.' . $this->setting['data_format']);
						$to_commenter = "\nIn reply to:\n\n\t" . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($get_cmt->body)) . "\n\n";
						$to_webmaster = "\nIn reply to " . $get_cmt->name . ":\n\n\t" . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($get_cmt->body)) . "\n\n";
						$decryto = $this->encryption->create_hash($get_cmt->email);

						if (!empty($decryto) and $decryto != $notification_email and $decryto != $email) {
							if ($get_cmt['notifications'] == 'yes') {
								if ($this->setting['user_reply'] != 'yes') $header = "From: " . $this->setting['noreply_email'] . "\r\nReply-To: " . $this->setting['noreply_email'];
								mail($decryto, $_SERVER['HTTP_HOST'] . ' - New Reply', "From $from_email:\n\n\t" . strip_tags($_POST['comment']) . "\n\n$to_commenter----\nPermalink: $page_url" . '#' . $permalink . "\nPage: $page_url", $header);
							}
						}
					}
				}

				// Notify webmaster via e-mail
				if (!$this->encryption->verify_hash($notification_email, $write_cmt->email)) {
					mail($notification_email, 'New Comment', "From $from_email:\n\n\t" . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($clean_code)) . "\n\n$to_webmaster----\nPermalink: $page_url" . '#' . $permalink . "\nPage: $page_url", $header);
				}

				// Set blank cookie for successful comment, kick visitor back to comment
				$cookies->set('replied', '');
				$cookies->set('hashover-' . strtolower(str_replace(' ', '-', $name)), hash('ripemd160', $write_cmt->name . $_POST['email']));
				exit(header('Location: ' . $kickback . '#' . $permalink));
			} else {
				$cookies->set('message', $this->text['post_fail']);
				exit(header('Location: ' . $kickback . '#comments'));
			}
		} else {
			// Set failed comment cookie
			$cookies->set('success', 'no');

			// Set message cookie to comment or reply requirement notice
			if (!empty($_POST['reply_to'])) {
				$cookies->set('replied', $_POST['reply_to']);
				$cookies->set('message', $this->text['reply_needed']);
			} else {
				$cookies->set('message', $this->text['cmt_needed']);
			}

			// Kick visitor back to comment form
			exit(header('Location: ' . $kickback . '#comments'));
		}
	}

?>
