<?php

	// Copyright (C) 2010-2015 Jacob Barkdull
	//
	//	I, Jacob Barkdull, hereby release this work into the public domain. 
	//	This applies worldwide. If this is not legally possible, I grant any 
	//	entity the right to use this work for any purpose, without any 
	//	conditions, unless such conditions are required by law.
	//
	//--------------------
	//
	// IMPORTANT NOTICE:
	//
	//	To retain your settings and maintain proper functionality, when 
	//	downloading or otherwise upgrading to a new version of HashOver it 
	//	is important that you preserve this file, unless directed otherwise.
	//
	//	It is also important to choose UNIQUE values for the encryption key, 
	//	admin name, and admin password, as not doing so puts HashOver at 
	//	risk of being hijacked. Allowing someone to delete comments and/or 
	//	edit existing comments to post spam, impersonate you or your 
	//	visitors in order to push some sort of agenda/propaganda, to defame 
	//	you or your visitors, or to imply endorsement of some product(s), 
	//	service(s), and/or political ideology.
	//
	//
	// NOTE FOR NOOBS:
	//
	//	"true" means yes, "false" means no.


	class Settings
	{
		// Required setup
		public    $notificationEmail	= 'example@example.com';	// E-mail for notification of new comments
		protected $encryptionKey	= '8CharKey';			// Unique encryption key
		protected $adminName		= 'admin';			// Login name to gain admin rights (case-sensitive)
		protected $adminPassword	= 'passwd';			// Login password to gain admin rights (case-sensitive)

		// Primary settings
		public $language		= 'en';				// Language used for forms, buttons, links, and tooltips
		public $theme			= 'default';			// Comment Cascading Style Sheet (CSS)
		public $timezone		= 'America/Los_Angeles';	// Timezone
		public $usesModeration		= false;			// Whether comments must be approved before they appear to other visitors
		public $dataFormat		= 'xml';			// Format comments will be stored in; options: xml, json, sql
		public $allowsNames		= true;				// Whether users can enter their own name
		public $defaultName		= 'Anonymous';			// Default name to use when one isn't given
		public $allowsPasswords		= true;				// Whether users can protect their comments with a password (required for user logins)
		public $allowsEmails		= true;				// Whether users can enter an e-mail address (required for user e-mail notifications)
		public $allowsWebsites		= true;				// Whether users can enter a website
		public $allowsImages		= true;				// Whether external image URLs wrapped in [img] tags are embedded
		public $allowsDislikes		= false;			// Whether a "Dislike" link is display; allowing Reddit-style voting
		public $collapsesComments	= true;				// Whether to hide comments and display a link to show them
		public $collapseLimit		= 3;				// Number of comments that aren't hidden
		public $popularityThreshold	= 5;				// Minimum likes a comment needs to be popular
		public $popularityLimit		= 2;				// Number of comments allowed to become popular

		// Behavior settings
		public $displaysTitle		= true;				// Whether page title is shown or not
		public $formPosition		= 'top';			// Position for primary form; options: 'top' or 'bottom'
		public $showsReplyCount		= true;				// Whether to show reply count separately from total
		public $uses12HourTime		= true;				// Whether to use 12 hour time format, otherwise use 24 hour format
		public $usesShortDates		= true;				// Whether comment dates are shortened
		public $iconMode		= 'image';			// How to display avatar icons (either 'image', 'count' or 'none')
		public $iconSize		= '45';				// Size of Gravatar icons in pixels
		public $imageFormat		= 'png';			// Format for icons and other images (use 'svg' for HDPI)
		public $replyMode		= 'thread';			// Whether to display replies as a 'thread' or as a 'stream'
		public $usesLabels		= false;			// Whether to display labels above inputs
		public $usesCancelButtons	= true;				// Whether forms have "Cancel" buttons
		public $appendsCSS		= true;				// Whether to automatically add a CSS <link> element to the page <head>
		public $displaysRSSLink		= true;				// Whether a comment RSS feed link is displayed

		// Technical settings
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

		// Allowed image types when embedded images are allowed
		public $imageTypes = array (
			'jpeg',
			'jpg',
			'png',
			'gif'
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
		public $httpDirectory;
		public $cookieExpiration;
		public $domain;

		public
		function __construct ()
		{
			// Set timezone
			date_default_timezone_set ($this->timezone);

			// Set encoding
			mb_internal_encoding ('UTF-8');

			// Get parent directory
			$dirname = dirname (__DIR__);

			// Technical settings
			$this->rootDirectory	= $dirname;			// Root directory for script
			$this->httpDirectory	= '/' . basename ($dirname);	// Root directory for HTTP
			$this->cookieExpiration	= time () + 60 * 60 * 24 * 30;	// Cookie expiration date
			$this->domain		= $_SERVER['HTTP_HOST'];	// Domain name for refer checking & notifications

			// Check if visitor is on mobile device
			if (!empty ($_SERVER['HTTP_USER_AGENT'])) {
				if (preg_match ('/(android|blackberry|phone)/i', $_SERVER['HTTP_USER_AGENT'])) {
					// Adjust settings to accommodate
					$this->isMobile = true;
					$this->imageFormat = 'svg';
				}
			}
		}
	}

?>
