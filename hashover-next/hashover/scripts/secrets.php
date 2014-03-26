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
	// IMPORTANT:
	//
	//	To maintain proper functionality when downloading or otherwise 
	//	upgrading to a new version of HashOver, it is important that you 
	//	preserve this file, unless directed otherwise.
	//
	//	It is also important to choose UNIQUE values for the encryption key, 
	//	admin nickname, and admin password, or else you're at risk of 
	//	someone hijacking the comment system to delete comments, edit 
	//	existing comments to post spam, and/or impersonate you or your 
	//	visitors in order to push some sort of agenda/propaganda, to defame 
	//	you or your visitors, or to imply endorsement of some product(s), 
	//	service(s), and/or political ideology.


	$encryption_key		= '8CharKey';			// Unique 8 to 32 character encryption key
	$notification_email	= 'example@example.com';	// E-mail for notification of new comments
	$admin_nickname		= 'admin';			// Nickname with admin rights (must be title-cased)
	$admin_password		= 'passwd';			// Password to gain admin rights

	// Display source code without giving away encryption key
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header("Content-Type: text/plain");
		echo '<?php' . PHP_EOL . PHP_EOL;
		echo "\t" . '// Copyright (C) 2014 Jacob Barkdull' . PHP_EOL;
		echo "\t" . '//' . PHP_EOL;
		echo "\t" . '//' . "\t" . 'I, Jacob Barkdull, hereby release this work into the public domain.' . PHP_EOL;
		echo "\t" . '//' . "\t" . 'This applies worldwide. If this is not legally possible, I grant any' . PHP_EOL;
		echo "\t" . '//' . "\t" . 'entity the right to use this work for any purpose, without any' . PHP_EOL;
		echo "\t" . '//' . "\t" . 'conditions, unless such conditions are required by law.' . PHP_EOL . PHP_EOL . PHP_EOL;

		echo "\t" . '$encryption_key' . "\t\t" . '= \'8CharKey\';' . "\t\t\t" . '// Unique 8 to 32 character encryption key' . PHP_EOL;
		echo "\t" . '$notification_email' . "\t" . '= \'example@example.com\';' . "\t" . '// E-mail for notification of new comments' . PHP_EOL;
		echo "\t" . '$admin_nickname' . "\t\t" . '= \'admin\';' . "\t\t\t" . '// Nickname with admin rights (must be title-cased)' . PHP_EOL;
		echo "\t" . '$admin_password' . "\t\t" . '= \'passwd\';' . "\t\t\t" . '// Password to gain admin rights' . PHP_EOL . PHP_EOL;
		echo '?>';
	}

?>
