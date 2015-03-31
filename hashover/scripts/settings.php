<?php

	// Copyright (C) 2014 Jacob Barkdull
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
	//	risk of being hijacking by someone, allowing them to delete comments, 
	//	edit existing comments to post spam, and/or impersonate you or your 
	//	visitors in order to push some sort of agenda/propaganda, to defame 
	//	you or your visitors, or to imply endorsement of some product(s), 
	//	service(s), and/or political ideology.


	class Settings
	{
		// Required setup
		public $notification_email	= 'example@example.com';	// E-mail for notification of new comments
		protected $encryption_key	= '8CharKey';			// Unique encryption key
		protected $admin_name		= 'admin';			// Login name to gain admin rights (case-sensitive)
		protected $admin_password	= 'passwd';			// Login password to gain admin rights (case-sensitive)

		// Various optional settings
		public $language		= 'en';				// Language used for forms, buttons, links, and tooltips
		public $theme			= 'default';			// Comment Cascading Style Sheet (CSS)
		public $default_name		= 'Anonymous';			// Nickname when one isn't given
		public $display_title		= 'yes';			// Whether page title is shown or not
		public $shows_count_total	= 'yes';			// Whether to show reply count separately from total
		public $timezone		= 'America/Los_Angeles';	// Timezone
		public $uses_12h_time		= 'yes';			// Whether to use 12 hour time format, otherwise use 24 hour format
		public $icon_mode		= 'image';			// How to display avatar icons (either 'image', 'count' or 'none')
		public $icon_size		= '45';				// Size of Gravatar icons in pixels
		public $allows_images		= 'yes';			// Whether external image URLs wrapped in [img] tags are embedded
		public $allows_dislikes		= 'no';				// Whether a "Dislike" link is display; allowing Reddit-style voting
		public $collapses_comments	= 'yes';			// Whether to hide comments and display a link to show them
		public $collapse_limit		= 3;				// Number of comments that aren't hidden
		public $pop_threshold		= 5;				// Minimum likes a comment needs to be popular
		public $pop_limit		= 2;				// Number of comments allowed to become popular
		public $reply_mode		= 'thread';			// Whether to display replies as a 'thread' or as a 'stream'
		public $indention		= 'left';			// Side to add comment indention on
		public $image_format		= 'png';			// Format for icons and other images (use 'svg' for HDPI)
		public $stores_ip_addrs		= 'no';				// Whether to store users' IP addresses
		public $spam_check_modes	= 'php';			// Perform IP spam check in 'javascript' or 'php' mode, or 'both'
		public $spam_database		= 'remote';			// Whether to use a remote or local spam database
		public $appends_css_link	= 'yes';			// Whether to automatically add a CSS <link> element to the page <head>
		public $displays_rss_link	= 'yes';			// Whether a comment RSS feed link is displayed

		// Technical settings
		public $data_format		= 'xml';			// Format comments will be stored in; options: xml, json, sql
		public $parses_huge		= 'yes';			// Whether to condense the XML data into a single string to parse
		public $uses_short_dates	= 'yes';			// Whether comment dates are shortened
		public $enable_api		= 'yes';			// API: 'yes' = fully-enabled, 'no' = fully disabled, or array of modes
		public $secure_cookies		= 'no';				// Whether cookies set over secure HTTPS will only be transmitted over HTTPS
		public $latest_num		= 10;				// Number of comments to save as latest comments
		public $latest_trimwidth	= 100;				// Number of characters to trim latest comments to, 0 for no trim
		public $allows_user_replies	= 'no';				// Whether given e-mails are sent as reply-to address to users
		public $noreply_email		= 'noreply@example.com';	// E-mail used when no e-mail is given

		// Allowed image types when embedded images are allowed
		public $image_types = array(
			'jpeg',
			'jpg',
			'png',
			'gif'
		);

		// General database options
		public $dbtype			= 'sqlite';			// Type of database, sqlite or mysql
		public $dbname			= 'hashover-pages';		// Database name

		// MySQL database options
		public $dbhost			= 'localhost';			// Database host name
		public $dbuser			= 'root';			// Database login user
		public $dbpass			= 'password';			// Database login password

		public function __construct()
		{
			$dirname = dirname(__DIR__);

			// Technical settings
			$this->root_dir		= '/' . basename($dirname);	// HTTP root directory for comments
			$this->expire		= time() + 60 * 60 * 24 * 30;	// Cookies' expiration date
			$this->domain		= $_SERVER['HTTP_HOST'];	// Domain name for refer checking & notifications
		}
	}

?>
