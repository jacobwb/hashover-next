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


class WriteComments extends Secrets
{
	protected $setup;
	protected $mode;
	protected $thread;
	protected $postData;
	protected $locale;
	protected $cookies;
	protected $login;
	protected $spamCheck;
	protected $metadata;
	protected $crypto;
	protected $avatar;
	protected $templater;
	protected $mail;
	protected $referer;
	protected $name = '';
	protected $password = '';
	protected $loginHash = '';
	protected $email = '';
	protected $website = '';
	protected $data = array ();
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
		// Store parameters as properties
		$this->setup = $setup;
		$this->mode = $setup->usage['mode'];
		$this->thread = $thread;

		// Instantiate various classes
		$this->postData = new PostData ();
		$this->locale = new Locale ($setup);
		$this->cookies = new Cookies ($setup);
		$this->login = new Login ($setup);
		$this->spamCheck = new SpamCheck ($setup);
		$this->crypto = new Crypto ();
		$this->avatars = new Avatars ($setup);
		$this->templater = new Templater ($setup);
		$this->mail = new Email ($setup);

		// Setup initial login data
		$this->setupLogin ();

		// Get regular expression escaped admin path
		$admin_path = preg_quote ($this->setup->getHttpPath ('admin'), '/');

		// Attempt to get referer
		$referer = Misc::getArrayItem ($_SERVER, 'HTTP_REFERER');

