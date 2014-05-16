<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	This program is free software: you can redistribute it and/or modify
	//	it under the terms of the GNU Affero General Public License as
	//	published by the Free Software Foundation, either version 3 of the
	//	License, or (at your option) any later version.
	//
	//	This program is distributed in the hope that it will be useful,
	//	but WITHOUT ANY WARRANTY; without even the implied warranty of
	//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//	GNU Affero General Public License for more details.
	//
	//	You should have received a copy of the GNU Affero General Public License
	//	along with this program.  If not, see <http://www.gnu.org/licenses/>.


	// Display source code
	if (isset($_GET['source']) and basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		header('Content-type: text/plain; charset=UTF-8');
		exit(file_get_contents(basename(__FILE__)));
	}

	if ($this->setting['page_title'] == 'yes') {
		$js_title = '<script type="text/javascript">if (document.title != "") { document.write(" on \"" + document.title + "\"") };</script>';
		$js_title = (isset($_GET['pagetitle'])) ? ' on "' . $_GET['pagetitle'] . '"' : $js_title;
	} else {
		$js_title = '';
	}

	class PhpMode extends HashOver {
		public $notifications, $template_replace, $form_avatar;

		// Default form settings
		public $nickname_on	= true;
		public $password_on	= true;
		public $email_on	= true;
		public $website_on	= true;

		public function __construct() {
			parent::__construct();

			// Avatar icon for edit and reply forms
			if (isset($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
				$this->form_avatar = '<img width="32" height="32" src="' . $this->setting['root_dir'] . 'scripts/avatars.php?format=' . $this->setting['image_format'] . '&size=' . $this->setting['icon_size'] . '&username=' . $_COOKIE['name'] . '&email=' . md5(strtolower(trim($_COOKIE['email']))) . '">';
			} else {
				$this->form_avatar = '<img width="32" height="32" src="' . $this->setting['root_dir'] . 'scripts/avatars.php?format=' . $this->setting['image_format'] . '&size=' . $this->setting['icon_size'] . ((isset($_COOKIE['email'])) ? '&email=' . md5(strtolower(trim($_COOKIE['email']))) : '') . '">';
			}
		}

		public function parse_template($comments, $count) {
			for ($array = 0; $array != count($comments) and $array != $count; $array++) {
				if (!isset($comments["$array"]['deletion_notice'])) {
					$this->template_replace = array(
						'indent' => $comments["$array"]['indent'],
						'cmtclass' => (isset($comments["$array"]['cmtclass'])) ? ' ' . $comments["$array"]['cmtclass'] : '',
						'permalink' => $comments["$array"]['permalink'],
						'avatar' => $comments["$array"]['avatar'],
						'name' => $comments["$array"]['name'],
						'thread' => (isset($comments["$array"]['thread'])) ? $comments["$array"]['thread'] : '',
						'comment' => $comments["$array"]['comment'],
						'action' => $_SERVER['REQUEST_URI'],
						'likes' => (isset($comments["$array"]['likes'])) ? $comments["$array"]['likes'] : ''
					);
	
					if (!in_array('hashover_reply=' . $this->template_replace['permalink'], $this->ref_queries) and !in_array('hashover_edit=' . $this->template_replace['permalink'], $this->ref_queries)) {
						$this->template_replace['date'] = $comments["$array"]['date'];
						$this->template_replace['like_link'] = (isset($comments["$array"]['like_link'])) ? $comments["$array"]['like_link'] : '';
						$this->template_replace['edit_link'] = (isset($comments["$array"]['edit_link'])) ? $comments["$array"]['edit_link'] : '';
						$this->template_replace['reply_link'] = $comments["$array"]['reply_link'];
					} else {
						$this->template_replace['hashover_footer_style'] = ' style="display: none;"';
					}
	
					// Load HTML template
					$load_html_template = str_replace(PHP_EOL, PHP_EOL . "\t", trim(file_get_contents('.' . $this->setting['root_dir'] . 'html-templates/' . $this->setting['html_template'] . '.html'), "\n"));
					$this->notifications = $comments["$array"]['notifications'];
	
					// Comment information into template; add reply or edit form
					$classThis = $this;

					echo "\t" . preg_replace_callback('/\\\' \+ (.*?) \+ \\\'/', function($arr) use($classThis) {
						if ($arr[1] != 'form') {
							return (isset($classThis->template_replace["$arr[1]"])) ? $classThis->template_replace["$arr[1]"] : '';
						} else {
							$return_form = '';
	
							if (in_array('hashover_reply=' . $classThis->template_replace['permalink'], $classThis->ref_queries)) {
								$return_form .= PHP_EOL . '<a name="' . $classThis->template_replace['permalink'] . '-form"></a>' . PHP_EOL;
								$return_form .= '<b class="hashover-title">' . $classThis->text['reply_to_cmt'] . '</b>' . PHP_EOL;
								$return_form .= '<span class="hashover-form-buttons">' . PHP_EOL;
								$return_form .= "\t" . '<a href="' . $classThis->parse_url['path'] . ((!empty($classThis->parse_url['query'])) ? '?' . $classThis->parse_url['query'] : '') . '#' . $classThis->template_replace['permalink'] . '">' . $classThis->text['cancel'] . '</a>' . PHP_EOL;
								$return_form .= '</span>' . PHP_EOL;
								$return_form .= '<div class="hashover-options open">' . PHP_EOL;
								$return_form .= "\t" . '<div class="hashover-inputs">' . PHP_EOL;
	
								if ($classThis->setting['icons'] == 'yes' and $classThis->nickname_on) {
									$return_form .= "\t\t" . '<div class="hashover-avatar-image">' . PHP_EOL . "\t\t" . $classThis->form_avatar . PHP_EOL . '</div>' . PHP_EOL;
								}
	
								if ($classThis->nickname_on) {
									$return_form .= "\t\t" . '<div class="hashover-name-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="text" name="name" title="' . $classThis->text['nickname_tip'] . '" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" maxlength="30" placeholder="' . $classThis->text['nickname'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
								}
	
								if ($classThis->password_on) {
									$return_form .= "\t\t" . '<div class="hashover-password-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="password" name="password" title="' . $classThis->text['password_tip'] . '" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $classThis->text['password'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
								}
	
								if ($classThis->is_mobile) {
									$return_form .= "\t" . '</div>' . PHP_EOL . "\t" . '<div class="hashover-inputs">' . PHP_EOL;
								}
	
								if ($classThis->email_on) {
									$return_form .= "\t\t" . '<div class="hashover-email-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="text" name="email" title="' . $classThis->text['email'] . '" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $classThis->text['email'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
								}
	
								if ($classThis->website_on) {
									$return_form .= "\t\t" . '<div class="hashover-website-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="text" name="website" title="' . $classThis->text['website'] . '" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $classThis->text['website'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
								}
	
								$return_form .= "\t" . '</div>' . PHP_EOL . '</div>' . PHP_EOL;
								$return_form .= "\t" . '<textarea rows="6" cols="62" name="comment" title="' . $classThis->text['cmt_tip'] . '" placeholder="' . $classThis->text['comment_form'] . '"></textarea>' . PHP_EOL;
								$return_form .= (isset($_GET['canon_url']) or isset($classThis->canon_url)) ? "\t" . '<input type="hidden" name="canon_url" value="' . $classThis->parse_url['path'] . ((!empty($classThis->parse_url['query'])) ? '?' . $classThis->parse_url['query'] : '') . '">' . PHP_EOL : '';
								$return_form .= "\t" . '<input type="hidden" name="reply_to" value="' . str_replace(array('c', 'r', '_pop'), array('', '-', ''), $classThis->template_replace['permalink']) . '">' . PHP_EOL;
								$return_form .= "\t" . '<input class="hashover-submit" type="submit" value="' . $classThis->text['post_button'] . '">' . PHP_EOL;
							} else {
								if (in_array('hashover_edit=' . $classThis->template_replace['permalink'], $classThis->ref_queries)) {
									$return_form .= PHP_EOL . '<a name="' . $classThis->template_replace['permalink'] . '-form"></a>' . PHP_EOL;
									$return_form .= '<b class="hashover-title">' . $classThis->text['edit_cmt'] . '</b>' . PHP_EOL;
									$return_form .= '<span class="hashover-form-buttons">' . PHP_EOL;
									$return_form .= "\t" . '<input type="submit" name="edit" value="." style="display: none;">';
									$return_form .= "\t" . '<input type="submit" name="delete" class="hashover-delete" value="' . $classThis->text['delete'] . '">' . PHP_EOL;
									$return_form .= "\t" . '<label for="notify" title="' . $classThis->text['subscribe_tip'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '<input type="checkbox"' . (($classThis->notifications != 'no') ? ' checked="true"' : '') . ' id="notify" name="notify"> ' . $classThis->text['subscribe'] . PHP_EOL;
									$return_form .= "\t" . '</label>' . PHP_EOL;
									$return_form .= "\t" . '<a href="' . $classThis->parse_url['path'] . ((!empty($classThis->parse_url['query'])) ? '?' . $classThis->parse_url['query'] : '') . '#' . $classThis->template_replace['permalink'] . '">' . $classThis->text['cancel'] . '</a>' . PHP_EOL;
									$return_form .= '</span>' . PHP_EOL;
									$return_form .= '<div class="hashover-options open">' . PHP_EOL;
									$return_form .= "\t" . '<div class="hashover-inputs">' . PHP_EOL;
	
									if ($classThis->setting['icons'] == 'yes') {
										$return_form .= "\t\t" . '<div class="hashover-avatar-image">' . PHP_EOL . "\t\t" . $classThis->form_avatar . PHP_EOL . '</div>' . PHP_EOL;
									}
	
									$return_form .= "\t\t" . '<div class="hashover-name-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="text" name="name" title="' . $classThis->text['nickname_tip'] . '" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" maxlength="30" placeholder="' . $classThis->text['nickname'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
	
									$return_form .= "\t\t" . '<div class="hashover-password-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="password" name="password" title="' . $classThis->text['password_tip'] . '" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $classThis->text['password'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
	
									if ($classThis->is_mobile) {
										$return_form .= "\t" . '</div>' . PHP_EOL . "\t" . '<div class="hashover-inputs">' . PHP_EOL;
									}
	
									$return_form .= "\t\t" . '<div class="hashover-email-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="text" name="email" title="' . $classThis->text['email'] . '" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $classThis->text['email'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
	
									$return_form .= "\t\t" . '<div class="hashover-website-input">' . PHP_EOL;
									$return_form .= "\t\t\t" . '<input type="text" name="website" title="' . $classThis->text['website'] . '" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $classThis->text['website'] . '">' . PHP_EOL;
									$return_form .= "\t\t" . '</div>' . PHP_EOL;
	
									// Clean HTML in comment
									$classThis->template_replace['comment'] = preg_replace('/<br>/i', '&#10;', $classThis->template_replace['comment']);
									$classThis->template_replace['comment'] = preg_replace('/<\/?a(\s+.*?>|>)/i', '', $classThis->template_replace['comment']);
									$classThis->template_replace['comment'] = preg_replace('/<img.*?title="(.*?)".*?>/i', '[img]\\1[/img]', $classThis->template_replace['comment']);
									$classThis->template_replace['comment'] = preg_replace('/^\s+|\s+$/i', '', $classThis->template_replace['comment']);
									$classThis->template_replace['comment'] = preg_replace('/<code style="white-space: pre;">/i', '<code>', $classThis->template_replace['comment']);
	
									$return_form .= "\t" . '</div>' . PHP_EOL . '</div>' . PHP_EOL;
									$return_form .= "\t" . '<textarea rows="10" cols="62" name="comment" title="' . $classThis->text['cmt_tip'] . '" placeholder="' . $classThis->text['reply_form'] . '">' . $classThis->template_replace['comment'] . '</textarea>' . PHP_EOL;
									$return_form .= (isset($_GET['canon_url']) or isset($classThis->canon_url)) ? "\t" . '<input type="hidden" name="canon_url" value="' . $classThis->parse_url['path'] . ((!empty($classThis->parse_url['query'])) ? '?' . $classThis->parse_url['query'] : '') . '">' . PHP_EOL : '';
									$return_form .= "\t" . '<input type="hidden" name="cmtfile" value="' . str_replace(array('c', 'r', '_pop'), array('', '-', ''), $classThis->template_replace['permalink']) . '">' . PHP_EOL;
									$return_form .= "\t" . '<input class="hashover-submit" type="submit" name="edit" value="' . $classThis->text['save_edit'] . '">' . PHP_EOL;
								}
							}
	
							return str_replace(PHP_EOL, PHP_EOL . "\t\t\t\t\t", $return_form) . PHP_EOL . "\t\t\t\t";
						}
					}, $load_html_template) . PHP_EOL;
				} else {
					echo '<a name="' . $comments["$array"]['permalink'] . '"></a>' . PHP_EOL;
					echo '<div style="margin: ' . $comments["$array"]['indent'] . ';" class="' . $comments["$array"]['cmtclass'] . '">' . PHP_EOL;
					echo $comments["$array"]['deletion_notice'] . PHP_EOL;
					echo '</div>' . PHP_EOL;
				}
			}
		}
	}

	$php_mode = new PhpMode();

?>

<div id="hashover" class="<?php echo $this->setting['image_format']; ?>">
	<a name="comments"></a>
	<b class="hashover-post-comment"><?php echo $this->text['post_cmt'] . $js_title; ?>:</b>
<?php
	if (!empty($_COOKIE['message'])) {
		echo "\t" . '<b id="hashover-message" class="hashover-title">' . $_COOKIE['message'] . '</b>' . PHP_EOL;
	}
?>

	<form id="hashover_form" name="hashover_form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<span class="hashover-avatar"><?php echo ($this->setting['icons'] == 'yes') ? $php_mode->form_avatar : '<span title="Permalink">#' . $this->cmt_count . '</span>'; ?></span>

		<div class="hashover-balloon">
			<div class="hashover-inputs">
<?php

	// Display name input tag if told to
	if ($php_mode->nickname_on) {
		echo "\t\t\t\t" . '<div class="hashover-name-input">' . PHP_EOL;
		echo "\t\t\t\t\t" . '<input type="text" name="name" title="' . $this->text['nickname_tip'] . '" maxlength="30" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" placeholder="' . $this->text['nickname'] . '">' . PHP_EOL;
		echo "\t\t\t\t" . '</div>' . PHP_EOL;
	}

	// Display password input tag if told to
	if ($php_mode->password_on) {
		echo "\t\t\t\t" . '<div class="hashover-password-input">' . PHP_EOL;
		echo "\t\t\t\t\t" . '<input type="password" name="password" title="' . $this->text['password_tip'] . '" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $this->text['password'] . '">' . PHP_EOL;
		echo "\t\t\t\t" . '</div>' . PHP_EOL;
	}

	// Add second table row on mobile devices
	if ($this->is_mobile) {
		if ($php_mode->nickname_on and $php_mode->password_on) {
			echo "\t\t\t\t" . '<div class="hashover-login-input">' . PHP_EOL;
			echo "\t\t\t\t\t" . '<input type="submit" name="login" title="Login (optional)" value="">' . PHP_EOL;
			echo "\t\t\t\t" . '</div>' . PHP_EOL;
		}

		echo "\t\t\t\t" . '</div>' . PHP_EOL;
		echo "\t\t\t\t" . '<div class="hashover-inputs">' . PHP_EOL;
	}

	// Display email input tag if told to
	if ($php_mode->email_on) {
		echo "\t\t\t\t" . '<div class="hashover-email-input">' . PHP_EOL;
		echo "\t\t\t\t\t" . '<input type="text" name="email" title="' . $this->text['email'] . '" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $this->text['email'] . '">' . PHP_EOL;
		echo "\t\t\t\t" . '</div>' . PHP_EOL;
	}

	// Display website input tag if told to
	if ($php_mode->website_on) {
		echo "\t\t\t\t" . '<div class="hashover-website-input">' . PHP_EOL;
		echo "\t\t\t\t\t" . '<input type="text" name="website" title="' . $this->text['website'] . '" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $this->text['website'] . '">' . PHP_EOL;
		echo "\t\t\t\t" . '</div>' . PHP_EOL;
	}

	if ($this->is_mobile == false) {
		if ($php_mode->nickname_on and $php_mode->password_on) {
			echo "\t\t\t\t" . '<div class="hashover-login-input">' . PHP_EOL;
			echo "\t\t\t\t\t" . '<input type="submit" name="login" title="Login (optional)" value="">' . PHP_EOL;
			echo "\t\t\t\t" . '</div>' . PHP_EOL;
		}
	}

	echo "\t\t\t" . '</div>' . PHP_EOL . PHP_EOL;
	echo "\t\t\t" . '<div id="requiredFields" style="display: none;">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="text" name="summary" value="" placeholder="Summary">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="hidden" name="middlename" value="" placeholder="Middle Name">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="text" name="lastname" value="" placeholder="Last Name">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="text" name="address" value="" placeholder="Address">' . PHP_EOL;
	echo "\t\t\t\t" . '<input type="hidden" name="zip" value="" placeholder="Last Name">' . PHP_EOL;
	echo "\t\t\t" . '</div>' . PHP_EOL . PHP_EOL;

	$replyborder = (isset($_COOKIE['success']) and $_COOKIE['success'] == "no") ? ' style="border: 2px solid #FF0000 !important; -moz-border-radius: 5px 5px 0px 0px; border-radius: 5px 5px 0px 0px;"' : '';
	echo "\t\t\t" . '<textarea rows="' . $this->setting['rows'] . '" cols="63" name="comment"' . $replyborder . ' title="' . $this->text['cmt_tip'] . '" placeholder="' . $this->text['comment_form'] . '"></textarea>' . PHP_EOL;
	if (isset($_GET['canon_url']) or isset($this->canon_url)) echo "\t\t\t" . '<input type="hidden" name="canon_url" value="' . $this->page_url . '">' . PHP_EOL;
	if (isset($_COOKIE['replied'])) echo "\t\t\t" . '<input type="hidden" name="reply_to" value="' . $_COOKIE['replied'] . '">' . PHP_EOL;
	echo "\t\t\t" . '<input class="hashover-submit" type="submit" value="' . $this->text['post_button'] . '">' . PHP_EOL;
	echo "\t\t" . '</div>' . PHP_EOL;
	echo "\t" . '</form>' . PHP_EOL . PHP_EOL;

	// Display most popular comments
	if (!empty($this->top_likes)) {
		echo "\t" . '<b class="hashover-title">' . $this->text['popular_cmts'] . ' Comment' . ((count($this->top_likes) != '1') ? 's' : '') . ':</b>' . PHP_EOL;
		echo "\t" . '<div id="hashover-top-comments">' . PHP_EOL;
		krsort($this->top_likes); // Sort popular comments

		for ($p = 1; $p <= count($this->top_likes) and $p <= $this->setting['top_cmts']; $p++) {
			static $likes_array = array();

			if (!empty($this->top_likes)) {
				$likes_array[] = $this->parse(array_shift($this->top_likes), true);
			}
		}

		$php_mode->parse_template(array_values($likes_array), $this->setting['top_cmts']);
		echo "\t" . '</div>' . PHP_EOL;
	}

	// Display comment count
	echo "\t" . '<b class="hashover-count">' . $this->text['showing_cmts'] . ' ' . $script = ($this->cmt_count == "1") ? '0 Comments:</b>' . PHP_EOL : $this->show_count . ':</b>' . PHP_EOL;

	// Display comments, if there are no comments display a note
	if (!empty($this->hashover)) {
		$php_mode->parse_template($this->hashover, $this->total_count);
	} else {
		echo "\t" . '<div style="margin: 16px 0px 12px 0px;" class="hashover-comment hashover-first">' . PHP_EOL;
		echo "\t\t" . '<span class="hashover-avatar"><img width="' . $this->setting['icon_size'] . '" height="' . $this->setting['icon_size'] . '" src="/hashover/images/' . $this->setting['image_format'] . 's/first-comment.' . $this->setting['image_format'] . '"></span>' . PHP_EOL;
		echo "\t\t" . '<div style="height: ' . $this->setting['icon_size'] . 'px;" class="hashover-balloon">' . PHP_EOL;
		echo "\t\t\t" . '<b class="hashover-title">Be the first to comment!</b>' . PHP_EOL;
		echo "\t\t" . '</div>' . PHP_EOL;
		echo "\t" . '</div>' . PHP_EOL;
	}

?>

	<div id="hashover-end-links">
		HashOver Comments &middot;
<?php if (!empty($this->hashover)) echo "\t\t" . '<a href="/hashover.php?rss=' . $this->page_url . '" target="_blank">RSS Feed</a> &middot;' . PHP_EOL; ?>
		<a href="/hashover.php?source" rel="hashover-source" target="_blank">Source Code</a> &middot;
		<a href="http://tildehash.com/hashover/changelog.txt" target="_blank">ChangeLog</a> &middot;
		<a href="http://tildehash.com/hashover/archives/" target="_blank">Archives</a>
	</div>
</div>

<script type="text/javascript">
// Copyright (C) 2014 Jacob Barkdull
//
//	This program is free software: you can redistribute it and/or modify
//	it under the terms of the GNU Affero General Public License as
//	published by the Free Software Foundation, either version 3 of the
//	License, or (at your option) any later version.
//
//	This program is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU Affero General Public License for more details.
//
//	You should have received a copy of the GNU Affero General Public License
//	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
//--------------------
//
// Source Code and Installation Instructions:
//	http://<?php echo $this->setting['domain'] . '/hashover.php?source' . PHP_EOL; ?>


// Function to like a comment
function hashover_like(c, f) {
	// Load "like.php"
	var like = new XMLHttpRequest();
	like.open('GET', '<?php echo $this->setting['root_dir'] . 'scripts/like.php?like=' . $this->ref_path; ?>/' + f);
	like.send();

	// Get number of likes
	if (document.getElementById('hashover-likes-' + c).innerHTML != '') {
		var likes = parseInt(document.getElementById('hashover-likes-' + c).innerHTML.replace(/[^0-9]/g, ''));
	} else {
		var likes = parseInt(0);
	}

	// Change "Like" button title and class; Increase likes
	if (document.getElementById('hashover-like-' + c).className == 'hashover-like') {
		document.getElementById('hashover-like-' + c).className = 'hashover-liked';
		document.getElementById('hashover-like-' + c).title = '<?php echo addcslashes($this->text['liked_cmt'], "'"); ?>';
		document.getElementById('hashover-like-' + c).innerHTML = '<?php echo $this->text['liked']; ?>';
		likes++;
	} else {
		document.getElementById('hashover-like-' + c).className = 'hashover-like';
		document.getElementById('hashover-like-' + c).title = '<?php echo addcslashes($this->text['like_cmt'], "'"); ?>';
		document.getElementById('hashover-like-' + c).innerHTML = '<?php echo $this->text['like']; ?>';
		likes--;
	}

	// Change number of likes
	var like_count = (likes != 1) ? likes + ' Likes' : likes + ' Like';
	document.getElementById('hashover-likes-' + c).innerHTML = (likes > 0) ? '<b>' + like_count + '</b>' : '';
}
</script>
