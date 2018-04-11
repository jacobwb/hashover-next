<?php namespace HashOver;

// Copyright (C) 2010-2018 Jacob Barkdull
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


class WriteComments extends PostData
{
	protected $setup;
	protected $encryption;
	protected $mode;
	protected $thread;
	protected $locale;
	protected $cookies;
	protected $login;
	protected $misc;
	protected $spamCheck;
	protected $metadata;
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
	public $closeTags = array (
		'b',
		'big',
		'blockquote',
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
	protected $statuses = array (
		'approved',
		'pending',
		'deleted'
	);

	public function __construct (Setup $setup, Thread $thread)
	{
		parent::__construct ();

		$this->setup = $setup;
		$this->encryption = $setup->encryption;
		$this->mode = $setup->usage['mode'];
		$this->thread = $thread;
		$this->locale = new Locale ($setup);
		$this->cookies = new Cookies ($setup);
		$this->login = new Login ($setup);
		$this->misc = new Misc ($this->mode);
		$this->spamCheck = new SpamCheck ($setup);
		$this->metadata = new Metadata ($setup, $thread);

		// Setup initial login data
		$this->setupLogin ();

		// Default email headers
		$this->setHeaders ($setup->noreplyEmail);

		// Check if we have an HTTP referer
		if (!empty ($_SERVER['HTTP_REFERER'])) {
			// If so, use it as the kickback URL
			$this->kickbackURL = $_SERVER['HTTP_REFERER'];
		} else {
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
				echo $this->misc->jsonData (array (
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
		// Attempt to get file
		$comment_file = $this->setup->getRequest ($file);

		// Check if file is set
		if ($comment_file !== false) {
			// Cast file to string
			$comment_file =(string) ($comment_file);

			// Return true if POST file is in comment list
			if (in_array ($comment_file, $this->thread->commentList, true)) {
				return $comment_file;
			}

			// Set cookies to indicate failure
			if ($this->viaAJAX !== true) {
				$this->cookies->setFailedOn ('comment', $this->replyTo, false);
			}
		}

		// Throw exception as error message
		throw new \Exception ($this->locale->text['comment-needed']);
	}

	protected function checkForSpam ()
	{
		// Check trap fields
		foreach ($this->trapFields as $name) {
			if ($this->setup->getRequest ($name)) {
				// Block for filing trap fields
				throw new \Exception ('You are blocked!');
			}
		}

		// Check user's IP address against local blocklist
		if ($this->spamCheck->checkList () === true) {
			throw new \Exception ('You are blocked!');
		}

		// Whether to check for spam in current mode
		if ($this->setup->spamCheckModes === 'both'
		    or $this->setup->spamCheckModes === $this->mode)
		{
			// Check user's IP address against local or remote database
			if ($this->spamCheck->{$this->setup->spamDatabase}() === true) {
				throw new \Exception ('You are blocked!');
			}

			// Throw any error message as exception
			if (!empty ($this->spamCheck->error)) {
				throw new \Exception ($this->spamCheck->error);
			}
		}

		return true;
	}

	// Set cookies
	public function login ($kickback = true)
	{
		try {
			// Log the user in
			if ($this->setup->allowsLogin !== false) {
				$this->login->setLogin ();
			}

			// Kick visitor back if told to
			if ($kickback !== false) {
				$this->kickback ($this->locale->text['logged-in']);
			}

		} catch (\Exception $error) {
			// Kick visitor back with exception if told to
			if ($kickback !== false) {
				$this->kickback ($error->getMessage (), true);
				return true;
			}

			// Otherwise, throw exception as-is
			throw $error;
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

	// Setup necessary login data
	protected function setupLogin ()
	{
		$this->name = $this->encodeHTML ($this->login->name);
		$this->password = $this->encodeHTML ($this->login->password);
		$this->loginHash = $this->encodeHTML ($this->login->loginHash);
		$this->email = $this->encodeHTML ($this->login->email);
		$this->website = $this->encodeHTML ($this->login->website);
	}

	// User comment authentication
	protected function commentAuthentication ()
	{
		// Verify file exists
		$file = $this->verifyFile ('file');

		// Read original comment
		$comment = $this->thread->data->read ($file);

		// Authentication data
		$auth = array (
			// Assume no authorization by default
			'authorized' => false,
			'user-owned' => false,

			// Original comment
			'comment' => $comment
		);

		// Return authorization data if we fail to get comment
		if ($comment === false) {
			return $auth;
		}

		// Check if we have both required passwords
		if (!empty ($this->postData['password']) and !empty ($comment['password'])) {
			// If so, get the user input password
			$user_password = $this->encodeHTML ($this->postData['password']);

			// Get the comment password
			$comment_password = $comment['password'];

			// Attempt to compare the two passwords
			if ($this->encryption->verifyHash ($user_password, $comment_password) === true) {
				$auth['user-owned'] = true;
				$auth['authorized'] = true;
			}
		}

		// Admin is always authorized after strict verification
		if ($this->setup->verifyAdmin ($this->password) === true) {
			$auth['authorized'] = true;
		}

		return $auth;
	}

	// Delete comment
	public function deleteComment ()
	{
		try {
			// Authenticate user password
			$auth = $this->commentAuthentication ();

			// Check if user is authorized
			if ($auth['authorized'] === true) {
				// Strict verification of an admin login
				$user_is_admin = $this->setup->verifyAdmin ($this->password);

				// Unlink comment file indicator
				$user_deletions_unlink = ($this->setup->userDeletionsUnlink === true);
				$unlink_comment = ($user_deletions_unlink or $user_is_admin);

				// If so, delete the comment file
				if ($this->thread->data->delete ($this->file, $unlink_comment)) {
					// Remove comment from latest comments metadata
					$this->metadata->removeFromLatest ($this->file);

					// And kick visitor back with comment deletion message
					$this->kickback ($this->locale->text['comment-deleted']);

					return true;
				}
			}

			// Otherwise sleep for 5 seconds
			sleep (5);

			// Then kick visitor back with comment posting error
			$this->kickback ($this->locale->text['post-fail'], true);

		} catch (\Exception $error) {
			// On exception kick visitor back with error
			$this->kickback ($error->getMessage (), true);
		}

		return false;
	}

	// Closes all allowed HTML tags
	public function tagCloser ($tags, $html)
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
					$html = preg_replace ('/<\/' . $tags[$tc] . '>/iS', '', $html, 1);
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

	// Escape all HTML tags excluding allowed tags
	public function htmlSelectiveEscape ($code)
	{
		// Escape all HTML tags
		$code = str_ireplace ($this->dataSearch, $this->dataReplace, $code);

		// Unescape allowed HTML tags
		foreach ($this->htmlTagSearch as $tag) {
			$escaped_tags = array ('&lt;' . $tag . '&gt;', '&lt;/' . $tag . '&gt;');
			$text_tags = array ('<' . $tag . '>', '</' . $tag . '>');
			$code = str_ireplace ($escaped_tags, $text_tags, $code);
		}

		return $code;
	}

	// Escapes HTML inside of <code> tags and markdown code blocks
	protected function codeEscaper ($groups)
	{
		return $groups[1] . htmlspecialchars ($groups[2], null, null, false) . $groups[3];
	}

	// Setup and test for necessary comment data
	protected function setupCommentData ($editing = false)
	{
		// Default status
		$new_status = 'approved';

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
				throw new \Exception ($this->locale->text['reply-needed']);
			}

			// Throw exception about comment requirement
			throw new \Exception ($this->locale->text['comment-needed']);
		}

		// Strictly verify if the user is logged in as admin
		if ($this->setup->verifyAdmin ($this->password) === true) {
			// If so, check if status is set in POST data is set
			if (!empty ($this->postData['status'])) {
				// If so, check if status is allowed
				if (in_array ($this->postData['status'], $this->statuses, true)) {
					// If so, use it
					$this->commentData['status'] = $this->postData['status'];
				}
			}
		} else {
			// Check if setup is for a comment edit
			if ($editing === true) {
				// If so, set status to "pending" if moderation of user edits is enabled
				if (($this->setup->usesModeration and $this->setup->pendsUserEdits) === true) {
					$this->commentData['status'] = 'pending';
				}
			} else {
				// If not, set status to "pending" if moderation is enabled
				if ($this->setup->usesModeration === true) {
					$this->commentData['status'] = 'pending';
				}
			}
		}

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

		// Setup login data
		$this->setupLogin ();

		// Set mail headers to user's e-mail address
		if (!empty ($this->email)) {
			$this->setHeaders ($this->email, false);
		}

		// Trim leading and trailing white space
		$clean_code = $this->postData['comment'];

		// Extract URLs from comment
		$clean_code = preg_replace_callback ('/((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+)/i', 'self::urlExtractor', $clean_code);

		// Escape all HTML tags excluding allowed tags
		$clean_code = $this->htmlSelectiveEscape ($clean_code);

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

		// Store posting date
		$this->commentData['date'] = date (DATE_ISO8601);

		// Check if name is enabled and isn't empty
		if ($this->setup->fieldOptions['name'] !== false) {
			// Store name
			if (!empty ($this->name)) {
				$this->commentData['name'] = $this->name;
			}
		}

		// Store password and login ID if a password is given
		if ($this->setup->fieldOptions['password'] !== false) {
			if (!empty ($this->password)) {
				$this->commentData['password'] = $this->password;
			}
		}

		// Store login ID if login hash is non-empty
		if (!empty ($this->loginHash)) {
			$this->commentData['login_id'] = $this->loginHash;
		}

		// Store e-mail if one is given
		if ($this->setup->fieldOptions['email'] !== false) {
			if (!empty ($this->email)) {
				$encryption_keys = $this->encryption->encrypt ($this->email);
				$this->commentData['email'] = $encryption_keys['encrypted'];
				$this->commentData['encryption'] = $encryption_keys['keys'];
				$this->commentData['email_hash'] = md5 (mb_strtolower ($this->email));

				// Set e-mail subscription if one is given
				$this->commentData['notifications'] = $this->setup->getRequest ('subscribe') ? 'yes' : 'no';
			}
		}

		// Store website URL if one is given
		if ($this->setup->fieldOptions['website'] !== false) {
			if (!empty ($this->website)) {
				$this->commentData['website'] = $this->website;
			}
		}

		// Store user IP address if setup to and one is given
		if ($this->setup->storesIpAddress === true) {
			if (!empty ($_SERVER['REMOTE_ADDR'])) {
				$this->commentData['ipaddr'] = $this->misc->makeXSSsafe ($_SERVER['REMOTE_ADDR']);
			}
		}

		return true;
	}

	public function editComment ()
	{
		try {
			// Authenticate user password
			$auth = $this->commentAuthentication ();

			// Check if user is authorized
			if ($auth['authorized'] === true) {
				// Login normal user with edited credentials
				if ($this->login->userIsAdmin === false) {
					$this->login (false);
				}

				// Set initial fields for update
				$update_fields = $this->editableFields;

				// Setup necessary comment data
				$this->setupCommentData (true);

				// Add status to editable fields if a new status is set
				if (!empty ($this->commentData['status'])) {
					$update_fields[] = 'status';
				}

				// Only set protected fields for update if passwords match
				if ($auth['user-owned'] === true) {
					$update_fields = array_merge ($update_fields, $this->protectedFields);
				}

				// Update login information and comment
				foreach ($update_fields as $key) {
					if (!empty ($this->commentData[$key])) {
						$auth['comment'][$key] = $this->commentData[$key];
					} else {
						unset ($auth['comment'][$key]);
					}
				}

				// Attempt to write edited comment
				if ($this->thread->data->save ($this->file, $auth['comment'], true)) {
					// If successful, check if request is via AJAX
					if ($this->viaAJAX === true) {
						// If so, return the comment data
						return array (
							'file' => $this->file,
							'comment' => $auth['comment']
						);
					}

					// Otherwise kick visitor back to posted comment
					$this->kickback ('', false, 'hashover-c' . str_replace ('-', 'r', $this->file));

					return true;
				}
			}

			// Otherwise sleep for 5 seconds
			sleep (5);

			// Then kick visitor back with comment posting error
			$this->kickback ($this->locale->text['post-fail'], true);

		} catch (\Exception $error) {
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
		if ($this->thread->data->save ($comment_file, $this->commentData)) {
			// Add comment to latest comments metadata
			$this->metadata->addLatestComment ($comment_file);

			// Send notification e-mails
			$permalink = 'hashover-c' . str_replace ('-', 'r', $comment_file);
			$from_line = !empty ($this->name) ? $this->name : $this->setup->defaultName;
			$mail_comment = html_entity_decode (strip_tags ($this->commentData['body']), ENT_COMPAT, 'UTF-8');
			$mail_comment = $this->indentedWordwrap ($mail_comment);
			$webmaster_reply = '';

			// Notify commenter of reply
			if (!empty ($this->replyTo)) {
				$reply_comment = $this->thread->data->read ($this->replyTo);
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
			if ($this->setup->usesAutoLogin !== false
			    and $this->login->userIsLoggedIn !== true)
			{
				$this->login (false);
			}

			// Return the comment data on success via AJAX
			if ($this->viaAJAX === true) {
				// Increase comment count(s)
				$this->thread->countComment ($comment_file);

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
				$comment_file = $this->replyTo . '-' . $this->thread->threadCount[$this->replyTo];
			} else {
				$comment_file = $this->thread->primaryCount;
			}

			// Check if comment is SPAM
			$this->checkForSpam ();

			// Check if comment thread exists
			$this->thread->data->checkThread ();

			// Write the comment file
			return $this->writeComment ($comment_file);

		} catch (\Exception $error) {
			// On exception kick visitor back with error
			$this->kickback ($error->getMessage (), true);
		}

		return false;
	}
}
