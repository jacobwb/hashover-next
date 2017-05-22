<?php namespace HashOver;

// Copyright (C) 2010-2017 Jacob Barkdull
// This file is part of HashOver.
//
// I, Jacob Barkdull, hereby release this work into the public domain.
// This applies worldwide. If this is not legally possible, I grant any
// entity the right to use this work for any purpose, without any
// conditions, unless such conditions are required by law.
//
//--------------------
//
// IMPORTANT NOTICE:
//
// To retain your settings and maintain proper functionality, when
// downloading or otherwise upgrading to a new version of HashOver it
// is important that you preserve this file, unless directed otherwise.
//
// It is also important to choose UNIQUE values for the encryption key,
// admin name, and admin password, as not doing so puts HashOver at
// risk of being hijacked. Allowing someone to delete comments and/or
// edit existing comments to post spam, impersonate you or your
// visitors in order to push some sort of agenda/propaganda, to defame
// you or your visitors, or to imply endorsement of some product(s),
// service(s), and/or political ideology.


class Settings
{
	// Required setup
	public    $notificationEmail	= 'example@example.com';	// E-mail for notification of new comments
	protected $encryptionKey	= '8CharKey';			// Unique encryption key
	protected $adminName		= 'admin';			// Login name to gain admin rights (case-sensitive)
	protected $adminPassword	= 'passwd';			// Login password to gain admin rights (case-sensitive)

	// Primary settings
	public $language		= 'auto';			// UI language, for example 'en', 'de', etc. 'auto' to use system locale
	public $languageVARNames	= [];				// The list of parameters to check for to get language required, [order is important],if not used leave the array void.
									// 	E.G ['Lang', 'MyLang'] it'll get the language required from the $_GET, $_POST, $_COOKIE variable that has the names: "Lang" or "MyLang"
	public $theme			= 'default';			// Comment Cascading Style Sheet (CSS)
	public $usesModeration		= false;			// Whether comments must be approved before they appear to other visitors
	public $dataFormat		= 'xml';			// Format comments will be stored in; options: xml, json, sql
	public $defaultName		= 'Anonymous';			// Default name to use when one isn't given
	public $allowsImages		= true;				// Whether external image URLs wrapped in [img] tags are embedded
	public $allowsLogin		= true;				// Whether users can login and logout (when false form cookies are still set)
	public $allowsLikes		= true;				// Whether a "Like" link is displayed
	public $allowsDislikes		= false;			// Whether a "Dislike" link is displayed; allowing Reddit-style voting
	public $usesAJAX		= true;				// Whether AJAX is used for posting, editing, and loading comments
	public $collapsesUI		= false;			// Whether the comment form, thread, and end links are all initially hidden
	public $collapsesComments	= true;				// Whether to hide comments and display a link to show them
	public $collapseLimit		= 3;				// Number of comments that aren't hidden
	public $replyMode		= 'thread';			// Whether to display replies as a 'thread' or as a 'stream'
	public $streamDepth		= 3;				// In stream mode, the number of reply indentions to allow before the thread flattens
	public $popularityThreshold	= 5;				// Minimum likes a comment needs to be popular
	public $popularityLimit		= 2;				// Number of comments allowed to become popular

	// Webmaster notification email
	public $notEmail_subject_txt		= 'New comment';	// The initial part of E-Mail notification subject
	public $notEmail_subject_appEmail	= false;		// Whether append the sender email to the E-Mail notification subject. NOTE, this is useful to prevent grouping done by Webmail softwares like GMail, when more emails arrive with the same subject. So you can never lose a comment.
	public $notEmail_subject_appName	= false;		// Whether append the name to the E-Mail notification subject. NOTE, this is useful to prevent grouping done by Webmail softwares like GMail, when more emails arrive with the same subject. So you can never lose a comment.
	public $notEmail_subject_appPage	= false;		// Whether append the page to the E-Mail notification subject. NOTE, this is useful to prevent grouping done by Webmail softwares like GMail, when more emails arrive with the same subject. So you can never lose a comment.
	
	// Emails
	public $emailSender			= NULL;			// Sender email, leave null to select it automatically		
	
	// Date and Time settings
	public $serverTimezone		= 'America/Los_Angeles';	// Server timezone
	public $usesUserTimezone	= true;				// Whether comment dates should use the user's timezone (JavaScript-mode)
	public $usesShortDates		= true;				// Whether comment dates are shortened, for example "X days ago"
	public $timeFormat		= 'g:ia';			// Time format, use 'H:i' for 24-hour format (see: http://php.net/manual/en/function.date.php)
	public $dateFormat		= 'm/d/Y';			// Date format (see: http://php.net/manual/en/function.date.php)

	// Field options, use true/false to enable/disable a field,
	// use 'required' to require a field be properly filled
	public $fieldOptions = array (
		'name'     => true,
		'password' => true,
		'email'    => true,
		'website'  => true
	);

	// Behavior settings
	public $displaysTitle		= true;				// Whether page title is shown or not
	public $formPosition		= 'top';			// Position for primary form; options: 'top' or 'bottom'
	public $usesAutoLogin		= true;				// Whether a user's first comment automatically logs them in
	public $showsReplyCount		= true;				// Whether to show reply count separately from total
	public $countIncludesDeleted	= true;				// Whether comment counts should include deleted comments
	public $iconMode		= 'image';			// How to display avatar icons (either 'image', 'count' or 'none')
	public $iconSize		= '45';				// Size of Gravatar icons in pixels
	public $imageFormat		= 'png';			// Format for icons and other images (use 'svg' for HDPI)
	public $usesLabels		= false;			// Whether to display labels above inputs
	public $usesCancelButtons	= true;				// Whether forms have "Cancel" buttons
	public $appendsCSS		= true;				// Whether to automatically add a CSS <link> element to the page <head>
	public $displaysRSSLink		= true;				// Whether a comment RSS feed link is displayed

