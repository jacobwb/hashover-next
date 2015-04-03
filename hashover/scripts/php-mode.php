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
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	$php_title = $this->setup->text['post_cmt_on'][0];

	if ($this->setup->display_title == 'yes') {
		$php_title .= str_replace('_TITLE_', $this->setup->page_title, $this->setup->text['post_cmt_on'][1]);
	}

	class PhpMode
	{
		public $notifications,
		       $template_replace,
		       $form_avatar,
		       $form_first_image,
		       $is_a_reply = false,
		       $is_an_edit = false;

		// Default form settings
		public $name_on		= true,
		       $password_on	= true,
		       $email_on	= true,
		       $website_on	= true;

		public function __construct($read_comments, $setup)
		{
			$this->read_comments = $read_comments;
			$this->setup = $setup;

			// Avatar icon for edit and reply forms
			if (!empty($_COOKIE['name']) and preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $_COOKIE['name'])) {
				$this->form_avatar = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="' . $this->setup->root_dir . '/scripts/avatars.php?format=' . $this->setup->image_format . '&amp;size=' . $this->setup->icon_size . '&amp;username=' . $_COOKIE['name'] . '&amp;email=' . md5(strtolower(trim($_COOKIE['email']))) . '" alt="#' . $this->read_comments->cmt_count . '">';
			} else {
				$this->form_avatar = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="' . $this->setup->root_dir . '/scripts/avatars.php?format=' . $this->setup->image_format . '&amp;size=' . $this->setup->icon_size . ((isset($_COOKIE['email'])) ? '&amp;email=' . md5(strtolower(trim($_COOKIE['email']))) : '') . '" alt="#' . $this->read_comments->cmt_count . '">';
			}

			$this->form_first_image = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="' . $this->setup->root_dir . '/images/' . $this->setup->image_format . 's/first-comment.' . $this->setup->image_format . '" alt="+">';

			// Load HTML template
			$layout_file = file_get_contents('./themes/' . $this->setup->theme . '/layout.html');
			$this->theme_layout = trim($layout_file);
		}

		public function parse_template(array $comment, $forPop = false)
		{
			$this->forPop = $forPop;

			$this->template_replace = array(
				'cmtclass' => '',
				'permalink' => str_replace('_pop', '', $comment['permalink'])
			);

			if ($forPop == false) {
				if (strpos($comment['permalink'], 'r') !== false) {
					$this->template_replace['cmtclass'] .= ' hashover-reply';
				}
			}

			// Check if either a reply or edit is happening
			$this->is_a_reply = in_array('hashover_reply=' . $comment['permalink'], $this->setup->parse_url['query']);
			$this->is_an_edit = in_array('hashover_edit=' . $comment['permalink'], $this->setup->parse_url['query']);

			// Generate necessary indention for HTML output
			$indention = str_repeat("\t", substr_count($comment['permalink'], 'r'));

			if (isset($comment['notice_class'])) {
				$this->template_replace['cmtclass'] .= ' ' . $comment['notice_class'];
			}

			echo "\t", $indention, '<div id="', $comment['permalink'], '" class="hashover-comment', $this->template_replace['cmtclass'], '">', PHP_EOL;

			if ($this->setup->icon_mode != 'none') {
				if ($this->setup->icon_mode == 'image') {
					$php_avatar = '<img width="' . $this->setup->icon_size . '" height="' . $this->setup->icon_size . '" src="' . $comment['avatar'] .  '" alt="#' . $comment['permalink'] .  '">';
				} else {
					$avatar_link_text = explode('r', substr($comment['permalink'], 1));
					$php_avatar = '<a href="#' . $comment['permalink'] . '" title="Permalink">#' . array_pop($avatar_link_text) . '</a>';
				}
			}

			// Setup avatar icon
			if (isset($comment['avatar'])) {
				$this->template_replace['avatar'] = '<span class="hashover-avatar">' . $php_avatar . '</span>';
			}

			if (!isset($comment['notice'])) {
				if (!empty($comment['likes']) and $comment['likes'] > 0) {
					$likes_num = $comment['likes'] . ' ' . $this->setup->text['like'][($comment['likes'] != '1')];
				} else {
					$likes_num = '';
				}

				if (!empty($comment['dislikes']) and $comment['dislikes'] > 0) {
					$dislikes_num = $comment['dislikes'] . ' ' . $this->setup->text['dislike'][($comment['dislikes'] != '1')];
				} else {
					$dislikes_num = '';
				}

				$this->template_replace['name'] = $comment['name'];
				$this->template_replace['thread'] = (strpos($comment['permalink'], 'r') !== false) ? '<a href="#' . preg_replace('/^(.*)r.*$/', '\\1', $comment['permalink']) . '" title="' . $this->setup->text['thread_tip'] . '" class="hashover-thread-link">' . $this->setup->text['thread'] . '</a>' : '';
				$this->template_replace['comment'] = $comment['comment'];
				$this->template_replace['action'] = $_SERVER['PHP_SELF'];
				$this->template_replace['likes'] = '<span id="hashover-likes-' . $comment['permalink'] . '" class="hashover-likes">' . $likes_num . '</span>';
				$this->template_replace['dislikes'] = '<span id="hashover-dislikes-' . $comment['permalink'] . '" class="hashover-dislikes">' . $dislikes_num . '</span>';
				$this->template_replace['date'] = '<a href="#' . $comment['permalink'] .  '" title="Permalink">' . $comment['date'] .  '</a>';
				$this->template_replace['dislike_link'] = (!empty($comment['dislike_link'])) ? $comment['dislike_link'] : '';
				$this->template_replace['like_link'] = (!empty($comment['like_link'])) ? $comment['like_link'] : '';
				$this->template_replace['edit_link'] = (!empty($comment['edit_link'])) ? $comment['edit_link'] : '';
				$this->template_replace['reply_link'] = $comment['reply_link'];

				if ($forPop == false) {
					if ($this->is_an_edit) {
						$this->template_replace['hashover_footer_style'] = ' style="display: none;"';
					}

					if ($this->is_a_reply) {
						$this->template_replace['hashover_reply_form_class'] = ' class="hashover-comment hashover-reply"';
					}
				}

				$name_at = preg_match('/^@.*?$/', $comment['name']) ? '@' : '';
				$name_class = preg_match('/^@.*?$/', $comment['name']) ? ' at' : '';

				if (empty($comment['website'])) {
					if (preg_match('/^@([a-zA-Z0-9_@]{1,29}$)/', $comment['name'])) {
						$comment['name'] = '<a id="hashover-name-' . $comment['permalink'] . '" href="http://twitter.com/' . $comment['name'] .  '" target="_blank">' . $comment['name'] . '</a>';
					}
				} else {
					if (!empty($comment['website'])) {
						$comment['name'] = '<a id="hashover-name-' . $comment['permalink'] .  '" href="' . $comment['website'] . '" target="_blank">' . $comment['name'] .  '</a>';
					} else {
						$comment['name'] = '<span id="hashover-name-' . $comment['permalink'] .  '">' . $comment['name'] .  '</span>';
					}
				}

				$this->template_replace['name'] = '<span class="hashover-name' . $name_class . '">' . $name_at . $comment['name'] . '</span>';
				$this->notifications = $comment['notifications'];

				// Populate template with comment data
				$themed_comment = preg_replace_callback('/\\\' \+ (.*?) \+ \\\'/', 'self::add_forms', $this->theme_layout);

				// Indent lines, removing blank lines
				foreach (explode(PHP_EOL, $themed_comment) as $line) {
					$trimmed_line = trim($line);

					if (!empty($trimmed_line)) {
						echo "\t\t", $indention, $line, PHP_EOL;
					}
				}
			} else {
				if (isset($comment['avatar'])) {
					echo "\t\t", '<div class="hashover-header">', PHP_EOL;
					echo "\t\t\t", $this->template_replace['avatar'], PHP_EOL;
					echo "\t\t", '</div>', PHP_EOL;
				}

				echo "\t\t", '<div class="hashover-balloon">', PHP_EOL;
				echo "\t\t\t", '<div id="hashover-content-', $comment['permalink'], '" class="hashover-content', $notice_class, '">', PHP_EOL;
				echo "\t\t\t\t", '<span class="hashover-title">', $comment['notice'], '</span>', PHP_EOL;
				echo "\t\t\t", '</div>', PHP_EOL;
				echo "\t\t", '</div>', PHP_EOL;
			}

			if (!empty($comment['replies'])) {
				if ($this->setup->reply_mode == 'stream') {
					if (strpos($comment['permalink'], 'r') !== false) {
						foreach ($comment['replies'] as $reply) {
							$this->parse_template($comment['replies'][$reply]);
						}
					} else {
						echo "\t", $indention, '</div>', PHP_EOL;

						foreach ($comment['replies'] as $reply) {
							$this->parse_template($comment['replies'][$reply]);
						}
					}
				} else {
					foreach ($comment['replies'] as $reply) {
						$this->parse_template($reply);
					}

				}
			}

			echo "\t", $indention, '</div>', PHP_EOL;
		}

		public function add_forms($arr)
		{
			$return_form = '';

			if ($this->forPop == false and ($arr[1] == 'reply_form' or $arr[1] == 'edit_form')) {
				if ($arr[1] == 'reply_form' and $this->is_a_reply) {
					$return_form .= '<div class="hashover-balloon">' . PHP_EOL;
					$first_cmt_image = "\t\t" . '<div class="hashover-avatar-image hashover-avatar-first">' . PHP_EOL . "\t\t\t" . $this->form_first_image . PHP_EOL . "\t\t" . '</div>' . PHP_EOL;

					if (!empty($_COOKIE['hashover-login'])) {
						$first_cmt_image = '<div class="hashover-avatar-image">' . PHP_EOL . "\t\t\t" . $this->form_avatar . PHP_EOL . "\t\t" . '</div>' . PHP_EOL;

						if ($this->setup->icon_mode != 'none') {
							$return_form .= $first_cmt_image;
						}

						$return_form .= "\t\t" . '<input type="hidden" name="name" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '">' . PHP_EOL;
						$return_form .= "\t\t" . '<input type="hidden" name="password" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '">' . PHP_EOL;
						$return_form .= "\t\t" . '<input type="hidden" name="email" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '">' . PHP_EOL;
						$return_form .= "\t\t" . '<input type="hidden" name="website" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '">' . PHP_EOL;
					} else {
						$return_form .= "\t" . '<div class="hashover-inputs">' . PHP_EOL;

						if ($this->setup->icon_mode != 'none') {
							$return_form .= $first_cmt_image;
						}

						if ($this->name_on) {
							$return_form .= "\t\t" . '<div class="hashover-name-input">' . PHP_EOL;
							$return_form .= "\t\t\t" . '<input type="text" name="name" title="' . $this->setup->text['name_tip'] . '" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" maxlength="30" placeholder="' . $this->setup->text['name'] . '">' . PHP_EOL;
							$return_form .= "\t\t" . '</div>' . PHP_EOL;
						}

						if ($this->password_on) {
							$return_form .= "\t\t" . '<div class="hashover-password-input">' . PHP_EOL;
							$return_form .= "\t\t\t" . '<input type="password" name="password" title="' . $this->setup->text['password_tip'] . '" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $this->setup->text['password'] . '">' . PHP_EOL;
							$return_form .= "\t\t" . '</div>' . PHP_EOL;
						}

						if ($this->setup->is_mobile) {
							$return_form .= "\t" . '</div>' . PHP_EOL . "\t" . '<div class="hashover-inputs">' . PHP_EOL;
						}

						if ($this->email_on) {
							$return_form .= "\t\t" . '<div class="hashover-email-input">' . PHP_EOL;
							$return_form .= "\t\t\t" . '<input type="text" name="email" title="' . $this->setup->text['email_tip'] . '" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $this->setup->text['email'] . '">' . PHP_EOL;
							$return_form .= "\t\t" . '</div>' . PHP_EOL;
						}

						if ($this->website_on) {
							$return_form .= "\t\t" . '<div class="hashover-website-input">' . PHP_EOL;
							$return_form .= "\t\t\t" . '<input type="text" name="website" title="' . $this->setup->text['website_tip'] . '" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $this->setup->text['website'] . '">' . PHP_EOL;
							$return_form .= "\t\t" . '</div>' . PHP_EOL;
						}

						$return_form .= "\t" . '</div>' . PHP_EOL;
					}

					$return_form .= '<textarea rows="6" cols="62" name="comment" title="' . $this->setup->text['cmt_tip'] . '" placeholder="' . $this->setup->text['comment_form'] . '"></textarea>' . PHP_EOL;
					$return_form .= '<div class="hashover-form-buttons">' . PHP_EOL;
					$return_form .= "\t" . '<label for="subscribe" title="' . $this->setup->text['subscribe_tip'] . '">' . PHP_EOL;
					$return_form .= "\t\t" . '<input type="checkbox" checked="true" id="subscribe" name="subscribe"> ' . $this->setup->text['subscribe'] . PHP_EOL;
					$return_form .= "\t" . '</label>' . PHP_EOL;
					$return_form .= "\t" . '<input type="hidden" name="title" value="' . $this->setup->page_title . '">' . PHP_EOL;
					$return_form .= "\t" . '<input type="hidden" name="url" value="' . $this->setup->page_url . '">' . PHP_EOL;
					$return_form .= "\t" . '<input type="hidden" name="reply_to" value="' . str_replace(array('c', 'r', '_pop'), array('', '-', ''), $this->template_replace['permalink']) . '">' . PHP_EOL;
					$return_form .= "\t" . '<input class="hashover-submit" type="submit" value="' . $this->setup->text['post_button'] . '">' . PHP_EOL;
					$return_form .= "\t" . '<a class="hashover-submit" href="' . $this->setup->parse_url['path'] . ((!empty($this->setup->ref_queries)) ? '?' . $this->setup->ref_queries : '') . '#' . $this->template_replace['permalink'] . '">' . $this->setup->text['cancel'] . '</a>' . PHP_EOL;
					$return_form .= "\t" . '</div>' . PHP_EOL;
					$return_form .= '</div>';
				}

				if ($arr[1] == 'edit_form' and $this->is_an_edit) {
					$return_form = '<div class="hashover-dashed-title hashover-title">' . $this->setup->text['edit_cmt'] . '</div>' . PHP_EOL;
					$return_form .= '<div class="hashover-options open">' . PHP_EOL;
					$return_form .= "\t" . '<div class="hashover-inputs">' . PHP_EOL;
					$return_form .= "\t\t" . '<div class="hashover-name-input">' . PHP_EOL;
					$return_form .= "\t\t\t" . '<input type="text" name="name" title="' . $this->setup->text['name_tip'] . '" value="' . ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : '') . '" maxlength="30" placeholder="' . $this->setup->text['name'] . '">' . PHP_EOL;
					$return_form .= "\t\t" . '</div>' . PHP_EOL;

					$return_form .= "\t\t" . '<div class="hashover-password-input">' . PHP_EOL;
					$return_form .= "\t\t\t" . '<input type="password" name="password" title="' . $this->setup->text['password_tip'] . '" value="' . ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : '') . '" placeholder="' . $this->setup->text['password'] . '">' . PHP_EOL;
					$return_form .= "\t\t" . '</div>' . PHP_EOL;

					if ($this->setup->is_mobile) {
						$return_form .= "\t" . '</div>' . PHP_EOL . "\t" . '<div class="hashover-inputs">' . PHP_EOL;
					}

					$return_form .= "\t\t" . '<div class="hashover-email-input">' . PHP_EOL;
					$return_form .= "\t\t\t" . '<input type="text" name="email" title="' . $this->setup->text['email_tip'] . '" value="' . ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : '') . '" placeholder="' . $this->setup->text['email'] . '">' . PHP_EOL;
					$return_form .= "\t\t" . '</div>' . PHP_EOL;

					$return_form .= "\t\t" . '<div class="hashover-website-input">' . PHP_EOL;
					$return_form .= "\t\t\t" . '<input type="text" name="website" title="' . $this->setup->text['website_tip'] . '" value="' . ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : '') . '" placeholder="' . $this->setup->text['website'] . '">' . PHP_EOL;
					$return_form .= "\t\t" . '</div>' . PHP_EOL;

					// Clean HTML in comment
					$this->template_replace['comment'] = preg_replace('/<br>/i', '&#10;', $this->template_replace['comment']);
					$this->template_replace['comment'] = preg_replace('/<\/?a(\s+.*?>|>)/i', '', $this->template_replace['comment']);
					$this->template_replace['comment'] = preg_replace('/^\s+|\s+$/i', '', $this->template_replace['comment']);
					$this->template_replace['comment'] = preg_replace('/<code style="white-space: pre;">/i', '<code>', $this->template_replace['comment']);

					$return_form .= "\t" . '</div>' . PHP_EOL . '</div>' . PHP_EOL;
					$return_form .= '<textarea rows="10" cols="62" name="comment" title="' . $this->setup->text['cmt_tip'] . '" placeholder="' . $this->setup->text['reply_form'] . '">' . $this->template_replace['comment'] . '</textarea>' . PHP_EOL;
					$return_form .= '<div class="hashover-form-buttons">' . PHP_EOL;
					$return_form .= "\t" . '<label for="notify" title="' . $this->setup->text['subscribe_tip'] . '">' . PHP_EOL;
					$return_form .= "\t\t" . '<input type="checkbox"' . (($this->notifications != 'no') ? ' checked="true"' : '') . ' id="notify" name="notify"> ' . $this->setup->text['subscribe'] . PHP_EOL;
					$return_form .= "\t" . '</label>' . PHP_EOL;
					$return_form .= "\t" . '<input type="hidden" name="title" value="' . $this->setup->page_title . '">' . PHP_EOL;
					$return_form .= "\t" . '<input type="hidden" name="url" value="' . $this->setup->page_url . '">' . PHP_EOL;
					$return_form .= "\t" . '<input type="hidden" name="cmtfile" value="' . str_replace(array('c', 'r', '_pop'), array('', '-', ''), $this->template_replace['permalink']) . '">' . PHP_EOL;
					$return_form .= "\t" . '<input class="hashover-submit" type="submit" name="edit" value="' . $this->setup->text['save_edit'] . '">';
					$return_form .= "\t" . '<a class="hashover-submit" href="' . $this->setup->parse_url['path'] . ((!empty($this->setup->ref_queries)) ? '?' . $this->setup->ref_queries : '') . '#' . $this->template_replace['permalink'] . '">' . $this->setup->text['cancel'] . '</a>' . PHP_EOL;
					$return_form .= "\t" . '<input class="hashover-submit hashover-post-button" type="submit" name="delete" class="hashover-delete" value="' . $this->setup->text['delete'] . '">' . PHP_EOL;
					$return_form .= '</div>' . PHP_EOL;
				}

				return str_replace(PHP_EOL, PHP_EOL . "\t\t\t\t\t", $return_form) . PHP_EOL . "\t\t\t\t";
			}

			if (!empty($this->template_replace[$arr[1]])) {
				return $this->template_replace[$arr[1]];
			}

			return '';
		}
	}

	$php_mode = new PhpMode($this->read_comments, $this->setup);

