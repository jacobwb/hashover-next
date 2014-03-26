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
	// NOTICE:
	//
	//	To retain your settings when downloading or otherwise upgrading 
	//	to a new version of HashOver, it is recommended that you 
	//	preserve this file, unless directed otherwise.


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	// Various Settings
	$root_dir	= '/hashover/';					// HTTP root directory for comments
	$language	= 'en';						// Language used for forms, buttons, links, and tooltips
	$name		= 'GNU Knows Who';				// Nickname when one isn't given
	$html_template	= 'default';					// Comment HTML layout template
	$style_sheet	= 'default';					// Comment Cascading Style Sheet (CSS)
	$page_title	= 'yes';					// Whether page title is shown or not
	$short_dates	= 'yes';					// Whether comment dates are shortened
	$icons		= 'yes';					// Whether comments have avatar icons (Gravatar)
	$icon_size	= '45';						// Size of Gravatar icons in pixels
	$indention	= 'left';					// Side to add comment indention on
	$rows		= '5';						// Default comment box height in rows
	$popular	= '5';						// Minimum likes a comment needs to be popular
	$top_cmts	= '2';						// Number of comments allowed to become popular
	$ip_addrs	= 'no';						// Whether to store users' IP addresses
	$spam_IP_check	= 'php';					// Options 'javascript' / 'php' for respective modes, or 'both'
	$expire		= time() + 60 * 60 * 24 * 30;			// Cookies' expiration date
	$domain		= $_SERVER['HTTP_HOST'];			// Domain name for refer checking & notifications
	$mode		= (isset($mode)) ? $mode : 'javascript';	// Content output type
	$noreply_email	= 'noreply@example.com';			// E-mail used when no e-mail is given
	$user_reply	= 'no';						// Whether given e-mails are sent as reply-to address to users

	// Change to root directory
	chdir(dirname(__FILE__) . '/../');

	// Timezone
	date_default_timezone_set('America/Los_Angeles');

?>
