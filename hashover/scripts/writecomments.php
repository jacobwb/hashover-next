<?php

// Copyright (C) 2010-2017 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	} else {
		exit ('<b>HashOver</b>: This is a class file.');
	}
}

class WriteComments extends PostData
{
	protected $setup;
	protected $encryption;
	protected $mode;
	protected $readComments;
	protected $formatData;
	protected $locale;
	protected $cookies;
	protected $login;
	protected $misc;
	protected $spamCheck;
	protected $metalevels;
	protected $headers;
	protected $userHeaders;
	protected $kickbackURL;
	protected $name = '';
	protected $password = '';
	protected $loginHash = '';
	protected $email = '';
	protected $website = '';
	protected $commentData = array ();
	protected $urls = array ();

	// Fake inputs used as spam trap fields
	protected $trapFields = array (
		'summary',
		'age',
		'lastname',
		'address',
		'zip'
	);

	// Characters to search for and replace with in comments
	protected $dataSearch = array (
		'\\',
		'"',
		'<',
		'>',
		"\r\n",
		"\r",
		"\n",
		'  '
	);

	// Character replacements
	protected $dataReplace = array (
		'&#92;',
		'&quot;',
		'&lt;',
		'&gt;',
		PHP_EOL,
		PHP_EOL,
		PHP_EOL,
		'&nbsp; '
	);

	// HTML tags to allow in comments
	protected $htmlTagSearch = array (
		'b',
		'big',
		'blockquote',
		'code',
		'em',
		'i',
		'li',
		'ol',
		'pre',
		's',
		'small',
		'strong',
		'sub',
		'sup',
		'u',
		'ul'
	);

