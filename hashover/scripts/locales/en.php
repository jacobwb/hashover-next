<?php

	// Copyright (C) 2015 Jacob Barkdull
	//
	//	I, Jacob Barkdull, hereby release this work into the public domain. 
	//	This applies worldwide. If this is not legally possible, I grant any 
	//	entity the right to use this work for any purpose, without any 
	//	conditions, unless such conditions are required by law.


	// Display source code
	if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
		if (isset ($_GET['source'])) {
			header ('Content-type: text/plain; charset=UTF-8');
			exit (file_get_contents (basename (__FILE__)));
		}
	}

	// English text for forms, buttons, links, and tooltips
	$locale = array (
		'comment_form'		=> 'Type Comment Here (other fields optional)',	// "Comment" field's default text
		'reply_form'		=> 'Type Reply Here (other fields optional)',	// "Reply" field's default text
		'form_tip'		=> 'Accepted HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLs automagically become links, and [img]URL here[/img] will display an external image.',
		'post_button'		=> 'Post Comment',				// "Post Comment" button's default text
		'login'			=> 'Login',					// "Login" button
		'login_tip'		=> 'Login (optional)',				// "Login" button tooltip
		'logout'		=> 'Logout',					// "Logout" button
		'pending_note'		=> 'This comment is pending approval.',		// Notice for pending comments
		'deleted_note'		=> 'This comment has been deleted.',		// Notice for deleted comments
		'comment_pending'	=> 'Pending',					// Name of pending comments
		'comment_deleted'	=> 'Comment Deleted!',				// Name of deleted comments
		'options'		=> 'Options',					// "Options" button text
		'cancel'		=> 'Cancel',					// "Cancel" button text
		'reply_to_comment'	=> 'Reply To Comment',
		'edit_your_comment'	=> 'Edit Your Comment',
		'name'			=> 'Name',
		'name_tip'		=> 'Name (optional)',
		'password'		=> 'Password',
		'password_tip'		=> 'Password (optional, allows you to edit or delete this comment)',
		'confirm_password'	=> 'Confirm Password',
		'email'			=> 'E-mail Address',
		'email_tip'		=> 'E-mail Address (optional, for e-mail notifications)',
		'website'		=> 'Website',
		'website_tip'		=> 'Website (optional)',
		'logged_in'		=> 'You have been successfully logged in!',
		'logged_out'		=> 'You have been successfully logged out!',
		'comment_needed'	=> 'You failed to enter a proper comment. Use the form below.',
		'reply_needed'		=> 'You failed to enter a proper reply. Use the form below.',
		'post_fail'		=> 'Failure! You lack sufficient permission.',
		'post_reply'		=> 'Post Reply',
		'delete'		=> 'Delete',
		'subscribe'		=> 'Notify me of replies',
		'subscribe_tip'		=> 'Subscribe to e-mail notifications',
		'edit_comment'		=> 'Edit Comment',
		'save_edit'		=> 'Save Edit',
		'no_email_warning'	=> 'You will not receive notification of replies to your comment without supplying an e-mail.',
		'invalid_email'		=> 'The e-mail address you entered is invalid.',
		'delete_comment'	=> 'Are you sure you want to delete this comment?',
		'post_comment_on'	=> array ('Post a Comment', ' on "_TITLE_"'),
		'popular_comments'	=> array ('Most Popular Comment', 'Most Popular Comments'),
		'showing_comments'	=> array ('Showing _NUM_ Comment', 'Showing _NUM_ Comments'),
		'count_link'		=> array ('_NUM_ Comment', '_NUM_ Comments'),
		'count_replies'		=> array ('_NUM_ counting reply', '_NUM_ counting replies'),
		'sort'			=> 'Sort',
		'sort_ascend'		=> 'In Order',
		'sort_descend'		=> 'In Reverse Order',
		'sort_byname'		=> 'By Commenter',
		'sort_bydate'		=> 'Newest First',
		'sort_bylikes'		=> 'By Popularity',
		'threaded'		=> 'Threaded',
		'thread'		=> 'Top of Thread',
		'thread_tip'		=> 'Jump to top of thread',
		'replies'		=> 'Replies',
		'edit'			=> 'Edit',
		'reply'			=> 'Reply',
		'like'			=> array ('Like', 'Likes'),
		'liked'			=> 'Liked',
		'unlike'		=> 'Unlike',
		'like_comment'		=> '\'Like\' This Comment',
		'liked_comment'		=> 'Unlike This Comment',
		'dislike'		=> array ('Dislike', 'Dislikes'),
		'disliked'		=> 'Disliked',
		'dislike_comment'	=> '\'Dislike\' This Comment',
		'disliked_comment'	=> 'You \'Disliked\' This Comment',
		'commenter_tip'		=> 'You will not be notified via e-mail',
		'subscribed_tip'	=> 'will be notified via e-mail',
		'unsubscribed_tip'	=> 'is not subscribed to e-mail notifications',
		'first_comment'		=> 'Be the first to comment!',
		'show_other_comments'	=> array ('Show _NUM_ Other Comment', 'Show _NUM_ Other Comments'),
		'show_number_comments'	=> array ('Show _NUM_ Comment', 'Show _NUM_ Comments'),
		'date_years'		=> array ('_NUM_ year ago', '_NUM_ years ago'),
		'date_months'		=> array ('_NUM_ month ago', '_NUM_ months ago'),
		'date_days'		=> array ('_NUM_ day ago', '_NUM_ days ago'),
		'date_today'		=> '_TIME_ today'
	);

?>
