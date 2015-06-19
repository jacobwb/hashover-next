<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	This file is part of HashOver.
	//
	//	HashOver is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	HashOver is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	class WriteComments extends Settings
	{
		protected $readComments;
		protected $commentData;
		protected $setup;
		protected $locales;
		protected $cookies;
		protected $encryption;
		protected $metalevels;
		protected $headers;
		protected $userHeaders;
		protected $kickbackURL;
		protected $replyTo;
		protected $name = '';
		protected $password = '';
		protected $email = '';
		protected $website = '';
		protected $writeComment = array ();

		// Fake inputs used as spam trap fields
		protected $trap_fields = array (
			'summary',
			'age',
			'lastname',
			'address',
			'zip'
		);

		protected $postActions = array (
			'login',
			'logout',
			'post',
			'edit',
			'delete'
		);

		// Characters to search for and replace with in comments
		protected $data_search = array (
			'\\',
			'"',
			'<',
			'>',
			"\r\n",
			"\r",
			"\n",
			'  ',
			'&lt;b&gt;',
			'&lt;/b&gt;',
			'&lt;u&gt;',
			'&lt;/u&gt;',
			'&lt;i&gt;',
			'&lt;/i&gt;',
			'&lt;s&gt;',
			'&lt;/s&gt;',
			'&lt;pre&gt;',
			'&lt;/pre&gt;',
			'&lt;code&gt;',
			'&lt;/code&gt;',
			'&lt;ul&gt;',
			'&lt;/ul&gt;',
			'&lt;ol&gt;',
			'&lt;/ol&gt;',
			'&lt;li&gt;',
			'&lt;/li&gt;',
			'&lt;blockquote&gt;',
			'&lt;/blockquote&gt;'
		);

		// Replacements
		protected $data_replace = array (
			'&#92;',
			'&quot;',
			'&lt;',
			'&gt;',
			PHP_EOL,
			PHP_EOL,
			PHP_EOL,
			' &nbsp;',
			'<b>',
			'</b>',
			'<u>',
			'</u>',
			'<i>',
			'</i>',
			'<s>',
			'</s>',
			'<pre>',
			'</pre>',
			'<code>',
			'</code>',
			'<ul>',
			'</ul>',
			'<ol>',
			'</ol>',
			'<li>',
			'</li>',
			'<blockquote>',
			'</blockquote>'
		);

		public
		function __construct (ReadComments $read_comments, Locales $locales, Cookies $cookies)
		{
			parent::__construct ();

			$this->readComments = $read_comments;
			$this->commentData = $read_comments->data;
			$this->setup = $read_comments->setup;
			$this->locales = $locales;
			$this->cookies = $cookies;
			$this->encryption = new Encryption ($this->encryptionKey);

			$this->metalevels = array (
				$this->setup->dir,
				$this->setup->rootDirectory . '/pages'
			);

			// Default email headers
			$this->headers  = 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
			$this->headers .= 'From: ' . $this->noreplyEmail . "\r\n";
			$this->headers .= 'Reply-To: ' . $this->noreplyEmail;

			// Default email headers for users
			$this->userHeaders = $this->headers;

			// URL back to comment
			$this->kickbackURL = $this->setup->parsedURL['path'];

			// Add URL queries to kickback URL
			if (!empty ($this->setup->URLQueries)) {
				$this->kickbackURL .= '?' . $this->setup->URLQueries;
			}

			// Clean POST data; Force data to UTF-8
			foreach ($_POST as $name => $value) {
				$_POST[$name] = $this->XMLSanitize ($value);
			}

			// Get reply comment file
			if (!empty ($_POST['reply_to'])) {
				$this->replyTo = $_POST['reply_to'];
			}

			// Setup login information
			if ($this->setup->userIsLoggedIn and !isset ($_POST['edit'])) {
				$this->name = trim (html_entity_decode ($this->setup->userName, ENT_COMPAT, 'UTF-8'), " \r\n\t");
				$this->password = trim (html_entity_decode ($this->setup->userPassword, ENT_COMPAT, 'UTF-8'), " \r\n\t");
				$this->email = trim (html_entity_decode ($this->setup->userEmail, ENT_COMPAT, 'UTF-8'), " \r\n\t");
				$this->website = trim (html_entity_decode ($this->setup->userWebsite, ENT_COMPAT, 'UTF-8'), " \r\n\t");
			} else {
				// Set name
				if (!empty ($_POST['name'])) {
					$this->name = trim ($_POST['name'], " \r\n\t");
				}

				// Set password
				if (!empty ($_POST['password'])) {
					$this->password = trim ($_POST['password'], " \r\n\t");
				}

				// Set e-mail address and mail headers
				if (!empty ($_POST['email'])) {
					if (filter_var ($_POST['email'], FILTER_VALIDATE_EMAIL)) {
						$this->email = trim ($_POST['email'], " \r\n\t");

						// Set mail headers to user's e-mail address
						$this->headers  = 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
						$this->headers .= 'From: ' . $this->email . "\r\n";
						$this->headers .= 'Reply-To: ' . $this->email;
					}
				}

				// Set website URL
				if (!empty ($_POST['website'])) {
					$this->website = trim ($_POST['website'], " \r\n\t");

					// Prepend "http://" to website URL if missing
					if (!preg_match ('/htt(p|ps):\/\//i', $this->website)) {
						$this->website = 'http://' . $this->website;
					}
				}

				// Set cookies
				if (empty ($_POST['edit'])) {
					$this->cookies->set ('name', $this->name);
					$this->cookies->set ('password', $this->password);
					$this->cookies->set ('email', $this->email);
					$this->cookies->set ('website', $this->website);
				}
			}

			// Escape disallowed characters
			$this->name = htmlentities ($this->name, ENT_COMPAT, 'UTF-8', false);
			$this->password = htmlentities ($this->password, ENT_COMPAT, 'UTF-8', false);
			$this->email = htmlentities ($this->email, ENT_COMPAT, 'UTF-8', false);
			$this->website = htmlentities ($this->website, ENT_COMPAT, 'UTF-8', false);
		}

		// Confirm that attempted actions are to existing comments
		protected
		function verifyFile ($file)
		{
			if (!empty ($_POST[$file])) {
				$comment_file =(string) $_POST[$file];

				if (!in_array ($comment_file, $this->readComments->commentlist, true)) {
					if ($file === 'reply_to') {
						$this->cookies->set ('replied', $this->replyTo);
					}

					$this->cookies->set ('success', 'no');
					$this->kickback ($this->locales->locale['cmt_needed'], true);
				}
			}
		}

		protected
		function spamCheck ()
		{
			// Check trap fields
			foreach ($this->trap_fields as $name) {
				if (!empty ($_POST[$name])) {
					$is_spam = true;
					break;
				}
			}

			// Block for filing trap fields
			if (isset ($is_spam)) {
				exit ('<b>HashOver</b>: You are blocked!');
			} else {
				$spam_check = new SpamCheck ($this->setup);
				$spam_check_modes =& $this->spamCheckModes;

				// Check user's IP address against stopforumspam.com
				if ($spam_check_modes === 'both' or $spam_check_modes === $this->setup->mode) {
					if ($spam_check->{$this->spamDatabase}()) {
						exit ('<b>HashOver:</b> You are blocked!');
					}

					if (!empty ($spam_check->error)) {
						exit ('<b>HashOver:</b> ' . $spam_check->error);
					}
				}
			}
		}

		protected
		function kickback ($text = '', $error = false, $anchor = 'comments')
		{
			// Set cookie to specified message or error
			if (!empty ($text)) {
				$this->cookies->set ($error ? 'error' : 'message', $text);
			}

			// Set header to redirect user to previous page
			header ('Location: ' . $this->kickbackURL . '#' . $anchor);
			exit;
		}

		// Set login cookie; kick visitor back
		public
		function login ()
		{
			$this->spamCheck ();
			$this->cookies->set ('hashover-login', hash ('ripemd160', $this->name . $this->password));
			$this->kickback ($this->locales->locale['logged_in']);
		}

		// Expire login cookie; kick visitor back
		public
		function logout ()
		{
			$this->cookies->expireCookie ('hashover-login');
			$this->kickback ($this->locales->locale['logged_out']);
		}

		// Force a string to UTF-8 encoding and acceptable character range
		protected
		function XMLSanitize ($string)
		{
			$string = mb_convert_encoding ($string, 'UTF-16', 'UTF-8');
			$string = mb_convert_encoding ($string, 'UTF-8', 'UTF-16');
			$string = preg_replace ('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '?', $string);

			return $string;
		}

		protected
		function addLatestComment ($file)
		{
			if ($this->commentData->storageMode !== 'flat-file') {
				return false;
			}

			foreach ($this->metalevels as $level => $metafile) {
				$metafile .= '/.metadata';
				$metadata = array ();
				$data = array ('latest' => array ());

				if ($level === 0) {
					$metadata['title'] = $this->setup->pageTitle;
					$metadata['url'] = $this->setup->pageURL;
					$metadata['status'] = 'open';
				}

				if (file_exists ($metafile) and is_writable ($metafile)) {
					$data = json_decode (file_get_contents ($metafile), true);

					if ($level === 0) {
						$metadata['status'] = $data['status'];
						array_unshift ($data['latest'], (string) $file);
					} else {
						$comment_directory = basename ($this->metalevels[0]);
						array_unshift ($data['latest'], $comment_directory . '/' . $file);
					}

					if (count ($data['latest']) >= 10) {
						if (count ($data['latest']) >= $this->latestMax) {
							$max = max (10, $this->latestMax);
							$data['latest'] = array_slice ($data['latest'], 0, $max);
						}
					}
				}

				$metadata['latest'] = $data['latest'];

				// Save metadata
				$this->commentData->save_metadata ($metadata, $metafile);
			}
		}

		protected
		function removeFromLatest ($file)
		{
			if ($this->commentData->storageMode !== 'flat-file') {
				return false;
			}

			foreach ($this->metalevels as $level => $metafile) {
				$metafile .= '/.metadata';

				if (!file_exists ($metafile) or !is_writable ($metafile)) {
					continue;
				}

				$metadata = json_decode (file_get_contents ($metafile), true);
				$file = basename ($file);
				$latest = array ();

				for ($key = 0, $length = count ($metadata['latest']); $key < $length; $key++) {
					$comment_directory = basename ($this->metalevels[0]);
					$comment = ($level === 0) ? $file : $comment_directory . '/' . $file;

					if ($metadata['latest'][$key] !== $comment) {
						$latest[] = $metadata['latest'][$key];
					}
				}

				$metadata['latest'] = $latest;
				$this->commentData->save_metadata ($metadata, $metafile);
			}
		}

		// Get password via post
		protected
		function getPassword ()
		{
			if (!empty ($_POST['password'])) {
				$password = trim ($_POST['password'], " \r\n\t");
				$password = htmlentities ($password, ENT_COMPAT, 'UTF-8', false);

				return $password;
			}

			return '';
		}

		// Delete comment
		public
		function deleteComment ()
		{
			$this->verifyFile ('file');

			if (!empty ($_POST['file'])) {
				$get_pass = $this->commentData->read ($_POST['file']);
				$passwords_match = $this->encryption->verifyHash ($this->getPassword (), $get_pass['password']);

				// Check if password matches the one in the file
				if ($passwords_match or $this->setup->userIsAdmin) {
					// Delete the comment file
					if ($this->commentData->delete ($_POST['file'], $this->userDeletionsUnlink)) {
						$this->removeFromLatest ($_POST['file']);
						$this->kickback ($this->locales->locale['cmt_deleted']);
					}
				}
			}

			$this->kickback ($this->locales->locale['post_fail'], true);
		}

		protected
		function setupCommentData ()
		{
			// Trim leading and trailing white space
			$clean_code = trim ($_POST['comment'], "\r\n");

			// Add space to end of URLs to separate '&' characters from escaped HTML tags
			$clean_code = preg_replace ('/(((ftp|http|https){1}:\/\/)[a-z0-9-@:%_\+.~#?&\/=]+)/i', '\\1 ', $clean_code);

			// Escape HTML tags
			$clean_code = str_ireplace ($this->data_search, $this->data_replace, $clean_code);

			// Collapse multiple newlines to three maximum
			$clean_code = preg_replace ('/' . PHP_EOL . '{3,}/', str_repeat (PHP_EOL, 3), $clean_code);

			// Escape HTML inside of <code> tags
			$clean_code = preg_replace_callback ('/(<code>)(.*?)(<\/code>)/is', function ($grp) use ($clean_code) {
				return $grp[1] . htmlspecialchars ($grp[2], null, null, false) . $grp[3];
			}, $clean_code);

			// HTML tags to automatically close
			$tags = array (
				'code',
				'b',
				'i',
				'u',
				's',
				'li',
				'pre',
				'blockquote',
				'ul',
				'ol'
			);

			// Check if all allowed HTML tags have been closed, if not add them at the end
			for ($tc = 0, $tcl = count ($tags); $tc < $tcl; $tc++) {
				$open_tags = substr_count ($clean_code, '<' . $tags[$tc] . '>');
				$close_tags = substr_count ($clean_code, '</' . $tags[$tc] . '>');

				if ($open_tags !== $close_tags) {
					while ($open_tags > $close_tags) {
						$clean_code .= '</' . $tags[$tc] . '>';
						$close_tags++;
					}

					while ($close_tags > $open_tags) {
						$clean_code = preg_replace ('/<\/' . $tags[$tc] . '>/i', '', $clean_code, 1);
						$close_tags--;
					}
				}
			}

			$this->writeComment['body'] = $clean_code;
			$this->writeComment['status'] = $this->usesModeration ? 'pending' : 'approved';
			$this->writeComment['date'] = date (DATE_ISO8601);

			if ($this->allowsNames) {
				if (!empty ($this->name)) {
					$this->writeComment['name'] = $this->name;
				}

				// Store password and login ID if a password is given
				if ($this->allowsPasswords and !empty ($this->password)) {
					$this->writeComment['password'] = $this->encryption->createHash ($this->password);

					// Store login ID if name given not the same as the default name
					if ($this->writeComment['name'] !== $this->defaultName) {
						$this->writeComment['login_id'] = hash ('ripemd160', $this->writeComment['name'] . $this->password);
					}
				}
			}

			// Store e-mail if one is given
			if ($this->allowsEmails) {
				if (!empty ($this->email)) {
					$encryption_keys = $this->encryption->encrypt ($this->email);
					$this->writeComment['email'] = $encryption_keys['encrypted'];
					$this->writeComment['encryption'] = $encryption_keys['keys'];
					$this->writeComment['email_hash'] = md5 (strtolower ($this->email));

					// Set e-mail subscription if one is given
					$this->writeComment['notifications'] = !empty ($_POST['subscribe']) ? 'yes' : 'no';
				}
			}

			// Store website URL if one is given
			if ($this->allowsWebsites) {
				if (!empty ($this->website)) {
					$this->writeComment['website'] = $this->website;
				}
			}

			// Store user IP address if setup to and one is given
			if ($this->storesIPAddress and !empty ($_SERVER['REMOTE_ADDR'])) {
				$this->writeComment['ipaddr'] = $_SERVER['REMOTE_ADDR'];
			}
		}

		public
		function editComment ()
		{
			$this->verifyFile ('file');
			$this->setupCommentData ();

			// Edit comment
			if (!empty ($this->password) and !empty ($_POST['file'])) {
				$edit_comment = $this->commentData->read ($_POST['file']);
				$passwords_match = $this->encryption->verifyHash ($this->getPassword (), $edit_comment['password']);

				// Check if password matches the one in the file
				if ($passwords_match or $this->setup->userIsAdmin) {
					$edit_comment['body'] = $this->writeComment['body'];

					if ($this->setup->userIsAdmin === false) {
						$edit_comment['name'] = $this->writeComment['name'];
						$edit_comment['website'] = $this->writeComment['website'];
						$edit_comment['email'] = $this->writeComment['email'];
						$edit_comment['email_hash'] = $this->writeComment['email_hash'];
						$edit_comment['encryption'] = $this->writeComment['encryption'];
						$edit_comment['password'] = $this->writeComment['password'];
					}

					// Update e-mail subscription status
					$edit_comment['notifications'] = !empty ($_POST['subscribe']) ? 'yes' : 'no';

					// Attempt to write edited comment
					if ($this->commentData->save ($edit_comment, $_POST['file'], true)) {
						$this->kickback ('', false, 'c' . str_replace ('-', 'r', $_POST['file']));
					}
				}
			}

			$this->kickback ($this->locales->locale['post_fail'], true);
		}

		protected
		function indentedWordwrap ($text)
		{
			$text = wordwrap ($text, 66, "\n", true);
			$paragraphs = explode (PHP_EOL . PHP_EOL, $text);
			$paragraphs = str_replace ("\n", "\n    ", $paragraphs);

			array_walk ($paragraphs, function (&$paragraph) {
				$paragraph = '    ' . $paragraph;
			});

			return implode (PHP_EOL . PHP_EOL, $paragraphs);
		}

		public
		function postComment ()
		{
			$this->verifyFile ('reply_to');
			$this->spamCheck ();
			$this->setupCommentData ();

			// Post fails when comment is empty
			if (empty ($_POST['comment'])) {
				$this->cookies->set ('success', 'no');

				// Set reply cookie
				if (!empty ($this->replyTo)) {
					$this->cookies->set ('replied', $this->replyTo);

					// Kick visitor back; display message of reply requirement
					$this->kickback ($this->locales->locale['reply_needed'], true);
				}

				// Kick visitor back; display message of comment requirement
				$this->kickback ($this->locales->locale['cmt_needed'], true);
			}

			// Check if comment thread directory exists
			if ($this->commentData->storageMode === 'flat-file') {
				if (file_exists ($this->setup->dir)) {
					// If yes, check if it is or can be made to be writable
					if (!is_writable ($this->setup->dir) and !@chmod ($this->setup->dir, 0755)) {
						// Kick visitor back; display error
						$this->kickback ('Comment thread directory at "' . $this->setup->dir . '" is not writable. Check directory permissions.', true);
					}
				} else {
					// If no, attempt to create the directory
					if (!@mkdir ($this->setup->dir, 0755, true) and !@chmod ($this->setup->dir, 0755)) {
						// Kick visitor back; display error
						$this->kickback ('Failed to create comment thread directory at "' . $this->setup->dir . '"', true);
					}
				}
			}

			// Set comment file name
			if (!empty ($this->replyTo)) {
				// Rename file for reply
				$comment_file = $this->replyTo . '-' . $this->readComments->threadCount[$this->replyTo];
			} else {
				$comment_file = $this->readComments->primaryCount;
			}

			// Write comment to file
			if ($this->commentData->save ($this->writeComment, $comment_file)) {
				$this->addLatestComment ($comment_file);

				// Send notification e-mails
				$permalink = 'c' . str_replace ('-', 'r', $comment_file);
				$from_line = !empty ($this->name) ? $this->name : $this->defaultName;
				$mail_comment = $this->indentedWordwrap (html_entity_decode ($this->writeComment['body'], ENT_COMPAT, 'UTF-8'));
				$webmaster_reply = '';

				// Add user's e-mail address to "From" line
				if (!empty ($this->email) and $this->allowsUserReplies) {
					$from_line .= ' <' . $this->email . '>';
				}

				// Notify commenter of reply
				if (!empty ($this->replyTo)) {
					$reply_comment = $this->commentData->read ($this->replyTo);
					$reply_body = $this->indentedWordwrap ($reply_comment['body']);
					$webmaster_reply = 'In reply to ' . $reply_comment['name'] . ':' . "\n\n" . $reply_body . "\n\n";
					$reply_email = $this->encryption->decrypt ($reply_comment['email'], $reply_comment['encryption']);

					if (!empty ($reply_email) and $reply_email !== $this->email) {
						if ($reply_comment['notifications'] === 'yes') {
							if ($this->allowsUserReplies) {
								$this->userHeaders = $this->headers;
							}

							// Message body to original poster
							$reply_message  = 'From ' . $from_line . ":\n\n";
							$reply_message .= $mail_comment . "\n\n";
							$reply_message .= 'In reply to:' . "\n\n" . $reply_body . "\n\n" . '----' . "\n";
							$reply_message .= 'Permalink: ' . $this->setup->pageURL . '#' . $permalink . "\n";
							$reply_message .= 'Page: ' . $this->setup->pageURL;

							// Send
							mail ($reply_email, $this->domain . ' - New Reply', $reply_message, $this->userHeaders);
						}
					}
				}

				// Notify webmaster via e-mail
				if ($this->email !== $this->notificationEmail) {
					$webmaster_message  = 'From ' . $from_line . ":\n\n";
					$webmaster_message .= $mail_comment . "\n\n";
					$webmaster_message .= $webmaster_reply . '----' . "\n";
					$webmaster_message .= 'Permalink: ' . $this->setup->pageURL . '#' . $permalink . "\n";
					$webmaster_message .= 'Page: ' . $this->setup->pageURL;

					// Send
					mail ($this->notificationEmail, 'New Comment', $webmaster_message, $this->headers);
				}

				// Set/update user login cookie, kick visitor back to comment
				$this->cookies->set ('hashover-login', hash ('ripemd160', $this->name . $this->password));
				$this->kickback ('', false, $permalink);
			}

			$this->kickback ($this->locales->locale['post_fail'], true);

			if (!empty ($this->replyTo)) {
				$this->cookies->set ('replied', $this->replyTo);
			}
		}

		public
		function getAction ()
		{
			foreach ($this->postActions as $action) {
				if (empty ($_POST[$action])) {
					continue;
				}

				switch ($action) {
					case 'login': {
						$this->login ();
						break;
					}

					case 'logout': {
						$this->logout ();
						break;
					}

					case 'post': {
						$this->postComment ();
						break;
					}

					case 'edit': {
						$this->editComment ();
						break;
					}

					case 'delete': {
						$this->deleteComment ();
						break;
					}
				}

				break;
			}
		}
	}

?>
