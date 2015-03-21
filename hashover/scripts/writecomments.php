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

	class WriteComments
	{
		public $header,
		       $kickback,
		       $name,
		       $email,
		       $website,
		       $metalevels;

		// Characters to be removed from name, email, and website fields
		public $search = array('<', '>', "\n", "\r", "\t", '&nbsp;', '&lt;', '&gt;', '"', "'", '\\');
		public $replace = array('', '', '', '', '', '', '', '', '&quot;', '&#39;', '');

		public function __construct($read_comments, $cookies)
		{
			$this->read_comments = $read_comments;
			$this->setup = $read_comments->data->setup;
			$this->cookies = $cookies;
			$this->metalevels = array($this->setup->dir, './pages');

			// Default email headers
			$this->header = "From: " . $this->setup->noreply_email . "\r\nReply-To: " . $this->setup->noreply_email;

			// URL back to comment
			$this->kickback = $this->setup->parse_url['path'];

			// Set timezone to UTC
			date_default_timezone_set('UTC');

			// Add URL queries to kickback URL
			if (!empty($this->setup->ref_queries)) {
				$this->kickback .= '?' . $this->setup->ref_queries;
			}

			foreach (array('cmtfile', 'reply_to') as $post_query) {
				if (isset($_POST[$post_query])) {
					$post_comment = (string) $_POST[$post_query];

					if (!in_array($post_comment, $read_comments->commentlist, true)) {
						$this->cookies->set('message', $this->setup->text['cmt_needed']);
						exit(header('Location: ' . $this->kickback . '#comments'));
					}
				}
			}

			// Clean up name, set name cookie
			if (!empty($_POST['name']) and trim($_POST['name'], ' ') != '') {
				$this->name = substr(str_replace($this->search, $this->replace, trim($_POST['name'])), 0, 30);

				if (isset($_POST['edit'])) {
					if (!isset($_POST['delete']) and $this->setup->user_is_admin == false) {
						$this->cookies->set('name', $this->name);
					}
				} else {
					if (!isset($_POST['delete'])) {
						$this->cookies->set('name', $this->name);
					}
				}
			} else {
				$this->name = $this->setup->default_name;
			}

			// Set password cookie
			if (!empty($_POST['password'])) {
				if (isset($_POST['edit'])) {
					if (!isset($_POST['delete']) and $this->setup->user_is_admin == false) {
						$this->cookies->set('password', str_replace('"', '&quot;', $_POST['password']));
					}
				} else {
					if (!isset($_POST['delete'])) {
						$this->cookies->set('password', str_replace('"', '&quot;', $_POST['password']));
					}
				}
			}

			// Clean up email, set email cookie
			if (isset($_POST['email']) and trim($_POST['email'], ' ') != '') {
				$this->email = str_replace($this->search, '', $_POST['email']);
				$this->header = (trim($_POST['email'], ' ') != '') ? "From: $this->email\r\nReply-To: $this->email" : $this->header;

				if (isset($_POST['edit'])) {
					if (!isset($_POST['delete']) and $this->setup->user_is_admin == false) {
						$this->cookies->set('email', $this->email);
					}
				} else {
					if (!isset($_POST['delete'])) {
						$this->cookies->set('email', $this->email);
					}
				}
			}

			// Clean up web address, set website cookie
			if (isset($_POST['website'])) {
				$this->website = str_replace($this->search, $this->replace, trim($_POST['website']));

				if (!empty($this->website)) {
					$this->website = (!preg_match('/htt[p|ps]:\/\//i', $this->website)) ? 'http://' . $this->website : $this->website;
				}

				if (isset($_POST['edit'])) {
					if (!isset($_POST['delete']) and $this->setup->user_is_admin == false) {
						$this->cookies->set('website', $this->website);
					}
				} else {
					if (!isset($_POST['delete'])) {
						$this->cookies->set('website', $this->website);
					}
				}
			}
		}

		// Force a string to UTF-8 encoding and acceptable character range
		public function xml_sanitize($string)
		{
			$string = mb_convert_encoding(mb_convert_encoding($string, 'UTF-16', 'UTF-8'), 'UTF-8', 'UTF-16');
			$string = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '?', $string);
			return $string;
		}

		public function add_latest_comment($file)
		{
			if ($this->read_comments->data->storage_format != 'flat-file') {
				return false;
			}

			foreach ($this->metalevels as $level => $metafile) {
				$metafile .= '/.metadata';
				$metadata = array();
				$data = array('latest' => array());

				if ($level == 0) {
					$metadata['title'] = $this->setup->page_title;
					$metadata['url'] = $this->setup->page_url;
					$metadata['status'] = 'open';
				}

				if (file_exists($metafile) and is_writable($metafile)) {
					$data = json_decode(file_get_contents($metafile), true);

					if ($level == 0) {
						$metadata['status'] = $data['status'];
						array_unshift($data['latest'], (string) $file);
					} else {
						$cmtdir = str_replace('./pages/', '', $this->metalevels[0]);
						array_unshift($data['latest'], $cmtdir . '/' . $file);
					}

					if (count($data['latest']) >= 10) {
						if (count($data['latest']) >= $this->setup->latest_num) {
							$max = max(10, $this->setup->latest_num);
							$data['latest'] = array_slice($data['latest'], 0, $max);
						}
					}
				}

				$metadata['latest'] = $data['latest'];

				// Save metadata
				$this->read_comments->data->save_metadata($metadata, $metafile);
			}
		}

		public function remove_from_latest($file)
		{
			if ($this->read_comments->data->storage_format != 'flat-file') {
				return false;
			}

			foreach ($this->metalevels as $level => $metafile) {
				$metafile .= '/.metadata';

				if (!file_exists($metafile) or !is_writable($metafile)) {
					continue;
				}

				$metadata = json_decode(file_get_contents($metafile), true);
				$file = str_replace('./pages/', '', $file);
				$latest = array();

				for ($key = 0, $length = count($metadata['latest']); $key < $length; $key++) {
					$cmtdir = str_replace('./pages/', '', $this->metalevels[0]);
					$comment = ($level == 0) ? $file : $cmtdir . '/' . $file;

					if ($metadata['latest'][$key] != $comment) {
						$latest[] = $metadata['latest'][$key];
					}
				}

				$metadata['latest'] = $latest;
				$this->read_comments->data->save_metadata($metadata, $metafile);
			}
		}

		// Delete comment
		public function delete_comment()
		{
			$get_pass = $this->read_comments->data->read($_POST['cmtfile']);

			// Check if password matches the one in the file
			if ($this->setup->encryption->verify_hash($_POST['password'], $get_pass['password']) or $this->setup->user_is_admin == true) {
				// Delete the comment file
				if ($this->read_comments->data->delete($_POST['cmtfile'])) {
					if ($this->read_comments->data->storage_format == 'flat-file') {
						$this->remove_from_latest($_POST['cmtfile']);
					}

					$this->cookies->set('password', str_replace('"', '&quot;', $_POST['password']));
					$this->cookies->set('message', $this->setup->text['cmt_deleted']);
				}
			} else {
				$this->cookies->set('message', $this->setup->text['post_fail']);
			}

			exit(header('Location: ' . $this->kickback . '#comments'));
		}

		public function post_comment()
		{
			// Check trap fields
			if (!empty($_POST['summary'])) $is_spam = true;
			if (!empty($_POST['age'])) $is_spam = true;
			if (!empty($_POST['lastname'])) $is_spam = true;
			if (!empty($_POST['address'])) $is_spam = true;
			if (!empty($_POST['zip'])) $is_spam = true;

			// Block for filing trap fields
			if (isset($is_spam)) {
				exit('<b>HashOver:</b> You are blocked!');
			} else {
				$spam_check = new SpamCheck($this->setup);

				// Check user's IP address against stopforumspam.com
				if ($this->setup->spam_check_modes == 'both') {
					if ($spam_check->{$this->setup->spam_database}()) {
						exit('<b>HashOver:</b> You are blocked!');
					}
				} else {
					if ($this->setup->spam_check_modes == $this->setup->mode) {
						if ($spam_check->{$this->setup->spam_database}()) {
							exit('<b>HashOver:</b> You are blocked!');
						}
					}
				}
			}

			// Set login cookie; kick visitor back
			if (isset($_POST['login'])) {
				$this->cookies->set('hashover-login', hash('ripemd160', $this->xml_sanitize($this->name) . $_POST['password']));
				$this->cookies->set('message', $this->setup->text['logged_in']);
				exit(header('Location: ' . $this->kickback . '#comments'));
			}

			// Set login cookie; kick visitor back
			if (isset($_POST['logout'])) {
				$this->cookies->expire_cookie('hashover-login');
				$this->cookies->set('message', $this->setup->text['logged_out']);
				exit(header('Location: ' . $this->kickback . '#comments'));
			}

			// Check if a comment was posted
			if (!empty($_POST['comment']) and trim($_POST['comment'], " \t\r\n") != '') {
				// Check if comment thread directory exists
				if ($this->read_comments->data->storage_format == 'flat-file') {
					if (file_exists($this->setup->dir)) {
						// If yes, exit with error if it's not writable
						if (!is_writable($this->setup->dir)) {
							exit($this->setup->escape_output('<b>HashOver</b>: Comment thread directory at "' . $this->setup->dir . '" is not writable. Check directory permissions.', 'single'));
						}
					} else {
						// If no, exit with error if it can't be created
						if (!@mkdir($this->setup->dir, 0755, true) and !@chmod($this->setup->dir, 0755)) {
							exit($this->setup->escape_output('<b>HashOver</b>: Failed to create comment thread directory at "' . $this->setup->dir . '"', 'single'));
						}
					}
				}

				// Characters to search for and replace with in comments
				$data_search = array('\\', '"', '<', '>', "\r\n", "\n", "\r", '  ', '&lt;b&gt;', '&lt;/b&gt;', '&lt;u&gt;', '&lt;/u&gt;', '&lt;i&gt;', '&lt;/i&gt;', '&lt;s&gt;', '&lt;/s&gt;', '&lt;pre&gt;', '&lt;/pre&gt;', '&lt;code&gt;', '&lt;/code&gt;', '&lt;ul&gt;', '&lt;/ul&gt;', '&lt;ol&gt;', '&lt;/ol&gt;', '&lt;li&gt;', '&lt;/li&gt;', '&lt;blockquote&gt;', '&lt;/blockquote&gt;');
				$data_replace = array('&#92;', '&quot;', '&lt;', '&gt;', "\n", '<br>', '<br>', ' &nbsp;', '<b>', '</b>', '<u>', '</u>', '<i>', '</i>', '<s>', '</s>', '<pre>', '</pre>', '<code>', '</code>', '<ul>', '</ul>', '<ol>', '</ol>', '<li>', '</li>', '<blockquote>', '</blockquote>');

				$clean_code = preg_replace('/(((ftp|http|https){1}:\/\/)[a-zA-Z0-9-@:%_\+.~#?&\/=]+)/i', '\\1 ', $_POST['comment']); // Add space to end of URLs to separate '&' characters from escaped HTML tags
				$clean_code = str_ireplace($data_search, $data_replace, preg_replace('/\n{2,}/', "\n\n", preg_replace('/^\s+$/m', '', rtrim($clean_code, " \r\n")))); // Escape HTML tags; remove trailing new lines
				$clean_code = preg_replace('/^(<br><br>)/', '', preg_replace('/(<br><br>)$/', '', preg_replace('/(<br>){2,}/i', '<br><br>', $clean_code))); // Remove repetitive and trailing HTML <br> tags

				// HTML tags to automatically close
				$tags = array('code', 'b', 'i', 'u', 's', 'li', 'pre', 'blockquote', 'ul', 'ol');
				$cleantags = array('blockquote', 'ul', 'ol');

				// Check if all allowed HTML tags have been closed, if not add them at the end
				for ($tc = 0, $tcl = count($tags); $tc < $tcl; $tc++) {
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

				// Setup data from template; Store default information
				$write_cmt = $this->setup->data_template;
				$write_cmt['name'] = $this->xml_sanitize($this->name);
				$write_cmt['date'] = date(DATE_ISO8601);
				$write_cmt['body'] = $this->xml_sanitize($clean_code);

				// Store password and login ID if a password is given
				if (!empty($_POST['password'])) {
					$write_cmt['password'] = $this->setup->encryption->create_hash($_POST['password']);

					// Store login ID if it's not the same as the default name
					if ($write_cmt['name'] != $this->setup->default_name) {
						$write_cmt['login_id'] = hash('ripemd160', $write_cmt['name'] . $_POST['password']);
					}
				}

				// Store e-mail if one is given
				if (!empty($_POST['email']) and filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
					$encryption_keys = $this->setup->encryption->encrypt($this->xml_sanitize($this->email));
					$write_cmt['email'] = $encryption_keys['encrypted'];
					$write_cmt['encryption'] = $encryption_keys['keys'];

					// Set e-mail subscription if one is given
					if (!empty($_POST['subscribe'])) {
						$write_cmt['notifications'] = ($_POST['subscribe'] == 'on') ? 'yes' : 'no';
					}
				}

				// Store website URL if one is given
				if (!empty($this->website)) {
					$write_cmt['website'] = $this->xml_sanitize(trim($this->website, ' '));
				}

				// Store user IP address if one is given
				if ($this->setup->stores_ip_addrs == 'yes') {
					$write_cmt['ipaddr'] = $_SERVER['REMOTE_ADDR'];
				}

				// Edit comment
				if (isset($_POST['edit']) and (isset($_POST['password']) and isset($_POST['cmtfile'])) and !isset($_POST['delete'])) {
					$edit_cmt = $this->read_comments->data->read($_POST['cmtfile']);

					// Check if password matches the one in the file
					if ($this->setup->encryption->verify_hash($_POST['password'], $edit_cmt['password']) or $this->setup->user_is_admin == true) {
						$edit_cmt['name'] = $write_cmt['name'];
						$edit_cmt['website'] = $write_cmt['website'];

						if ($this->setup->user_is_admin == false) {
							$edit_cmt['email'] = $write_cmt['email'];
							$edit_cmt['encryption'] = $write_cmt['encryption'];
							$edit_cmt['password'] = $write_cmt['password'];
						}

						if (!empty($_POST['notify'])) {
							$edit_cmt['notifications'] = ($_POST['notify'] == 'on') ? 'yes' : 'no';
						}

						// Write edited comment to file
						if ($clean_code != $edit_cmt['body']) {
							$edit_cmt['body'] = $write_cmt['body'];
						}

						// Attempt to write edited comment
						if ($this->read_comments->data->save($edit_cmt, $_POST['cmtfile'], true)) {
							// Kick visitor back to comment(s)
							exit(header('Location: ' . $this->kickback . '#c' . str_replace('-', 'r', $_POST['cmtfile'])));
						} else {
							$this->cookies->set('message', $this->setup->text['post_fail']);
						}
					} else {
						$this->cookies->set('message', $this->setup->text['post_fail']);
					}

					exit(header('Location: ' . $this->kickback . '#comments'));
				}

				// Rename file for reply
				if (!empty($_POST['reply_to'])) {
					// Set reply directory information & "cookie" for successful reply
					$cmt_file = $_POST['reply_to'] . '-' . $this->read_comments->subfile_count[$_POST['reply_to']];
					$this->cookies->set('replied', $_POST['reply_to']);
				} else {
					$cmt_file = $this->read_comments->cmt_count;
				}

				// Write comment to file
				if ($this->read_comments->data->save($write_cmt, $cmt_file)) {
					$this->add_latest_comment($cmt_file);

					// Send notification e-mails
					$permalink = 'c' . str_replace('-', 'r', $cmt_file);
					$from_email = (!empty($_POST['email']) and $_POST['email'] != $this->setup->text['email'] and $this->setup->allows_user_replies == 'yes') ? $this->name . ' <' . $this->email . '>' : $this->name;
					$reverse_datasearch = array('&quot;', '&lt;', '&gt;', '<br>\n', '\n', '<br>', '&nbsp;', '\r');
					$reverse_datareplace = array('"', '<', '>', PHP_EOL, PHP_EOL, "\r", '  ', "\r");
					$mail_cmt = wordwrap('    ' . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($clean_code)), 76, "\n    ", true);
					$to_webmaster = '';

					// Notify commenter of reply
					if (!empty($_POST['reply_to'])) {
						$get_cmt = (object) $this->read_comments->data->read($_POST['reply_to']);
						$op_cmt = wordwrap('    ' . str_replace($reverse_datasearch, $reverse_datareplace, strip_tags($get_cmt->body)), 76, "\n    ", true);
						$to_commenter = "In reply to:\n\n" . $op_cmt . "\n\n";
						$to_webmaster = "In reply to " . $get_cmt->name . ":\n\n" . $op_cmt . "\n\n";
						$decryto = $this->setup->encryption->decrypt($get_cmt->email, $get_cmt->encryption);

						if (!empty($decryto) and $decryto != $this->setup->notification_email and $decryto != $this->email) {
							if ($get_cmt['notifications'] == 'yes') {
								if ($this->setup->allows_user_replies != 'yes') $this->header = "From: " . $this->setup->noreply_email . "\r\nReply-To: " . $this->setup->noreply_email;
								mail($decryto, $_SERVER['HTTP_HOST'] . ' - New Reply', "From $from_email:\n\n" . $mail_cmt . "\n\n$to_commenter----\nPermalink: " . $this->setup->page_url . '#' . $permalink . "\nPage: " . $this->setup->page_url, $this->header);
							}
						}
					}

					// Notify webmaster via e-mail
					if ($this->email != $this->setup->notification_email) {
						mail($this->setup->notification_email, 'New Comment', "From $from_email:\n\n" . $mail_cmt . "\n\n$to_webmaster----\nPermalink: " . $this->setup->page_url . '#' . $permalink . "\nPage: " . $this->setup->page_url, $this->header);
					}

					// Set blank cookie for successful comment, kick visitor back to comment
					$this->cookies->set('replied', '');
					$this->cookies->set('hashover-login', hash('ripemd160', $write_cmt['name'] . $_POST['password']));
					exit(header('Location: ' . $this->kickback . '#' . $permalink));
				} else {
					$this->cookies->set('message', $this->setup->text['post_fail']);
					exit(header('Location: ' . $this->kickback . '#comments'));
				}
			} else {
				// Set failed comment cookie
				$this->cookies->set('success', 'no');

				// Set message cookie to comment or reply requirement notice
				if (!empty($_POST['reply_to'])) {
					$this->cookies->set('replied', $_POST['reply_to']);
					$this->cookies->set('message', $this->setup->text['reply_needed']);
				} else {
					$this->cookies->set('message', $this->setup->text['cmt_needed']);
				}

				// Kick visitor back to comment form
				exit(header('Location: ' . $this->kickback . '#comments'));
			}
		}
	}

?>
