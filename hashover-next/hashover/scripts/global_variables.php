<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	I, Jacob Barkdull, hereby release this work into the public domain. 
	//	This applies worldwide. If this is not legally possible, I grant any 
	//	entity the right to use this work for any purpose, without any 
	//	conditions, unless such conditions are required by law.


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	$top_likes	= array();	// For sorting top comments
	$subfile_count	= array();	// Individual comment thread count
	$cmt_count	= '1';		// Comment count excluding replies
	$total_count	= '1';		// Comment count including replies
	$show_cmt	= '';		// Will contain all comments
	$deleted_files	= array();	// Deleted files

	// Characters to be removed from name, email, and website fields
	$search = array('<', '>', "\n", "\r", "\t", '&nbsp;', '&lt;', '&gt;', '"', "'", '\\');
	$replace = array('', '', '', '', '', '', '', '', '&quot;', '&#39;', '');

?>