?>

<div id="hashover" class="<?php echo $this->setup->image_format; ?>">
	<span id="comments"></span>
	<div class="hashover-dashed-title hashover-main-title hashover-title">
		<?php echo $php_title, PHP_EOL; ?>
	</div>
<?php
	if (!empty($_COOKIE['message'])) {
		echo "\t", '<span id="hashover-message" class="hashover-title">', $_COOKIE['message'], '</span>', PHP_EOL;
	}
?>

	<form id="hashover_form" name="hashover_form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<div class="hashover-balloon">
			<div class="hashover-inputs">
<?php

	if ($this->setup->icon_mode != 'none') {
		if ($this->setup->icon_mode == 'image') {
			if (!empty($_COOKIE['hashover-login'])) {
				echo "\t\t\t\t", '<div class="hashover-avatar-image">', $php_mode->form_avatar, '</div>', PHP_EOL;
			} else {
				echo "\t\t\t\t", '<div class="hashover-avatar-image hashover-avatar-first">', $php_mode->form_first_image, '</div>', PHP_EOL;
			}
		} else {
			echo "\t\t\t\t", '<div class="hashover-avatar-image"><span>#', $this->read_comments->cmt_count, '</span></div>', PHP_EOL;
		}
	}

?>

<?php

	if (!empty($_COOKIE['hashover-login'])) {
		echo "\t\t\t\t", '<div>', PHP_EOL;

		if (!empty($_COOKIE['website'])) {
			echo "\t\t\t\t\t", '<a class="hashover-name hashover-top-name" href="', $_COOKIE['website'], '" target="_blank">', $_COOKIE['name'], '</a>', PHP_EOL;
		} else {
			echo "\t\t\t\t\t", '<span class="hashover-name hashover-top-name">', $_COOKIE['name'], '</span>', PHP_EOL;
		}

		echo "\t\t\t\t", '</div>', PHP_EOL;
		echo "\t\t\t\t", '<input type="hidden" name="name" value="', !empty($_COOKIE['name']) ? $_COOKIE['name'] : '', '">', PHP_EOL;
		echo "\t\t\t\t", '<input type="hidden" name="password" value="', !empty($_COOKIE['password']) ? $_COOKIE['password'] : '', '">', PHP_EOL;
		echo "\t\t\t\t", '<input type="hidden" name="email" value="', !empty($_COOKIE['email']) ? $_COOKIE['email'] : '', '">', PHP_EOL;
		echo "\t\t\t\t", '<input type="hidden" name="website" value="', !empty($_COOKIE['website']) ? $_COOKIE['website'] : '', '">', PHP_EOL;
	} else {
		// Display name input tag if told to
		if ($php_mode->name_on) {
			echo "\t\t\t\t", '<div class="hashover-name-input">', PHP_EOL;
			echo "\t\t\t\t\t", '<input type="text" name="name" title="', $this->setup->text['name_tip'], '" maxlength="30" value="', ((!empty($_COOKIE['name'])) ? $_COOKIE['name'] : ''), '" placeholder="', $this->setup->text['name'], '">', PHP_EOL;
			echo "\t\t\t\t", '</div>', PHP_EOL;
		}

		// Display password input tag if told to
		if ($php_mode->password_on) {
			echo "\t\t\t\t", '<div class="hashover-password-input">', PHP_EOL;
			echo "\t\t\t\t\t", '<input type="password" name="password" title="', $this->setup->text['password_tip'], '" value="', ((!empty($_COOKIE['password'])) ? $_COOKIE['password'] : ''), '" placeholder="', $this->setup->text['password'], '">', PHP_EOL;
			echo "\t\t\t\t", '</div>', PHP_EOL;
		}

		// Add second table row on mobile devices
		if ($this->setup->is_mobile) {
			echo "\t\t\t\t", '</div>', PHP_EOL, "\t\t\t\t\t", '<div class="hashover-inputs">', PHP_EOL;

			if ($this->setup->icon_mode != 'none') {
				echo "\t\t\t\t\t", '<div class="hashover-avatar-image"></div>', PHP_EOL;
			}
		}

		// Display email input tag if told to
		if ($php_mode->email_on) {
			echo "\t\t\t\t", '<div class="hashover-email-input">', PHP_EOL;
			echo "\t\t\t\t\t", '<input type="text" name="email" title="', $this->setup->text['email_tip'], '" value="', ((!empty($_COOKIE['email'])) ? $_COOKIE['email'] : ''), '" placeholder="', $this->setup->text['email'], '">', PHP_EOL;
			echo "\t\t\t\t", '</div>', PHP_EOL;
		}

		// Display website input tag if told to
		if ($php_mode->website_on) {
			echo "\t\t\t\t", '<div class="hashover-website-input">', PHP_EOL;
			echo "\t\t\t\t\t", '<input type="text" name="website" title="', $this->setup->text['website_tip'], '" value="', ((!empty($_COOKIE['website'])) ? $_COOKIE['website'] : ''), '" placeholder="', $this->setup->text['website'], '">', PHP_EOL;
			echo "\t\t\t\t", '</div>', PHP_EOL;
		}
	}

	echo "\t\t\t", '</div>', PHP_EOL, PHP_EOL;
	echo "\t\t\t", '<div id="hashover-requiredFields">', PHP_EOL;
	echo "\t\t\t\t", '<input type="text" name="summary" value="" placeholder="Summary">', PHP_EOL;
	echo "\t\t\t\t", '<input type="hidden" name="age" value="" placeholder="Age">', PHP_EOL;
	echo "\t\t\t\t", '<input type="text" name="lastname" value="" placeholder="Last Name">', PHP_EOL;
	echo "\t\t\t\t", '<input type="text" name="address" value="" placeholder="Address">', PHP_EOL;
	echo "\t\t\t\t", '<input type="hidden" name="zip" value="" placeholder="Last Name">', PHP_EOL;
	echo "\t\t\t", '</div>', PHP_EOL, PHP_EOL;

	$replyborder = (isset($_COOKIE['success']) and $_COOKIE['success'] == "no") ? ' style="border: 2px solid #FF0000 !important;"' : '';
	echo "\t\t\t", '<textarea rows="5" cols="63" name="comment"', $replyborder, ' title="', $this->setup->text['cmt_tip'], '" placeholder="', $this->setup->text['comment_form'], '"></textarea>', PHP_EOL;
	echo "\t\t\t", '<input type="hidden" name="title" value="', $this->setup->page_title, '">', PHP_EOL;
	echo "\t\t\t", '<input type="hidden" name="url" value="', $this->setup->page_url, '">', PHP_EOL;
	if (isset($_COOKIE['replied'])) echo "\t\t\t", '<input type="hidden" name="reply_to" value="', $_COOKIE['replied'], '">', PHP_EOL;
	echo "\t\t\t", '<div class="hashover-main-buttons">';
	echo "\t\t\t\t", '<label for="hashover-subscribe"><input id="hashover-subscribe" type="checkbox" name="subscribe" checked="true"> ' . $this->setup->text['subscribe'] . '</label>', PHP_EOL;
	echo "\t\t\t\t", '<input class="hashover-submit hashover-post-button" type="submit" value="', $this->setup->text['post_button'], '">', PHP_EOL;

	if (empty($_COOKIE['hashover-login'])) {
		echo "\t\t\t\t", '<input class="hashover-submit hashover-login" type="submit" name="login" title="' . $this->setup->text['login_tip'] . '" value="' . $this->setup->text['login'] . '">', PHP_EOL;
	} else {
		echo "\t\t\t\t", '<input class="hashover-submit hashover-logout" type="submit" name="logout" title="' . $this->setup->text['logout'] . '" value="' . $this->setup->text['logout'] . '">', PHP_EOL;
	}

	echo "\t\t\t", '</div>', PHP_EOL;
	echo "\t\t", '</div>', PHP_EOL;
	echo "\t", '</form>', PHP_EOL, PHP_EOL;

	// Display most popular comments
	if (!empty($this->top_likes)) {
		krsort($this->top_likes); // Sort popular comments
		$popPlural = (count($this->top_likes) != 1) ? 1 : 0;

		echo "\t", '<div class="hashover-dashed-title">', PHP_EOL;
		echo "\t\t", '<span class="hashover-title">', PHP_EOL;
		echo "\t\t\t", $this->setup->text['popular_cmts'][$popPlural], PHP_EOL;
		echo "\t\t", '</span>', PHP_EOL;
		echo "\t", '</div>', PHP_EOL, PHP_EOL;

		echo "\t", '<div id="hashover-top-comments">', PHP_EOL;

		for ($p = 1, $pl = count($this->top_likes); $p <= $pl and $p <= $this->setup->pop_limit; $p++) {
			$popKey = array_shift($this->top_likes);
			$popComment = $this->read_comments->data->read($popKey);
			$php_mode->parse_template($this->parse($popComment, $popKey), true);
		}

		echo "\t", '</div>', PHP_EOL;
	}

	// Display comments, if there are no comments display a note
	if (!empty($this->hashover)) {
		// Display comment count
		echo "\t", '<div class="hashover-dashed-title hashover-sort-count">', PHP_EOL;
		echo "\t\t", '<span id="hashover-count">', $this->read_comments->show_count, '</span>', PHP_EOL;
		echo "\t", '</div>', PHP_EOL, PHP_EOL;

		foreach ($this->hashover as $comment) {
			$php_mode->parse_template($comment);
		}
	} else {
		$php_mode->parse_template(
			array(
				'avatar' => $this->setup->root_dir . '/images/' . $this->setup->image_format . 's/first-comment.' . $this->setup->image_format,
				'permalink' => 'c1',
				'notice' => $this->setup->text['first_cmt'],
				'notice_class' => 'hashover-first'
			)
		);
	}

