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

	// Japanese text for forms, buttons, links, and tooltips
	$locale = array (
		'comment_form'		=> 'ここにコメントを入力し（その他のフィールドはオプショナル）',
		'reply_form'		=> 'ここに返信を入力し（その他のフィールドはオプショナル）',
		'form_tip'		=> 'HTML容認：&lt;b&gt;、&lt;u&gt;、&lt;i&gt;、&lt;s&gt;、&lt;pre&gt;、&lt;ul&gt;、&lt;ol&gt;、&lt;li&gt;、&lt;blockquote&gt;、&lt;code&gt;はHTMLをエスケープ、URLは自動的にリンクになり、とここで[img]URLここ[/img]外部画像を表示します。',
		'post_button'		=> 'コメントポスト',
		'login'			=> 'ログイン',
		'login_tip'		=> 'ログイン (オプショナル)',
		'logout'		=> 'ログアウト',
		'pending_note'		=> 'このコメントは承認が保留されています。',
		'deleted_note'		=> 'このコメントは削除されました。',
		'comment_pending'	=> '保留中',
		'comment_deleted'	=> 'コメント削除た',
		'options'		=> 'のオプション',
		'cancel'		=> '取り消す',
		'reply_to_comment'	=> 'コメントへ返信',
		'edit_your_comment'	=> 'あなたのコメントを編集',
		'name'			=> '名字',
		'name_tip'		=> '名字 (オプショナル)',
		'password'		=> 'パスワード',
		'password_tip'		=> 'パスワード（オプショナル, 編集またはこのコメント削除することができます）',
		'confirm_password'	=> '確認パスワード',
		'email'			=> 'メールアドレス',
		'email_tip'		=> 'メールアドレス (オプショナル, 電子メール通知のための)',
		'website'		=> 'ウェブサイト',
		'website_tip'		=> 'ウェブサイト (オプショナル)',
		'logged_in'		=> 'あなたは、正常にログインされています！',
		'logged_out'		=> 'あなたは、正常にログアウトされています!',
		'comment_needed'	=> 'あなたが適切なコメントを入力ていませんでした。下記のフォームを使用。',
		'reply_needed'		=> 'あなたが適切なを入力応答ていませんでした。下記のフォームを使用。',
		'post_fail'		=> '失敗！あなたは十分な権限を欠いている。',
		'post_reply'		=> 'ポスト返信',
		'delete'		=> '削除',
		'subscribe'		=> '返信をお知らせ',
		'subscribe_tip'		=> '電子メール通知を購読',
		'edit_comment'		=> 'コメントを編集',
		'save_edit'		=> '保存編集',
		'no_email_warning'	=> 'あなたは、電子メールをずにあなたのコメントへの返信の通知を受け取ることができません。',
		'invalid_email'		=> '入力したザeメールアドレスが無効。',
		'delete_comment'	=> 'あなたはこのコメントを削除してもよろしいですか？',
		'post_comment_on'	=> array ('コメントの投稿', '上「_TITLE_」'),
		'popular_comments'	=> array ('コメントほとんど人気です', 'コメント最も人気'),
		'showing_comments'	=> array ('表示_NUM_コメント', '表示_NUM_のコメント'),
		'count_link'		=> array ('_NUM_コメント', '_NUM_のコメント'),
		'count_replies'		=> array ('_NUM_返信含めて', '_NUM_返信を含む'),
		'sort'			=> 'ソート',
		'sort_ascend'		=> '順番に',
		'sort_descend'		=> '逆の順番で',
		'sort_byname'		=> '評者によって',
		'sort_bydate'		=> '最初最新',
		'sort_bylikes'		=> '人気によって',
		'threaded'		=> '木構造',
		'thread'		=> 'トップのスレッド',
		'thread_tip'		=> 'スレッドの先頭にジャンプ',
		'replies'		=> '回答',
		'edit'			=> '編集',
		'reply'			=> '返信',
		'like'			=> array ('いいね', 'いいねわ'),
		'liked'			=> 'いいね',
		'unlike'		=> 'アンドゥ',
		'like_comment'		=> '「いいね」このコメント',
		'liked_comment'		=> 'アンドゥ「いいね」',
		'dislike'		=> array ('厭悪', '厭悪言いました'),
		'disliked'		=> '嫌わ',
		'dislike_comment'	=> '「厭悪」このコメントを',
		'disliked_comment'	=> 'あなたが「厭悪」このコメントを',
		'commenter_tip'		=> 'あなたが電子メールを介して通知されません',
		'subscribed_tip'	=> '電子メールを介して通知され',
		'unsubscribed_tip'	=> 'は、電子メール通知にサブスクライブされていない',
		'first_comment'		=> 'あなたが最初のコメントを付けて！',
		'show_other_comments'	=> array ('_NUM_その他のコメントを表示', '_NUM_その他のコメントを表示'),
		'show_number_comments'	=> array ('_NUM_件のコメントを表示', '_NUM_のコメントを表示'),
		'date_years'		=> array ('_NUM_年前', '_NUM_年前'),
		'date_months'		=> array ('_NUM_月前', '_NUM_ヶ月前'),
		'date_days'		=> array ('_NUM_日前', '_NUM_日前'),
		'date_today'		=> '_TIME_今日'
	);

?>