	// Technical settings
	public $loginMethod		= 'defaultLogin';		// Login method class for handling user login information
	public $setsCookies		= true;				// Whether cookies are enabled
	public $secureCookies		= false;			// Whether cookies set over secure HTTPS will only be transmitted over HTTPS
	public $storesIPAddress		= false;			// Whether to store users' IP addresses
	public $allowsUserReplies	= false;			// Whether given e-mails are sent as reply-to address to users
	public $noreplyEmail		= 'noreply@example.com';	// E-mail used when no e-mail is given
	public $spamDatabase		= 'remote';			// Whether to use a remote or local spam database
	public $spamCheckModes		= 'php';			// Perform IP spam check in 'javascript' or 'php' mode, or 'both'
	public $gravatarDefault		= 'custom';			// Gravatar theme to use ('custom', 'identicon', 'monsterid', 'wavatar', or 'retro')
	public $gravatarForce		= false;			// Whether to force the themed Gravatar images instead of an avatar image
	public $minifiesJavaScript	= false;			// Whether JavaScript output should be minified
	public $minifyLevel		= 4;				// How much to minify JavaScript code, options: 1, 2, 3, 4
	public $enablesAPI		= true;				// API: true = fully-enabled, false = fully disabled, or array of modes
	public $latestMax		= 10;				// Maximum number of comments to save as latest comments
	public $latestTrimWidth		= 100;				// Number of characters to trim latest comments to, 0 for no trim
	public $userDeletionsUnlink	= false;			// Whether user deleted files are actually unlinked from the filesystem

	// Types of images allowed to be embedded in comments
	public $imageTypes = array (
		'jpeg',
		'jpg',
		'png',
		'gif'
	);

	// External domains allowed to remotely load HashOver scripts
	public $allowedDomains = array (
		// '*.example.com',
		// '*.example.org',
		// '*.example.net'
	);

	// General database options
	public $databaseType		= 'sqlite';			// Type of database, sqlite or mysql
	public $databaseName		= 'hashover-pages';		// Database name

	// SQL database options
	public $databaseHost		= 'localhost';			// Database host name
	public $databaseUser		= 'root';			// Database login user
	public $databasePassword	= 'password';			// Database login password
	public $databaseCharset		= 'utf8';			// Database character set

	// Automated settings
	public $isMobile		= false;

	// Technical settings placeholders
	public $rootDirectory;
	public $httpRoot;
	public $httpScripts;
	public $httpImages;
	public $cookieExpiration;
	public $domain;

	public function __construct ()
	{
		// Set server timezone
		date_default_timezone_set ($this->serverTimezone);

		// Set encoding
		mb_internal_encoding ('UTF-8');

		// Get parent directory
		$root_directory = dirname (__DIR__);

		// Get HTTP parent directory
		$document_root = realpath ($_SERVER['DOCUMENT_ROOT']);
		$http_directory = mb_substr ($root_directory, mb_strlen ($document_root));

		// Replace backslashes with forwardslashes on Windows
		if (DIRECTORY_SEPARATOR === '\\') {
			$http_directory = str_replace ('\\', '/', $http_directory);
		}

		// Determine HTTP or HTTPS
		$protocol = ($this->isHTTPS () ? 'https' : 'http') . '://';

		// Technical settings
		$this->rootDirectory	= $root_directory;		// Root directory for script
		$this->httpRoot		= $http_directory;		// Root directory for HTTP
		$this->cookieExpiration	= time () + 60 * 60 * 24 * 30;	// Cookie expiration date
		$this->domain		= $_SERVER['HTTP_HOST'];	// Domain name for refer checking & notifications
		$this->absolutePath	= $protocol . $this->domain;	// Absolute path or remote access

		// Synchronize settings
		$this->syncSettings ();
	}

	function isHTTPS ()
	{
		// The connection is HTTPS if server says so
		if (!empty ($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== 'off') {
			return true;
		}

		// Assume the connection is HTTPS on standard SSL port
		if ($_SERVER['SERVER_PORT'] == 443) {
			return true;
		}

		return false;
	}

	// Synchronizes specific settings after remote changes
	public function syncSettings ()
	{
		// Disable likes and dislikes if cookies are disabled
		if ($this->setsCookies === false) {
			$this->allowsLikes = false;
			$this->allowsDislikes = false;
		}

		// Setup default field options
		foreach (array ('name', 'password', 'email', 'website') as $field) {
			if (!isset ($this->fieldOptions[$field])) {
				$this->fieldOptions[$field] = true;
			}
		}

		// Disable password if name is disabled
		if ($this->fieldOptions['name'] === false) {
			$this->fieldOptions['password'] = false;
		}

		// Disable login if name or password is disabled
		if ($this->fieldOptions['name'] === false
		    or $this->fieldOptions['password'] === false)
		{
			$this->allowsLogin = false;
		}

		// Disable autologin if login is disabled
		if ($this->allowsLogin === false) {
			$this->usesAutoLogin = false;
		}

		$this->httpScripts	= $this->httpRoot . '/scripts';	// Script directory for HTTP
		$this->httpImages	= $this->httpRoot . '/images';	// Image directory for HTTP
	}
}
