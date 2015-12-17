<?php

// Copyright (C) 2010-2015 Jacob Barkdull
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
	protected $readComments;
	protected $commentData;
	protected $setup;
	protected $locales;
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
	protected $editPassword = '';
	protected $loginHash = '';
	protected $email = '';
	protected $website = '';
	protected $writeComment = array ();
	protected $ajax = false;

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
	protected $dataReplace = array (
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

	public function __construct (ReadComments $read_comments, Locales $locales, Cookies $cookies, Login $login, Misc $misc)
	{
		parent::__construct ();

		$this->readComments = $read_comments;
		$this->commentData = $read_comments->data;
		$this->setup = $read_comments->setup;
		$this->locales = $locales;
		$this->cookies = $cookies;
		$this->login = $login;
		$this->misc = $misc;
		$this->spamCheck = new SpamCheck ($this->setup);

		$this->metalevels = array (
			$this->setup->dir,
			$this->setup->rootDirectory . '/pages'
		);

		// Default email headers
		$this->setHeaders ($this->setup->noreplyEmail);

		// URL back to comment
		$this->kickbackURL = $this->setup->filePath;

		// Add URL queries to kickback URL
		if (!empty ($this->setup->URLQueries)) {
			$this->kickbackURL .= '?' . $this->setup->URLQueries;
		}

		// Setup login information
		if (isset ($_POST['edit'])) {
			$this->login->setLogin ();
		}

		// Escape disallowed characters
		$this->name = $this->encodeHTML ($this->login->name);
		$this->password = $this->encodeHTML ($this->login->password);
		$this->loginHash = $this->encodeHTML ($this->login->loginHash);
		$this->email = $this->encodeHTML ($this->login->email);
		$this->website = $this->encodeHTML ($this->login->website);

		// Set mail headers to user's e-mail address
		if (!empty ($this->email)) {
			$this->setHeaders ($this->email, false);
		}

		// Get password from edit form via post
		if (!empty ($this->postData['password'])) {
			$this->editPassword = $this->encodeHTML ($this->postData['password']);
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
		if ($this->AJAX === true) {
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

	// Set cookies for remembering state after post action
	protected function setStatusCookies ($input, $try_reply = true)
	{
		// Don't set the cookies if the request is via AJAX
		if ($this->AJAX === true) {
			return;
		}

		// Set success status cookie
		$this->cookies->set ('failed-on', $input);

		// Set reply cookie
		if ($try_reply === true and !empty ($this->replyTo)) {
			$this->cookies->set ('replied', $this->replyTo);
		}
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
			$this->setStatusCookies ('comment', false);

			// Throw exception as error message
			throw new Exception ($this->locales->locale['comment-needed']);
		}

		return false;
	}

	protected function spamCheck ()
	{
		// Check trap fields
		foreach ($this->trapFields as $name) {
			if (!empty ($_POST[$name])) {
				// Block for filing trap fields
				throw new Exception ('You are blocked!');
				return false;
			}
		}

		// Check user's IP address against stopforumspam.com
		if ($this->setup->spamCheckModes === 'both'
		    or $this->setup->spamCheckModes === $this->setup->mode)
		{
			if ($this->spamCheck->{$this->setup->spamDatabase}() === true) {
				throw new Exception ('You are blocked!');
				return false;
			}

			if (!empty ($this->spamCheck->error)) {
				throw new Exception ($this->spamCheck->error);
				return false;
			}
		}

		return true;
	}

	// Set cookies
	public function login ()
	{
		try {
			// Check if required fields have values
			$this->validateFields ();

		} catch (Exception $error) {
			$this->kickback ($error->getMessage (), true);
			return false;
		}

		// Log the user in
		$this->login->setLogin ();

		// Kick visitor back
		$this->kickback ($this->locales->locale['logged-in']);

		return true;
	}

	// Expire cookies
	public function logout ()
	{
		// Log the user out
		$this->login->clearLogin ();

		// Kick visitor back
		$this->kickback ($this->locales->locale['logged-out']);

		return true;
	}

	protected function addLatestComment ($file)
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
					if (count ($data['latest']) >= $this->setup->latestMax) {
						$max = max (10, $this->setup->latestMax);
						$data['latest'] = array_slice ($data['latest'], 0, $max);
					}
				}
			}

			$metadata['latest'] = $data['latest'];

			// Save metadata
			$this->commentData->saveMetadata ($metadata, $metafile);
		}
	}

	protected function removeFromLatest ($file)
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
			$this->commentData->saveMetadata ($metadata, $metafile);
		}
	}

	// Delete comment
	public function deleteComment ()
	{
		try {
			// Test for necessary comment data
			$this->verifyFile ('file');

		} catch (Exception $error) {
			$this->kickback ($error->getMessage (), true);
			return false;
		}

		if (!empty ($this->editPassword) and !empty ($this->file)) {
			$get_pass = $this->commentData->read ($this->file);
			$passwords_match = $this->setup->encryption->verifyHash ($this->editPassword, $get_pass['password']);

			// Check if password matches the one in the file
			if ($passwords_match === true or $this->login->userIsAdmin === true) {
				// Delete the comment file
				if ($this->commentData->delete ($this->file, $this->setup->userDeletionsUnlink)) {
					$this->removeFromLatest ($this->file);
					$this->kickback ($this->locales->locale['comment-deleted']);

					return true;
				}
			} else {
				sleep (5);
			}
		}

		$this->kickback ($this->locales->locale['post-fail'], true);
		return false;
	}

	// Check if required fields have values
	protected function validateFields ()
	{
		foreach ($this->setup->fieldOptions as $field => $status) {
			if ($status === 'required' and empty ($this->postData[$field])) {
				$this->setStatusCookies ($field);

				throw new Exception (sprintf (
					$this->locales->locale ('field-needed'), strtolower ($this->locales->locale ($field))
				));

				return false;
			}
		}
	}

	// Setup and test for necessary comment data
	protected function setupCommentData ()
	{
		// Check if required fields have values
		$this->validateFields ();

		// Post fails when comment is empty
		if (empty ($this->postData['comment'])) {
			$this->setStatusCookies ('comment');

			// Set reply cookie
			if (!empty ($this->replyTo)) {
				// Kick visitor back; display message of reply requirement
				throw new Exception ($this->locales->locale['reply-needed']);

				return false;
			}

			// Kick visitor back; display message of comment requirement
			throw new Exception ($this->locales->locale['comment-needed']);

			return false;
		}

		// Trim leading and trailing white space
		$clean_code = $this->postData['comment'];

		// Add space to end of URLs to separate '&' characters from escaped HTML tags
		$clean_code = preg_replace ('/(((ftp|http|https){1}:\/\/)[a-z0-9-@:%_\+.~#?&\/=]+)/i', '\\1 ', $clean_code);

		// Escape HTML tags
		$clean_code = str_ireplace ($this->dataSearch, $this->dataReplace, $clean_code);

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
		$this->writeComment['status'] = ($this->setup->usesModeration === true) ? 'pending' : 'approved';
		$this->writeComment['date'] = date (DATE_ISO8601);

		if ($this->setup->fieldOptions['name'] === true and !empty ($this->name)) {
			$this->writeComment['name'] = $this->name;

			// Store password and login ID if a password is given
			if ($this->setup->fieldOptions['password'] === true and !empty ($this->password)) {
				$this->writeComment['password'] = $this->password;

				// Store login ID if login hash is non-empty
				if (!empty ($this->loginHash)) {
					$this->writeComment['login_id'] = $this->loginHash;
				}
			}
		}

		// Store e-mail if one is given
		if ($this->setup->fieldOptions['email'] === true) {
			if (!empty ($this->email)) {
				$encryption_keys = $this->setup->encryption->encrypt ($this->email);
				$this->writeComment['email'] = $encryption_keys['encrypted'];
				$this->writeComment['encryption'] = $encryption_keys['keys'];
				$this->writeComment['email_hash'] = md5 (strtolower ($this->email));

				// Set e-mail subscription if one is given
				$this->writeComment['notifications'] = !empty ($_POST['subscribe']) ? 'yes' : 'no';
			}
		}

		// Store website URL if one is given
		if ($this->setup->fieldOptions['website'] === true) {
			if (!empty ($this->website)) {
				$this->writeComment['website'] = $this->website;
			}
		}

		// Store user IP address if setup to and one is given
		if ($this->setup->storesIPAddress === true) {
			if (!empty ($_SERVER['REMOTE_ADDR'])) {
				$this->writeComment['ipaddr'] = $this->misc->makeXSSsafe ($_SERVER['REMOTE_ADDR']);
			}
		}

		return true;
	}

	public function editComment ()
	{
		try {
			// Test for necessary comment data
			$this->verifyFile ('file');
			$this->setupCommentData ();

		} catch (Exception $error) {
			$this->kickback ($error->getMessage (), true);
			return false;
		}

		// Read original comment
		$edit_comment = $this->commentData->read ($this->file);
		$passwords_match = false;

		// Compare passwords
		if (!empty ($this->editPassword) and !empty ($this->file)) {
			$passwords_match = $this->setup->encryption->verifyHash ($this->editPassword, $edit_comment['password']);
		}

		// Check if password matches the one in the file
		if ($passwords_match or $this->login->userIsAdmin) {
			if ($this->login->userIsAdmin === false) {
				foreach ($edit_comment as $key => $value) {
					if (isset ($this->writeComment[$key])) {
						$edit_comment[$key] = $this->writeComment[$key];
					}
				}
			} else {
				$edit_comment['body'] = $this->writeComment['body'];
			}

			// Attempt to write edited comment
			if ($this->commentData->save ($edit_comment, $this->file, true)) {

				// Return the comment data on success via AJAX
				if ($this->AJAX === true) {
					return array (
						'file' => $this->file,
						'comment' => $edit_comment
					);
				}

				// Return with message on success
				$this->kickback ('', false, 'c' . str_replace ('-', 'r', $this->file));
				return true;
			}
		} else {
			sleep (5);
		}

		$this->kickback ($this->locales->locale['post-fail'], true);
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

	public function postComment ()
	{
		try {
			// Test for necessary comment data
			$this->verifyFile ('reply-to');
			$this->setupCommentData ();

			// Check if comment is SPAM
			$this->spamCheck ();

			// Check if comment thread exists
			$this->commentData->checkThread ();

		} catch (Exception $error) {
			$this->kickback ($error->getMessage (), true);
			return false;
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
			$from_line = !empty ($this->name) ? $this->name : $this->setup->defaultName;
			$mail_comment = html_entity_decode (strip_tags ($this->writeComment['body']), ENT_COMPAT, 'UTF-8');
			$mail_comment = $this->indentedWordwrap ($mail_comment);
			$webmaster_reply = '';

			// Notify commenter of reply
			if (!empty ($this->replyTo)) {
				$reply_comment = $this->commentData->read ($this->replyTo);
				$reply_body = html_entity_decode (strip_tags ($reply_comment['body']), ENT_COMPAT, 'UTF-8');
				$reply_body = $this->indentedWordwrap ($reply_body);
				$reply_name = !empty ($reply_comment['name']) ? $reply_comment['name'] : $this->setup->defaultName;
				$webmaster_reply = 'In reply to ' . $reply_name . ':' . "\r\n\r\n" . $reply_body . "\r\n\r\n";

				if (!empty ($reply_comment['email']) and !empty ($reply_comment['encryption'])) {
					$reply_email = $this->setup->encryption->decrypt ($reply_comment['email'], $reply_comment['encryption']);

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
				$this->login->setLogin ();
			}

			// Return the comment data on success via AJAX
			if ($this->AJAX === true) {
				// Increase comment count(s)
				$this->readComments->countComments ($comment_file);

				return array (
					'file' => $comment_file,
					'comment' => $this->writeComment
				);
			}

			// Kick visitor back to comment
			$this->kickback ('', false, $permalink);

			return true;
		}

		// Kick visitor back with an error on failure
		$this->kickback ($this->locales->locale['post-fail'], true);
		return false;
	}
}