	// HTML tags to automatically close
	protected $closeTags = array (
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

	// Unprotected fields to update when editing a comment
	protected $editableFields = array (
		'body',
		'name',
		'notifications',
		'website'
	);

	// Password protected fields
	protected $protectedFields = array (
		'password',
		'login_id',
		'email',
		'encryption',
		'email_hash'
	);

	// Possible comment status options
	protected $statusOptions = array (
		'approved',
		'pending',
		'deleted'
	);

	public function __construct (Setup $setup, ReadComments $read_comments)
	{
		parent::__construct ();

		$this->setup = $setup;
		$this->encryption = $setup->encryption;
		$this->mode = $setup->usage['mode'];
		$this->readComments = $read_comments;
		$this->formatData = $this->readComments->data;
		$this->locale = new Locale ($setup);
		$this->cookies = new Cookies ($setup);
		$this->login = new Login ($setup);
		$this->misc = new Misc ($this->mode);
		$this->spamCheck = new SpamCheck ($setup);

		$this->metalevels = array (
			$setup->dir,
			$setup->rootDirectory . '/pages'
		);

		// Default email headers
		$this->setHeaders ($setup->noreplyEmail);

		// Check if posting from remote domain
		if ($this->remoteAccess === true) {
			// If so, use absolute path
			$this->kickbackURL = $setup->pageURL;
		} else {
			// If not, use relative path
			$this->kickbackURL = $setup->filePath;
		}

		// Add URL queries to kickback URL
		if (!empty ($setup->URLQueries)) {
			$this->kickbackURL .= '?' . $setup->URLQueries;
		}
	}

	// Encodes HTML entities
	protected function encodeHTML ($value)
	{
		return htmlentities ($value, ENT_COMPAT, 'UTF-8', false);
	}

	// Set mail headers
	protected function setHeaders ($email, $user = true)
	{
		$this->headers  = 'Content-Type: text/plain; charset=UTF-8' . "\r\n";
		$this->headers .= 'From: ' . $email . "\r\n";
		$this->headers .= 'Reply-To: ' . $email;

		// Set commenter's headers to new value as well
		if ($user === true) {
			$this->userHeaders = $this->headers;
		}
	}

	protected function kickback ($text = '', $error = false, $anchor = 'comments')
	{
		$message_type = ($error) ? 'error' : 'message';

		// Return error as JSON if request is AJAX
		if ($this->viaAJAX === true) {
			if (!empty ($text)) {
				echo json_encode (array (
					'message' => $text,
					'type' => $message_type
				));
			}

			return;
		}

		// Set cookie to specified message or error
		if (!empty ($text)) {
			$this->cookies->set ($message_type, $text);
		}

		// Set header to redirect user to previous page
		header ('Location: ' . $this->kickbackURL . '#' . $anchor);
	}

	// Confirm that attempted actions are to existing comments
	protected function verifyFile ($file)
	{
		if (!empty ($_POST[$file])) {
			$comment_file =(string) $_POST[$file];

			// Return true if POST file is in comment list
			if (in_array ($comment_file, $this->readComments->commentList, true)) {
				return true;
			}

			// Set cookies to indicate failure
			if ($this->viaAJAX !== true) {
				$this->cookies->setFailedOn ('comment', $this->replyTo, false);
			}
		}

		// Throw exception as error message
		throw new Exception ($this->locale->text['comment-needed']);

		return false;
	}

	protected function checkForSpam ()
	{
		// Check trap fields
		foreach ($this->trapFields as $name) {
			if (!empty ($_POST[$name])) {
				// Block for filing trap fields
				throw new Exception ('You are blocked!');
				return false;
			}
		}

		// Check user's IP address against local blocklist
		if ($this->spamCheck->checkList () === true) {
			throw new Exception ('You are blocked!');
			return false;
		}

		// Whether to check for spam in current mode
		if ($this->setup->spamCheckModes === 'both'
		    or $this->setup->spamCheckModes === $this->mode)
		{
			// Check user's IP address against local or remote database
			if ($this->spamCheck->{$this->setup->spamDatabase}() === true) {
				throw new Exception ('You are blocked!');
				return false;
			}

			// Throw any error message as exception
			if (!empty ($this->spamCheck->error)) {
				throw new Exception ($this->spamCheck->error);
				return false;
			}
		}

		return true;
	}

	// Set cookies
	public function login ($kickback = true)
	{
		try {
			// Log the user in
			$this->login->setLogin ();

			// Kick visitor back
			if ($kickback !== false) {
				$this->kickback ($this->locale->text['logged-in']);
			}

		} catch (Exception $error) {
			throw new Exception ($error->getMessage ());
			return false;
		}

		return true;
	}

	// Expire cookies
	public function logout ()
	{
		// Log the user out
		$this->login->clearLogin ();

		// Kick visitor back
		$this->kickback ($this->locale->text['logged-out']);

		return true;
	}

	protected function addLatestComment ($file)
	{
		if ($this->formatData->storageMode !== 'flat-file') {
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

			if (!file_exists ($metafile) or !is_writable ($metafile)) {
				continue;
			}

			$contents = @file_get_contents ($metafile);
			$data = @json_decode ($contents, true);

			if ($contents === false or $data === null) {
				continue;
			}

			if (!isset ($data['status']) or !is_string ($data['status'])) {
				$data['status'] = 'open';
			}

			if (!isset ($data['latest']) or !is_array ($data['latest'])) {
				$data['latest'] = array ();
			}

			if ($level === 0) {
				$metadata['status'] = $data['status'];
				array_unshift ($data['latest'], (string) $file);
			} else {
				$comment_directory = basename ($this->metalevels[0]);
				array_unshift ($data['latest'], $comment_directory . '/' . $file);
			}

			$latest_count = count ($data['latest']);

			if ($latest_count >= 10) {
				if ($latest_count >= $this->setup->latestMax) {
					$max = max (10, $this->setup->latestMax);
					$data['latest'] = array_slice ($data['latest'], 0, $max);
				}
			}

			$metadata['latest'] = $data['latest'];

			// Save metadata
			$this->formatData->saveMetadata ($metadata, $metafile);
		}
	}

	protected function removeFromLatest ($file)
	{
		if ($this->formatData->storageMode !== 'flat-file') {
			return false;
		}

		foreach ($this->metalevels as $level => $metafile) {
			$metafile .= '/.metadata';

			if (!file_exists ($metafile) or !is_writable ($metafile)) {
				continue;
			}

			$contents = @file_get_contents ($metafile);
			$metadata = @json_decode ($contents, true);

			if ($contents === false or $metadata === null) {
				continue;
			}

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
			$this->formatData->saveMetadata ($metadata, $metafile);
		}
	}

	// Delete comment
	public function deleteComment ()
	{
		try {
			// Verify file exists
			$this->verifyFile ('file');

			// Assume passwords don't match by default
			$passwords_match = false;

			// Check if password and file values were given
			if (!empty ($this->postData['password']) and !empty ($this->file)) {
				// Read original comment
				$get_pass = $this->formatData->read ($this->file);

				// Compare passwords
				$edit_password = $this->encodeHTML ($this->postData['password']);
				$passwords_match = $this->encryption->verifyHash ($edit_password, $get_pass['password']);
			}

			// Check if password matches the one in the file
			if ($passwords_match === true or $this->login->userIsAdmin === true) {
				// Delete the comment file
				if ($this->formatData->delete ($this->file, $this->setup->userDeletionsUnlink)) {
					$this->removeFromLatest ($this->file);

					// Kick visitor back with comment deletion message
					$this->kickback ($this->locale->text['comment-deleted']);

					return true;
				}
			}

			// Otherwise sleep for 5 seconds
			sleep (5);

			// Then kick visitor back with comment posting error
			$this->kickback ($this->locale->text['post-fail'], true);

		} catch (Exception $error) {
			// On exception kick visitor back with error
			$this->kickback ($error->getMessage (), true);
		}

		return false;
	}

	// Closes all allowed HTML tags
	protected function tagCloser ($tags, $html)
	{
		for ($tc = 0, $tcl = count ($tags); $tc < $tcl; $tc++) {
			// Count opening and closing tags
			$open_tags = mb_substr_count ($html, '<' . $tags[$tc] . '>');
			$close_tags = mb_substr_count ($html, '</' . $tags[$tc] . '>');

			// Check if opening and closing tags aren't equal
			if ($open_tags !== $close_tags) {
				// Add closing tags to end of comment
				while ($open_tags > $close_tags) {
					$html .= '</' . $tags[$tc] . '>';
					$close_tags++;
				}

				// Remove closing tags for unopened tags
				while ($close_tags > $open_tags) {
					$html = preg_replace ('/<\/' . $tags[$tc] . '>/i', '', $html, 1);
					$close_tags--;
				}
			}
		}

		return $html;
	}

	// Extract URLs for later injection
	protected function urlExtractor ($groups)
	{
		$link_number = count ($this->urls);
		$this->urls[] = $groups[1];

		return 'URL[' . $link_number . ']';
	}

	// Escapes HTML inside of <code> tags and markdown code blocks
	protected function codeEscaper ($groups)
	{
		return $groups[1] . htmlspecialchars ($groups[2], null, null, false) . $groups[3];
	}

	// Setup and test for necessary comment data
	protected function setupCommentData ($editing = false)
	{
		// Check if setup is for a comment edit
		if ($editing === true) {
			// If so, mimic normal user login
			$this->login->prepareCredentials ();
			$this->login->updateCredentials ();
		} else {
			// If not, setup initial login information
			if ($this->login->userIsLoggedIn !== true) {
				$this->login->setCredentials ();
			}
		}

		// Check if required fields have values
		$this->login->validateFields ();

		// Post fails when comment is empty
		if (empty ($this->postData['comment'])) {
			// Set cookies to indicate failure
			if ($this->viaAJAX !== true) {
				$this->cookies->setFailedOn ('comment', $this->replyTo);
			}

			// Set reply cookie
			if (!empty ($this->replyTo)) {
				// Throw exception about reply requirement
				throw new Exception ($this->locale->text['reply-needed']);

				return false;
			}

			// Throw exception about comment requirement
			throw new Exception ($this->locale->text['comment-needed']);

			return false;
		}

		// Escape disallowed characters in login information
		$this->name = $this->encodeHTML ($this->login->name);
		$this->password = $this->encodeHTML ($this->login->password);
		$this->loginHash = $this->encodeHTML ($this->login->loginHash);
		$this->email = $this->encodeHTML ($this->login->email);
		$this->website = $this->encodeHTML ($this->login->website);

		// Set mail headers to user's e-mail address
		if (!empty ($this->email)) {
			$this->setHeaders ($this->email, false);
		}

		// Trim leading and trailing white space
		$clean_code = $this->postData['comment'];

		// Extract URLs from comment
		$clean_code = preg_replace_callback ('/((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+)/i', 'self::urlExtractor', $clean_code);

		// Escape HTML tags
		$clean_code = str_ireplace ($this->dataSearch, $this->dataReplace, $clean_code);

		// Unescape allowed HTML tags
		foreach ($this->htmlTagSearch as $tag) {
			$escaped_tags = array ('&lt;' . $tag . '&gt;', '&lt;/' . $tag . '&gt;');
			$text_tags = array ('<' . $tag . '>', '</' . $tag . '>');
			$clean_code = str_ireplace ($escaped_tags, $text_tags, $clean_code);
		}

		// Collapse multiple newlines to three maximum
		$clean_code = preg_replace ('/' . PHP_EOL . '{3,}/', str_repeat (PHP_EOL, 3), $clean_code);

		// Close <code> tags
		$clean_code = $this->tagCloser (array ('code'), $clean_code);

		// Escape HTML inside of <code> tags and markdown code blocks
		$clean_code = preg_replace_callback ('/(<code>)(.*?)(<\/code>)/is', 'self::codeEscaper', $clean_code);
		$clean_code = preg_replace_callback ('/(```)(.*?)(```)/is', 'self::codeEscaper', $clean_code);

		// Close remaining tags
		$clean_code = $this->tagCloser ($this->closeTags, $clean_code);

		// Inject original URLs back into comment
		$clean_code = preg_replace_callback ('/URL\[([0-9]+)\]/', function ($groups) {
			$url_key = $groups[1];
			$url = $this->urls[$url_key];

			return $url . ' ';
		}, $clean_code);

		// Store clean code
		$this->commentData['body'] = $clean_code;

		// Check if user is admin
		if ($this->login->userIsAdmin === true) {
			// If so, check if status is allowed
			if (in_array ($this->postData['status'], $this->statusOptions, true)) {
				$this->commentData['status'] = $this->postData['status'];
			}
		} else {
			// If not, store default status
			$this->commentData['status'] = ($this->setup->usesModeration === true) ? 'pending' : 'approved';
		}

		// Store posting date
		$this->commentData['date'] = date (DATE_ISO8601);

		// Check if name is enabled and isn't empty
		if ($this->setup->fieldOptions['name'] !== false and !empty ($this->name)) {
			// Store name
			$this->commentData['name'] = $this->name;

			// Store password and login ID if a password is given
			if ($this->setup->fieldOptions['password'] !== false and !empty ($this->password)) {
				$this->commentData['password'] = $this->password;

				// Store login ID if login hash is non-empty
				if (!empty ($this->loginHash)) {
					$this->commentData['login_id'] = $this->loginHash;
				}
			}
		}

		// Store e-mail if one is given
		if ($this->setup->fieldOptions['email'] !== false) {
			if (!empty ($this->email)) {
				$encryption_keys = $this->encryption->encrypt ($this->email);
				$this->commentData['email'] = $encryption_keys['encrypted'];
				$this->commentData['encryption'] = $encryption_keys['keys'];
				$this->commentData['email_hash'] = md5 (mb_strtolower ($this->email));

				// Set e-mail subscription if one is given
				$this->commentData['notifications'] = !empty ($_POST['subscribe']) ? 'yes' : 'no';
			}
		}

		// Store website URL if one is given
		if ($this->setup->fieldOptions['website'] !== false) {
			if (!empty ($this->website)) {
				$this->commentData['website'] = $this->website;
			}
		}

		// Store user IP address if setup to and one is given
		if ($this->setup->storesIPAddress === true) {
			if (!empty ($_SERVER['REMOTE_ADDR'])) {
				$this->commentData['ipaddr'] = $this->misc->makeXSSsafe ($_SERVER['REMOTE_ADDR']);
			}
		}

		return true;
	}

	public function editComment ()
	{
		try {
			// Verify file exists
			$this->verifyFile ('file');

			// Assume passwords don't match by default
			$passwords_match = false;

			if (!empty ($this->file)) {
				// Read original comment
				$edit_comment = $this->formatData->read ($this->file);

				// Check if password and file values were given
				if (!empty ($this->postData['password']) and !empty ($edit_comment['password'])) {
					// Compare passwords
					$edit_password = $this->encodeHTML ($this->postData['password']);
					$passwords_match = $this->encryption->verifyHash ($edit_password, $edit_comment['password']);
				}
			}

			// Check if password matches the one in the file
			if ($passwords_match === true or $this->login->userIsAdmin === true) {
				// Login normal user with edited credentials
				if ($this->login->userIsAdmin === false) {
					$this->login (false);
				}

				// Setup necessary comment data
				$this->setupCommentData (true);

				// Set initial fields for update
				$update_fields = $this->editableFields;

				// Only set status for update if user is admin
				if ($this->login->userIsAdmin === true) {
					$update_fields[] = 'status';
				}

				// Only set protected fields for update if passwords match
				if ($passwords_match === true) {
					$update_fields = array_merge ($update_fields, $this->protectedFields);
				}

				// Update login information and comment
				foreach ($update_fields as $key) {
					if (isset ($this->commentData[$key])) {
						$edit_comment[$key] = $this->commentData[$key];
					}
				}

				// Attempt to write edited comment
				if ($this->formatData->save ($edit_comment, $this->file, true)) {
					// If successful, check if request is via AJAX
					if ($this->viaAJAX === true) {
						// If so, return the comment data
						return array (
							'file' => $this->file,
							'comment' => $edit_comment
						);
					}

					// Otherwise kick visitor back to posted comment
					$this->kickback ('', false, 'c' . str_replace ('-', 'r', $this->file));

					return true;
				}
			}

			// Otherwise sleep for 5 seconds
			sleep (5);

			// Then kick visitor back with comment posting error
			$this->kickback ($this->locale->text['post-fail'], true);

		} catch (Exception $error) {
			// On exception kick visitor back with error
			$this->kickback ($error->getMessage (), true);
		}

		return false;
	}

	protected function indentedWordwrap ($text)
	{
		if (PHP_EOL !== "\r\n") {
			$text = str_replace (PHP_EOL, "\r\n", $text);
		}

		$text = wordwrap ($text, 66, "\r\n", true);
		$paragraphs = explode ("\r\n\r\n", $text);
		$paragraphs = str_replace ("\r\n", "\r\n    ", $paragraphs);

		array_walk ($paragraphs, function (&$paragraph) {
			$paragraph = '    ' . $paragraph;
		});

		return implode ("\r\n\r\n", $paragraphs);
	}

	protected function sendNotification ($from, $comment, $reply = '', $permalink, $email, $header)
	{
		$subject  = $this->setup->domain . ' - New ';
		$subject .= !empty ($reply) ? 'Reply' : 'Comment';

		// Message body to original poster
		$message  = 'From ' . $from . ":\r\n\r\n";
		$message .= $comment . "\r\n\r\n";
		$message .= 'In reply to:' . "\r\n\r\n" . $reply . "\r\n\r\n" . '----' . "\r\n\r\n";
		$message .= 'Permalink: ' . $this->setup->pageURL . '#' . $permalink . "\r\n\r\n";
		$message .= 'Page: ' . $this->setup->pageURL;

		// Send e-mail
		mail ($email, $subject, $message, $header);
	}

	protected function writeComment ($comment_file)
	{
		// Write comment to file
		if ($this->formatData->save ($this->commentData, $comment_file)) {
			$this->addLatestComment ($comment_file);

			// Send notification e-mails
			$permalink = 'c' . str_replace ('-', 'r', $comment_file);
			$from_line = !empty ($this->name) ? $this->name : $this->setup->defaultName;
			$mail_comment = html_entity_decode (strip_tags ($this->commentData['body']), ENT_COMPAT, 'UTF-8');
			$mail_comment = $this->indentedWordwrap ($mail_comment);
			$webmaster_reply = '';

			// Notify commenter of reply
			if (!empty ($this->replyTo)) {
				$reply_comment = $this->formatData->read ($this->replyTo);
				$reply_body = html_entity_decode (strip_tags ($reply_comment['body']), ENT_COMPAT, 'UTF-8');
				$reply_body = $this->indentedWordwrap ($reply_body);
				$reply_name = !empty ($reply_comment['name']) ? $reply_comment['name'] : $this->setup->defaultName;
				$webmaster_reply = 'In reply to ' . $reply_name . ':' . "\r\n\r\n" . $reply_body . "\r\n\r\n";

				if (!empty ($reply_comment['email']) and !empty ($reply_comment['encryption'])) {
					$reply_email = $this->encryption->decrypt ($reply_comment['email'], $reply_comment['encryption']);

					if ($reply_email !== $this->email
					    and !empty ($reply_comment['notifications'])
					    and $reply_comment['notifications'] === 'yes')
					{
						if ($this->setup->allowsUserReplies === true) {
							$this->userHeaders = $this->headers;

							// Add user's e-mail address to "From" line
							if (!empty ($this->email)) {
								$from_line .= ' <' . $this->email . '>';
							}
						}

						// Message body to original poster
						$reply_message  = 'From ' . $from_line . ":\r\n\r\n";
						$reply_message .= $mail_comment . "\r\n\r\n";
						$reply_message .= 'In reply to:' . "\r\n\r\n" . $reply_body . "\r\n\r\n" . '----' . "\r\n\r\n";
						$reply_message .= 'Permalink: ' . $this->setup->pageURL . '#' . $permalink . "\r\n\r\n";
						$reply_message .= 'Page: ' . $this->setup->pageURL;

						// Send
						mail ($reply_email, $this->setup->domain . ' - New Reply', $reply_message, $this->userHeaders);
					}
				}
			}

			// Notify webmaster via e-mail
			if ($this->email !== $this->setup->notificationEmail) {
				// Add user's e-mail address to "From" line
				if (!empty ($this->email)) {
					$from_line .= ' <' . $this->email . '>';
				}

				$webmaster_message  = 'From ' . $from_line . ":\r\n\r\n";
				$webmaster_message .= $mail_comment . "\r\n\r\n";
				$webmaster_message .= $webmaster_reply . '----' . "\r\n\r\n";
				$webmaster_message .= 'Permalink: ' . $this->setup->pageURL . '#' . $permalink . "\r\n\r\n";
				$webmaster_message .= 'Page: ' . $this->setup->pageURL;

				// Send
				mail ($this->setup->notificationEmail, 'New Comment', $webmaster_message, $this->headers);
			}

			// Set/update user login cookie
			if ($this->setup->usesAutoLogin !== false) {
				$this->login (false);
			}

			// Return the comment data on success via AJAX
			if ($this->viaAJAX === true) {
				// Increase comment count(s)
				$this->readComments->countComment ($comment_file);

				return array (
					'file' => $comment_file,
					'comment' => $this->commentData
				);
			}

			// Kick visitor back to comment
			$this->kickback ('', false, $permalink);

			return true;
		}

		// Kick visitor back with an error on failure
		$this->kickback ($this->locale->text['post-fail'], true);
		return false;
	}

	public function postComment ()
	{
		try {
			// Test for necessary comment data
			$this->setupCommentData ();

			// Set comment file name
			if (isset ($this->replyTo)) {
				// Verify file exists
				$this->verifyFile ('reply-to');

				// Rename file for reply
				$comment_file = $this->replyTo . '-' . $this->readComments->threadCount[$this->replyTo];
			} else {
				$comment_file = $this->readComments->primaryCount;
			}

			// Check if comment is SPAM
			$this->checkForSpam ();

			// Check if comment thread exists
			$this->formatData->checkThread ();

			// Write the comment file
			return $this->writeComment ($comment_file);

		} catch (Exception $error) {
			// On exception kick visitor back with error
			$this->kickback ($error->getMessage (), true);
		}

		return false;
	}
}