		// Check if we're coming from an admin page
		if (preg_match ('/' . $admin_path . '/i', $referer)) {
			// If so, use it as the kickback URL
			$this->referer = $_SERVER['HTTP_REFERER'];
		} else {
			// If not, check if posting from remote domain
			if ($this->postData->remoteAccess === true) {
				// If so, use absolute path
				$this->referer = $setup->pageURL;
			} else {
				// If not, use relative path
				$this->referer = $setup->filePath;
			}

			// Add URL queries to kickback URL
			if (!empty ($setup->urlQueries)) {
				$this->referer .= '?' . $setup->urlQueries;
			}
		}
	}

	// Encodes HTML entities
	protected function encodeHTML ($value)
	{
		return htmlentities ($value, ENT_COMPAT, 'UTF-8', false);
	}

	// Sets header to redirect user back to the previous page
	protected function kickback ($anchor = 'comments')
	{
		if ($this->postData->viaAJAX === false) {
			header ('Location: ' . $this->referer . '#' . $anchor);
		}
	}

	// Displays message to visitor, via AJAX or redirect
	protected function displayMessage ($text, $error = false)
	{
		// Message type as string
		$message_type = ($error === true) ? 'error' : 'message';

		// Check if request is AJAX
		if ($this->postData->viaAJAX === true) {
			// If so, display JSON for JavaScript frontend
			echo Misc::jsonData (array (
				'message' => $text,
				'type' => $message_type
			));
		} else {
			// If not, set cookie to specified message
			$this->cookies->set ($message_type, $text);

			// And redirect user to previous page
			$this->kickback ('hashover-form-section');
		}
	}

	// Confirms attempted actions are to existing comments
	protected function verifyFile ($file)
	{
		// Attempt to get file
		$comment_file = $this->setup->getRequest ($file);

		// Check if file is set
		if ($comment_file !== false) {
			// Cast file to string
			$comment_file = (string)($comment_file);

			// Return true if POST file is in comment list
			if (in_array ($comment_file, $this->thread->commentList, true)) {
				return $comment_file;
			}

			// Set cookies to indicate failure
			if ($this->postData->viaAJAX !== true) {
				$this->cookies->setFailedOn ('comment', $this->postData->replyTo, false);
			}
		}

		// Throw exception as error message
		throw new \Exception (
			$this->locale->text['comment-needed']
		);
	}

	// Checks user IP against spam databases
	protected function checkForSpam ()
	{
		// Block user if they fill any trap fields
		foreach ($this->trapFields as $name) {
			if ($this->setup->getRequest ($name)) {
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

	// Checks login requirements
	protected function loginRequirements ()
	{
		// Check if a login is required
		if ($this->setup->requiresLogin === true) {
			// If so, return false if user is not logged in
			if ($this->login->userIsLoggedIn === false) {
				return false;
			}
		}

		// Otherwise, login requirements are met
		return true;
	}

	// Sets cookies
	public function login ($kickback = true)
	{
		// Check login requirements
		if ($this->loginRequirements () === false) {
			$this->displayMessage ('Normal login not allowed!', true);
			return false;
		}

		try {
			// Log the user in
			if ($this->setup->allowsLogin !== false) {
				$this->login->setLogin ();
			}

			// Kick visitor back if told to
			if ($kickback !== false) {
				$this->displayMessage ($this->locale->text['logged-in']);
			}
		} catch (\Exception $error) {
			// Kick visitor back with exception if told to
			if ($kickback !== false) {
				$this->displayMessage ($error->getMessage (), true);
				return true;
			}

			// Otherwise, throw exception as-is
			throw $error;
		}

		return true;
	}

	// Expires cookies
	public function logout ()
	{
		// Log the user out
		$this->login->clearLogin ();

		// Kick visitor back
		$this->displayMessage ($this->locale->text['logged-out']);

		return true;
	}

	// Sets up necessary login data
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
		if (!empty ($this->postData->data['password']) and !empty ($comment['password'])) {
			// If so, get the user input password
			$password = $this->encodeHTML ($this->postData->data['password']);

			// Get the comment password
			$comment_password = $comment['password'];

			// Attempt to compare the two passwords
			$match = $this->crypto->verifyHash ($password, $comment_password);

			// Authenticate if the passwords match
			if ($match === true) {
				$auth['user-owned'] = true;
				$auth['authorized'] = true;
			}
		}

		// Admin is always authorized after strict verification
		if ($this->login->verifyAdmin ($this->password) === true) {
			$auth['authorized'] = true;
		}

		return $auth;
	}

	// Deletes comment
	public function deleteComment ()
	{
		// Check login requirements
		if ($this->loginRequirements () === false) {
			$this->displayMessage ('You must be logged in to delete a comment!', true);
			return false;
		}

		try {
			// Authenticate user password
			$auth = $this->commentAuthentication ();

			// Check if user is authorized
			if ($auth['authorized'] === true) {
				// If so, strictly verify admin login
				$user_is_admin = $this->login->verifyAdmin ($this->password);

				// Unlink comment file indicator
				$user_deletions_unlink = ($this->setup->userDeletionsUnlink === true);
				$unlink_comment = ($user_deletions_unlink or $user_is_admin);

				// Attempt to delete comment file
				if ($this->thread->data->delete ($this->postData->file, $unlink_comment)) {
					// If successful, remove comment from latest comments metadata
					if ($unlink_comment === true) {
						$this->thread->data->removeFromLatest ($this->postData->file);
					}

					// And kick visitor back with comment deletion message
					$this->displayMessage ($this->locale->text['comment-deleted']);

					return true;
				}
			}

			// Otherwise sleep for 5 seconds
			sleep (5);

			// Then kick visitor back with comment posting error
			$this->displayMessage ($this->locale->text['post-fail'], true);

		} catch (\Exception $error) {
			$this->displayMessage ($error->getMessage (), true);
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

	// Extracts URLs for later injection
	protected function urlExtractor ($groups)
	{
		$link_number = count ($this->urls);
		$this->urls[] = $groups[1];

		return 'URL[' . $link_number . ']';
	}

	// Escapes all HTML tags excluding allowed tags
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

	// Sets up and tests for necessary comment data
	protected function setupCommentData ($editing = false)
	{
		// Post fails when comment is empty
		if (empty ($this->postData->data['comment'])) {
			// Set cookies to indicate failure
			if ($this->postData->viaAJAX !== true) {
				$this->cookies->setFailedOn ('comment', $this->postData->replyTo);
			}

			// Throw exception about reply requirement
			if (!empty ($this->postData->replyTo)) {
				throw new \Exception (
					$this->locale->text['reply-needed']
				);
			}

			// Throw exception about comment requirement
			throw new \Exception (
				$this->locale->text['comment-needed']
			);
		}

		// Strictly verify if the user is logged in as admin
		if ($this->login->verifyAdmin ($this->password) === true) {
			// If so, check if status is set in POST data is set
			if (!empty ($this->postData->data['status'])) {
				// If so, use status if it is allowed
				if (in_array ($this->postData->data['status'], $this->statuses, true)) {
					$this->data['status'] = $this->postData->data['status'];
				}
			}
		} else {
			// Check if setup is for a comment edit
			if ($editing === true) {
				// If so, set status to "pending" if moderation of user edits is enabled
				if ($this->setup->pendsUserEdits === true) {
					$this->data['status'] = 'pending';
				}
			} else {
				// If not, set status to "pending" if moderation is enabled
				if ($this->setup->usesModeration === true) {
					$this->data['status'] = 'pending';
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

		// Check if required fields have values
		$this->login->validateFields ();

		// Setup login data
		$this->setupLogin ();

		// Trim leading and trailing white space
		$clean_code = $this->postData->data['comment'];

		// URL regular expression
		$url_regex = '/((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+)/i';

		// Extract URLs from comment
		$clean_code = preg_replace_callback ($url_regex, 'self::urlExtractor', $clean_code);

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
		$this->data['body'] = $clean_code;

		// Store posting date
		$this->data['date'] = date (DATE_ISO8601);

		// Store name if one is given
		if ($this->setup->fieldOptions['name'] !== false) {
			if (!empty ($this->name)) {
				$this->data['name'] = $this->name;
			}
		}

		// Store password and login ID if a password is given
		if ($this->setup->fieldOptions['password'] !== false) {
			if (!empty ($this->password)) {
				$this->data['password'] = $this->password;
			}
		}

		// Store login ID if login hash is non-empty
		if (!empty ($this->loginHash)) {
			$this->data['login_id'] = $this->loginHash;
		}

		// Check if the e-mail field is enabled
		if ($this->setup->fieldOptions['email'] !== false) {
			// Check if we have an e-mail address
			if (!empty ($this->email)) {
				// Get encryption info for e-mail
				$encryption_keys = $this->crypto->encrypt ($this->email);

				// Set encrypted e-mail address
				$this->data['email'] = $encryption_keys['encrypted'];

				// Set decryption keys
				$this->data['encryption'] = $encryption_keys['keys'];

				// Set e-mail hash
				$this->data['email_hash'] = md5 (mb_strtolower ($this->email));

				// Get subscription status
				$subscribed = $this->setup->getRequest ('subscribe') ? 'yes' : 'no';

				// And set e-mail subscription if one is given
				$this->data['notifications'] = $subscribed;
			}
		}

		// Store website URL if one is given
		if ($this->setup->fieldOptions['website'] !== false) {
			if (!empty ($this->website)) {
				$this->data['website'] = $this->website;
			}
		}

		// Store user IP address if setup to and one is given
		if ($this->setup->storesIpAddress === true) {
			// Check if remote IP address exists
			if (!empty ($_SERVER['REMOTE_ADDR'])) {
				// If so, get XSS safe IP address
				$ip = Misc::makeXSSsafe ($_SERVER['REMOTE_ADDR']);

				// And set the IP address
				$this->data['ipaddr'] = $ip;
			}
		}

		return true;
	}

	// Converts a file name (1-2) to a permalink (hashover-c1r1)
	protected function filePermalink ($file)
	{
		return 'hashover-c' . str_replace ('-', 'r', $file);
	}

	// Edits a comment
	public function editComment ()
	{
		// Check login requirements
		if ($this->loginRequirements () === false) {
			$this->displayMessage ('You must be logged in to edit a comment!', true);
			return false;
		}

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
				if (!empty ($this->data['status'])) {
					$update_fields[] = 'status';
				}

				// Only set protected fields for update if passwords match
				if ($auth['user-owned'] === true) {
					$update_fields = array_merge ($update_fields, $this->protectedFields);
				}

				// Update login information and comment
				foreach ($update_fields as $key) {
					if (!empty ($this->data[$key])) {
						$auth['comment'][$key] = $this->data[$key];
					} else {
						unset ($auth['comment'][$key]);
					}
				}

				// Attempt to write edited comment
				if ($this->thread->data->save ($this->postData->file, $auth['comment'], true)) {
					// If successful, check if request is via AJAX
					if ($this->postData->viaAJAX === true) {
						// If so, return the comment data
						return array (
							'file' => $this->postData->file,
							'comment' => $auth['comment']
						);
					}

					// Otherwise kick visitor back to posted comment
					$this->kickback ($this->filePermalink ($this->postData->file));

					return true;
				}
			}

			// Otherwise sleep for 5 seconds
			sleep (5);

			// Then kick visitor back with comment posting error
			$this->displayMessage ($this->locale->text['post-fail'], true);

		} catch (\Exception $error) {
			$this->displayMessage ($error->getMessage (), true);
		}

		return false;
	}

	// Wordwraps text after adding indentation
	protected function indentWordwrap ($text)
	{
		// Line ending styles to convert
		$styles = array ("\r\n", "\r");

		// Convert line endings to UNIX-style
		$text = str_replace ($styles, "\n", $text);

		// Wordwrap the text to 64 characters long
		$text = wordwrap ($text, 64, "\n", true);

		// Split the text by paragraphs
		$paragraphs = explode ("\n\n", $text);

		// Indent the first line of each paragraph
		array_walk ($paragraphs, function (&$paragraph) {
			$paragraph = '    ' . $paragraph;
		});

		// Indent all other lines of each paragraph
		$paragraphs = str_replace ("\n", "\r\n    ", $paragraphs);

		// Convert paragraphs back to a string
		$text = implode ("\r\n\r\n", $paragraphs);

		return $text;
	}

	// Converts text paragraphs to HTML paragraph tags
	protected function paragraphsTags ($text, $indention = '')
	{
		// Initial HTML paragraphs
		$paragraphs = array ();

		// Break comment into paragraphs
		$ps = preg_split ('/(\r\n|\r|\n){2}/S', $text);

		// Wrap each paragraph in <p> tags and place <br> tags after each line
		for ($i = 0, $il = count ($ps); $i < $il; $i++) {
			// Place <br> tags after each line
			$paragraph = preg_replace ('/(\r\n|\r|\n)/S', '<br>\\1', $ps[$i]);

			// Create <p> tag
			$pTag = new HTMLTag ('p', $paragraph);

			// Add paragraph to HTML
			$paragraphs[] = $pTag->asHTML ($indention);
		}

		// Convert paragraphs array to string
		$html = implode ("\r\n\r\n" . $indention, $paragraphs);

		return $html;
	}

	// Sends a notification e-mail to another commenter
	protected function sendNotifications ($file)
	{
		// Initial comment data
		$data = array ();

		// Shorthand
		$default_name = $this->setup->defaultName;

		// Commenter's name
		$name = $this->name ?: $default_name;

		// Get comment permalink
		$permalink = $this->filePermalink ($file);

		// "New Comment" locale string
		$new_comment = $this->locale->text['new-comment'];

		// E-mail hash for Gravatar or empty for default avatar
		$hash = Misc::getArrayItem ($this->data, 'email_hash') ?: '';

		// Add avatar to data
		$data['avatar'] = $this->avatars->getGravatar ($hash, true, 128);

		// Add name of commenter or configurable default name to data
		$data['name'] = $name;

		// Add domain name to data
		$data['domain'] = $this->setup->domain;

		// Add plain text comment to data
		$data['text-comment'] = $this->indentWordwrap ($this->data['body']);

		// Add "From <name>" locale string to data
		$data['from'] = sprintf ($this->locale->text['from'], $name);

		// Add some locale strings to data
		$data['comment'] = $this->locale->text['comment'];
		$data['page'] = $this->locale->text['page'];
		$data['new-comment'] = $new_comment;

		// Add comment permalink to data
		$data['permalink'] = $this->setup->pageURL . '#' . $permalink;

		// Add page URL to data
		$data['url'] = $this->setup->pageURL;

		// Add page URL to data
		$data['title'] = $this->setup->pageTitle;

		// Add message about where the e-mail is coming from to data
		$data['sent-by'] = sprintf ($this->locale->text['sent-by'], $this->setup->domain);

		// Attempt to read reply comment
		$reply = $this->thread->data->read ($this->postData->replyTo);

		// Check if the reply comment read successfully
		if ($reply !== false) {
			// If so, decide name of recipient
			$reply_name = Misc::getArrayItem ($reply, 'name') ?: $default_name;

			// Add reply name to data
			$data['reply-name'] = $reply_name;

			// Add "In reply to" locale string to data
			$data['in-reply-to'] = sprintf ($this->locale->text['thread'], $reply_name);

			// Add indented body of recipient's comment to data
			$data['text-reply'] = $this->indentWordwrap ($reply['body']);

			// And add HTML version of the reply comment to data
			if ($this->setup->mailType !== 'text') {
				$data['html-reply'] = $this->paragraphsTags ($reply['body'], "\t\t\t\t");
			}
		}

		// Get and parse plain text e-mail notification
		$text_body = $this->templater->parseTheme ('email-notification.txt', $data);

		// Set subject to "New Comment - <domain here>"
		$this->mail->subject ($new_comment . ' - ' . $this->setup->domain);

		// Set plain text version of the message
		$this->mail->text ($text_body);

		// Check if e-mail format is anything other than text
		if ($this->setup->mailType !== 'text') {
			// If so, add HTML version of the message to data
			$data['html-comment'] = $this->paragraphsTags ($this->data['body'], "\t\t\t\t");

			// Get and parse HTML e-mail notification
			$html_body = $this->templater->parseTheme ('email-notification.html', $data);

			// And set HTML version of the message if told to
			$this->mail->html ($html_body);
		}

		// Only send admin notification if it's not admin posting
		if ($this->email !== $this->notificationEmail) {
			// Set e-mail to be sent to admin
			$this->mail->to ($this->notificationEmail);

			// Set e-mail as coming from the posting user
			$this->mail->from ($this->email);

			// And actually send the message
			$this->mail->send ();
		}

		// Do nothing else if the reply comment failed to read
		if ($reply === false) {
			return;
		}

		// Do nothing else if the reply comment lacks e-mail and decrypt info
		if (empty ($reply['email']) or empty ($reply['encryption'])) {
			return;
		}

		// Do nothing else if the reply comment poster disabled notifications
		if (!empty ($reply['notifications']) and $reply['notifications'] === 'no') {
			return;
		}

		// Otherwise, decrypt the reply e-mail address
		$reply_email = $this->crypto->decrypt ($reply['email'], $reply['encryption']);

		// Check if the reply e-mail is different than the one logged in
		if ($reply_email !== $this->email) {
			// If not, set message to be sent to reply comment e-mail
			$this->mail->to ($reply_email);

			// If so, check if users are allowed to reply by email
			if ($this->setup->allowsUserReplies === true) {
				// If so, set e-mail as coming from posting user
				$this->mail->from ($this->email);
			} else {
				// If not, set e-mail as coming from noreply e-mail
				$this->mail->from ($this->setup->noreplyEmail);
			}

			// And actually send the message
			$this->mail->send ();
		}
	}

	// Writes a comment
	protected function writeComment ($comment_file)
	{
		// Attempt to save comment
		$saved = $this->thread->data->save ($comment_file, $this->data);

		// Check if the comment was saved successfully
		if ($saved === true) {
			// If so, add it to latest comments metadata
			$this->thread->data->addLatestComment ($comment_file);

			// Send notification e-mails
			$this->sendNotifications ($comment_file);

			// Set/update user login cookie
			if ($this->setup->usesAutoLogin !== false
			    and $this->login->userIsLoggedIn !== true)
			{
				$this->login (false);
			}

			// Check if we're on AJAX
			if ($this->postData->viaAJAX === true) {
				// If so, increase comment count(s)
				$this->thread->countComment ($comment_file);

				// And return the comment data
				return array (
					'file' => $comment_file,
					'comment' => $this->data
				);
			}

			// Otherwise, kick visitor back to comment
			$this->kickback ($this->filePermalink ($comment_file));

			return true;
		}

		// If not, kick visitor back with an error
		$this->displayMessage ($this->locale->text['post-fail'], true);

		return false;
	}

	// Posts a comment
	public function postComment ()
	{
		// Initial status
		$status = false;

		// Check login requirements
		if ($this->loginRequirements () === false) {
			$this->displayMessage ('You must be logged in to comment!', true);
			return $status;
		}

		try {
			// Test for necessary comment data
			$this->setupCommentData ();

			// Set comment file name
			if (isset ($this->postData->replyTo)) {
				// Verify file exists
				$this->verifyFile ('reply-to');

				// Comment number
				$comment_number = $this->thread->threadCount[$this->postData->replyTo];

				// Rename file for reply
				$comment_file = $this->postData->replyTo . '-' . $comment_number;
			} else {
				$comment_file = $this->thread->primaryCount;
			}

			// Check if comment is SPAM
			$this->checkForSpam ();

			// Check if comment thread exists
			$this->thread->data->checkThread ();

			// Write the comment file
			$status = $this->writeComment ($comment_file);

		} catch (\Exception $error) {
			$this->displayMessage ($error->getMessage (), true);
		}

		return $status;
	}
}