?>

	<div id="hashover-end-links">
		<a href="http://tildehash.com/?page=hashover" target="_blank"><b>HashOver Comments</b></a> &#8210;
<?php

		if (!empty($this->hashover) and $this->setup->displays_rss_link == 'yes') {
			if ($this->setup->api_status('rss') != 'disabled') {
				echo "\t\t", '<a href="', $this->setup->root_dir, '/api/rss.php?url=', urlencode($this->setup->page_url), '" target="_blank">RSS Feed</a> &middot;', PHP_EOL;
			}
		}

?>
		<a href="/hashover.php?source" rel="hashover-source" target="_blank">Source Code</a>
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
//	http://<?php echo $this->setup->domain, '/hashover.php?source', PHP_EOL; ?>

window.onload = function() {
	var like_links = document.getElementsByClassName('hashover-like');
	var dislike_links = document.getElementsByClassName('hashover-dislike');

	for (var i = 0, li = like_links.length; i < li; i++) {
		like_links[i].style = '';
	}

	for (var i = 0, li = dislike_links.length; i < li; i++) {
		dislike_links[i].style = '';
	}
};

// Function to like a comment
function hashover_like(a, c, f) {
	// Load "like.php"
	var like = new XMLHttpRequest();
	var likeElement = document.getElementById('hashover-' + a + '-' + c);
	var likesElement = document.getElementById('hashover-' + a + 's-' + c);
	var dislikesClass = (a == 'like') ? '<?php if ($this->setup->allows_dislikes == 'yes') echo ' dislikes'; ?>' : '';
	like.open('GET', '<?php echo $this->setup->root_dir, '/scripts/like.php?like=', $this->setup->ref_path; ?>/' + f + '&action=' + a);
	like.send();

	// Get number of likes
	if (likesElement.textContent != '') {
		var likes = parseInt(likesElement.textContent.replace(/[^0-9]/g, ''));
	} else {
		var likes = parseInt(0);
	}

	// Change "Like" button title and class; Increase likes
	if (likeElement.className == 'hashover-' + a + dislikesClass) {
		likeElement.className = 'hashover-' + a + 'd' + dislikesClass;
		likeElement.title = (a == 'like') ? '<?php echo $this->setup->text['liked_cmt']; ?>' : '<?php echo $this->setup->text['disliked_cmt']; ?>';
		likeElement.textContent = (a == 'like') ? '<?php echo $this->setup->text['liked']; ?>' : '<?php echo $this->setup->text['disliked']; ?>';
		likes++;
	} else {
		likeElement.className = 'hashover-' + a + dislikesClass;
		likeElement.title = (a == 'like') ? '<?php echo $this->setup->text['like_cmt']; ?>' : '<?php echo $this->setup->text['dislike_cmt']; ?>';
		likeElement.textContent = (a == 'like') ? '<?php echo $this->setup->text['like'][0]; ?>' : '<?php echo $this->setup->text['dislike'][0]; ?>';
		likes--;
	}

	// Change number of likes
	var like_count = likes + ((a == 'like') ? ' Like' : ' Dislike') + ((likes != 1) ? 's' : '');
	likesElement.innerHTML = (likes > 0) ? '<b>' + like_count + '</b>' : '';
}
</script>
