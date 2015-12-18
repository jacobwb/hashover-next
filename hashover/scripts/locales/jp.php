<?php

// Copyright (C) 2015 Jacob Barkdull
// This file is part of HashOver.
//
// I, Jacob Barkdull, hereby release this work into the public domain.
// This applies worldwide. If this is not legally possible, I grant any
// entity the right to use this work for any purpose, without any
// conditions, unless such conditions are required by law.


// Display source code
if (basename ($_SERVER['PHP_SELF']) === basename (__FILE__)) {
	if (isset ($_GET['source'])) {
		header ('Content-type: text/plain; charset=UTF-8');
		exit (file_get_contents (basename (__FILE__)));
	}
}

// Japanese text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'ここにコメントを入力し...',
	'reply-form'		=> 'ここに返信を入力し...',
	'form-tip'		=> 'HTML容認：&lt;b&gt;、&lt;u&gt;、&lt;i&gt;、&lt;s&gt;、&lt;pre&gt;、&lt;ul&gt;、&lt;ol&gt;、&lt;li&gt;、&lt;blockquote&gt;、&lt;code&gt;はHTMLをエスケープ、URLは自動的にリンクになり、とここで[img]URLここ[/img]外部画像を表示します。',
	'post-button'		=> 'コメントポスト',
	'login'			=> 'ログイン',
	'login-tip'		=> 'ログイン (オプショナル)',
	'logout'		=> 'ログアウト',
	'pending-note'		=> 'このコメントは承認が保留されています。',
	'deleted-note'		=> 'このコメントは削除されました。',
	'comment-pending'	=> '保留中...',
	'comment-deleted'	=> 'コメント削除た！',
	'options'		=> 'のオプション',
	'cancel'		=> '取り消す',
	'reply-to-comment'	=> 'コメントへ返信',
	'edit-your-comment'	=> 'あなたのコメントを編集',
	'optional'		=> 'オプショナル',
	'required'		=> '必須です',
	'name'			=> '名字',
	'name-tip'		=> '名字 (%s)',
	'password'		=> 'パスワード',
	'password-tip'		=> 'パスワード（%s, 編集またはこのコメント削除することができます）',
	'confirm-password'	=> '確認パスワード',
	'email'			=> 'メールアドレス',
	'email-tip'		=> 'メールアドレス (%s, 電子メール通知のための)',
	'website'		=> 'ウェブサイト',
	'website-tip'		=> 'ウェブサイト (%s)',
	'logged-in'		=> 'あなたは、正常にログインされています！',
	'logged-out'		=> 'あなたは、正常にログアウトされています!',
	'comment-needed'	=> 'あなたが適切なコメントを入力ていませんでした。下記のフォームを使用。',
	'reply-needed'		=> 'あなたが適切なを入力応答ていませんでした。下記のフォームを使用。',
	'field-needed'		=> '%sフィールドは必須です。',
	'post-fail'		=> '失敗！あなたは十分な権限を欠いている。',
	'post-reply'		=> 'ポスト返信',
	'delete'		=> '削除',
	'subscribe'		=> '返信をお知らせ',
	'subscribe-tip'		=> '電子メール通知を購読',
	'edit-comment'		=> 'コメントを編集',
	'save-edit'		=> '保存編集',
	'no-email-warning'	=> 'あなたは、電子メールをずにあなたのコメントへの返信の通知を受け取ることができません。',
	'invalid-email'		=> '入力したザeメールアドレスが無効。',
	'delete-comment'	=> 'あなたはこのコメントを削除してもよろしいですか？',
	'post-comment-on'	=> array ('コメントの投稿', 'コメントの投稿上「%s」'),
	'popular-comments'	=> array ('コメントほとんど人気です', 'コメント最も人気'),
	'showing-comments'	=> array ('表示%dコメント', '表示%dのコメント'),
	'count-link'		=> array ('%dコメント', '%dのコメント'),
	'count-replies'		=> array ('%d返信含めて', '%d返信を含む'),
	'sort'			=> 'ソート',
	'sort-ascend'		=> '順番に',
	'sort-descend'		=> '逆の順番で',
	'sort-byname'		=> '評者によって',
	'sort-bydate'		=> '最初最新',
	'sort-bylikes'		=> '人気によって',
	'threaded'		=> '木構造',
	'thread'		=> '%sへの返信で',
	'thread-tip'		=> 'スレッドの先頭にジャンプ',
	'replies'		=> '回答',
	'edit'			=> '編集',
	'reply'			=> '返信',
	'like'			=> array ('いいね', 'いいねわ'),
	'liked'			=> 'いいね',
	'unlike'		=> 'アンドゥ',
	'like-comment'		=> '「いいね」このコメント',
	'liked-comment'		=> 'アンドゥ「いいね」',
	'dislike'		=> array ('厭悪', '厭悪言いました'),
	'disliked'		=> '嫌わ',
	'dislike-comment'	=> '「厭悪」このコメントを',
	'disliked-comment'	=> 'あなたが「厭悪」このコメントを',
	'commenter-tip'		=> 'あなたが電子メールを介して通知されません',
	'subscribed-tip'	=> '電子メールを介して通知され',
	'unsubscribed-tip'	=> 'は、電子メール通知にサブスクライブされていない',
	'first-comment'		=> 'あなたが最初のコメントを付けて！',
	'show-other-comments'	=> array ('%dその他のコメントを表示', '%dその他のコメントを表示'),
	'show-number-comments'	=> array ('%d件のコメントを表示', '%dのコメントを表示'),
	'date-years'		=> array ('%d年前', '%d年前'),
	'date-months'		=> array ('%d月前', '%dヶ月前'),
	'date-days'		=> array ('%d日前', '%d日前'),
	'date-today'		=> '%s今日',
	'untitled'		=> 'タイトルなし'
);
