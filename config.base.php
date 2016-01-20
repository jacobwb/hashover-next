<?php
// Copyright (C) 2010-2015 Jacob Barkdull
// Copyright (C) 2016 Stéphane Mourey
// This file is part of HashOver.
//
// We, Jacob Barkdull and Stéphane Mourey, hereby release this work into
// the public domain.
// This applies worldwide. If this is not legally possible, I grant any
// entity the right to use this work for any purpose, without any
// conditions, unless such conditions are required by law.

/**********************************************************************/
/*
 *                       IMPORTANT NOTICE                            *
 *                                                                   *
 * ***************************************************************** *
 *                                                                   *
 * DO NOT MODIFY THIS FILE !                                         *
 *                                                                   *
 * Instead, copy it, or, better, just the lines you need to change   *
 * in "config.php" in the same directory. The values in "config.php" *
 * will replace the previous ones set in this file                   *
 *                                                                   *
/**********************************************************************/

// It is also important to choose UNIQUE values for the encryption key,
// admin name, and admin password, as not doing so puts HashOver at
// risk of being hijacked. Allowing someone to delete comments and/or
// edit existing comments to post spam, impersonate you or your
// visitors in order to push some sort of agenda/propaganda, to defame
// you or your visitors, or to imply endorsement of some product(s),
// service(s), and/or political ideology.

// Required setup
$config['notificationEmail'] = 'example@example.com';	// E-mail for notification of new comments
$config['encryptionKey'] = '8CharKey';	// Unique encryption key
$config['adminName'] = 'admin';	// Login name to gain admin rights (case-sensitive)
$config['adminPassword'] = 'passw';	// Login password to gain admin rights (case-sensitive)

// Primary settings
$config['language'] = 'en';	// Language used for forms, buttons, links, and tooltips
$config['theme'] = 'default';	// Comment Cascading Style Sheet (CSS)
$config['timezone'] = 'America/Los_Angeles';	// Timezone
$config['usesModeration'] = false;	// Whether comments must be approved before they appear to other visitors
$config['dataFormat'] = 'xml';	// Format comments will be stored in; options: xml, json, sql
$config['defaultName'] = 'Anonymous';	// Default name to use when one isn't given
$config['allowsImages'] = true;	// Whether external image URLs wrapped in [img] tags are embedded
$config['allowsLogin'] = true;	// Whether users can login and logout (when false form cookies are still set)
$config['allowsDislikes'] = false;	// Whether a "Dislike" link is display; allowing Reddit-style voting
$config['usesAJAX'] = true;	// Whether AJAX is used for posting, editing, and loading comments
$config['collapsesComments'] = true;	// Whether to hide comments and display a link to show them
$config['collapseLimit'] = 3;	// Number of comments that aren't hidden
$config['replyMode'] = 'thread';	// Whether to display replies as a 'thread' or as a 'stream'
$config['streamDepth'] = 3;	// In stream mode, the number of reply indentions to allow before the thread flattens
$config['popularityThreshold'] = 5;	// Minimum likes a comment needs to be popular
$config['popularityLimit'] = 2;	// Number of comments allowed to become popular

// Field options, use true/false to enable/disable a field;
// use 'required' to require a field be properly filled
$config['fieldOptions']['name'] = true;
$config['fieldOptions']['password'] = true;
$config['fieldOptions']['email'] = true;
$config['fieldOptions']['website'] = true;

// Behavior settings
$config['displaysTitle'] = true;	// Whether page title is shown or not
$config['formPosition'] = 'top';	// Position for primary form; options: 'top' or 'bottom'
$config['usesAutoLogin'] = true;	// Whether a user's first comment automatically logs them in
$config['showsReplyCount'] = true;	// Whether to show reply count separately from total
$config['uses12HourTime'] = true;	// Whether to use 12 hour time format, otherwise use 24 hour format
$config['usesShortDates'] = true;	// Whether comment dates are shortened
$config['iconMode'] = 'image';	// How to display avatar icons (either 'image', 'count' or 'none')
$config['iconSize'] = '45';	// Size of Gravatar icons in pixels
$config['imageFormat'] = 'png';	// Format for icons and other images (use 'svg' for HDPI)
$config['usesLabels'] = false;	// Whether to display labels above inputs
$config['usesCancelButtons'] = true;	// Whether forms have "Cancel" buttons
$config['appendsCSS'] = true;	// Whether to automatically add a CSS <link> element to the page <head>
$config['displaysRSSLink'] = true;	// Whether a comment RSS feed link is displayed

// Technical settings
$config['JSONSettingsFile'] = 'settings.json';	// Optional JSON settings file (overrides defaults)
$config['loginMethod'] = 'defaultLogin';	// Login method class for handling user login information
$config['secureCookies'] = false;	// Whether cookies set over secure HTTPS will only be transmitted over HTTPS
$config['storesIPAddress'] = false;	// Whether to store users' IP addresses
$config['allowsUserReplies'] = false;	// Whether given e-mails are sent as reply-to address to users
$config['noreplyEmail'] = 'noreply@example.com';	// E-mail used when no e-mail is given
$config['spamDatabase'] = 'remote';	// Whether to use a remote or local spam database
$config['spamCheckModes'] = 'php';	// Perform IP spam check in 'javascript' or 'php' mode, or 'both'
$config['gravatarDefault'] = 'custom';	// Gravatar theme to use ('custom', 'identicon', 'monsterid', 'wavatar', or 'retro')
$config['gravatarForce'] = false;	// Whether to force the themed Gravatar images instead of an avatar image
$config['minifiesJavaScript'] = false;	// Whether JavaScript output should be minified
$config['minifyLevel'] = 4;	// How much to minify JavaScript code, options: 1, 2, 3, 4
$config['enablesAPI'] = true;	// API: true = fully-enabled, false = fully disabled, or array of modes
$config['latestMax'] = 10;	// Maximum number of comments to save as latest comments
$config['latestTrimWidth'] = 100;	// Number of characters to trim latest comments to, 0 for no trim
$config['userDeletionsUnlink'] = false;	// Whether user deleted files are actually unlinked from the filesystem

// Allowed image types when embedded images are allowed
$config['imageTypes'] = array('jpeg','jpg','png','gif');

// General database options
$config['databaseType'] = 'sqlite';	// Type of database, sqlite or mysql
$config['databaseName'] = 'hashover-pages';	// Database name

// SQL database options
$config['databaseHost'] = 'localhost';	// Database host name
$config['databaseUser'] = 'root';	// Database login user
$config['databasePassword'] = 'password';	// Database login password
$config['databaseCharset'] = 'utf8';	// Database character set

// Automated settings
$config['isMobile'] = false;
