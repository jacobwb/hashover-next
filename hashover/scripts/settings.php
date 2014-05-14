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
	//	admin nickname, and admin password, as not doing so puts HashOver at 
	//	risk of being hijacking by someone, allowing them to delete comments, 
	//	edit existing comments to post spam, and/or impersonate you or your 
	//	visitors in order to push some sort of agenda/propaganda, to defame 
	//	you or your visitors, or to imply endorsement of some product(s), 
	//	service(s), and/or political ideology.


	class Settings {
		public $setting = array();
		protected $notification_email, $encryption_key, $admin_nickname, $admin_password;

		public function __construct() {
			// Required setup
			$this->encryption_key			= '8CharKey';			// Unique encryption key
			$this->notification_email		= 'example@example.com';	// E-mail for notification of new comments
			$this->admin_nickname			= 'admin';			// Nickname with admin rights (must be title-cased)
			$this->admin_password			= 'passwd';			// Password to gain admin rights

			// Various optional settings
			$this->setting['language']		= 'en';				// Language used for forms, buttons, links, and tooltips
			$this->setting['default_name']		= 'Anonymous';			// Nickname when one isn't given
			$this->setting['page_title']		= 'yes';			// Whether page title is shown or not
			$this->setting['short_dates']		= 'yes';			// Whether comment dates are shortened
			$this->setting['icons']			= 'yes';			// Whether comments have avatar icons (Gravatar)
			$this->setting['icon_size']		= '45';				// Size of Gravatar icons in pixels
			$this->setting['image_format']		= 'png';			// Format for icons and other images (use 'svg' for HDPI)
			$this->setting['html_template']		= 'default';			// Comment HTML layout template
			$this->setting['style_sheet']		= 'default';			// Comment Cascading Style Sheet (CSS)
			$this->setting['rows']			= '5';				// Default comment box height in rows
			$this->setting['popular']		= '5';				// Minimum likes a comment needs to be popular
			$this->setting['top_cmts']		= '2';				// Number of comments allowed to become popular
			$this->setting['ip_addrs']		= 'no';				// Whether to store users' IP addresses
			$this->setting['spam_check_modes']	= 'php';			// Perform IP spam check in 'javascript' or 'php' mode, or 'both'
			$this->setting['spam_database']		= 'remote';			// Whether to use a remote or local spam database
			$this->setting['user_reply']		= 'no';				// Whether given e-mails are sent as reply-to address to users
			$this->setting['data_format']		= 'xml';			// Format comments will be stored in; options: xml, json, sqlite

			// Timezone
			date_default_timezone_set('America/Los_Angeles');

			// Technical settings
			$this->setting['root_dir']	= '/hashover/';				// HTTP root directory for comments
			$this->setting['indention']	= 'left';				// Side to add comment indention on
			$this->setting['noreply_email']	= 'noreply@example.com';		// E-mail used when no e-mail is given
			$this->setting['expire']	= time() + 60 * 60 * 24 * 30;		// Cookies' expiration date
			$this->setting['domain']	= $_SERVER['HTTP_HOST'];		// Domain name for refer checking & notifications
		}
	}

?>
