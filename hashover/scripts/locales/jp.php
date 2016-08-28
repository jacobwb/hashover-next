<?php

// Copyright (C) 2015-2016 Jacob Barkdull
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
	'comment-form'		=> 'ココにコメントを入力...',
	'reply-form'		=> 'ココに返信を入力...',
	'form-tip'		=> 'HTML容認：&lt;b&gt;、&lt;u&gt;、&lt;i&gt;、&lt;s&gt;、&lt;pre&gt;、&lt;ul&gt;、&lt;ol&gt;、&lt;li&gt;、&lt;blockquote&gt;、&lt;code&gt;はHTMLをエスケープ、URLは自動的にリンクになり、とここで[img]URLここ[/img]外部画像を表示します。',
	'post-button'		=> '送信する',
	'login'			=> 'ログイン',
	'login-tip'		=> 'ログイン ( 任意 )',
	'logout'		=> 'ログアウト',
	'pending-note'		=> 'このコメントは承認が保留されています。',
	'deleted-note'		=> 'このコメントは削除されました。',
	'comment-pending'	=> '保留中...',
	'comment-deleted'	=> 'コメントは削除！',
	'options'		=> 'のオプション',
	'cancel'		=> 'キャンセル',
	'reply-to-comment'	=> 'コメントへ返信',
	'edit-your-comment'	=> 'あなたのコメントを編集',
	'optional'		=> '任意',
	'required'		=> '必須',
	'name'			=> 'お名前',
	'name-tip'		=> 'お名前 ( %s )',
	'password'		=> 'パスワード',
	'password-tip'		=> 'パスワード（ %s , 編集またはこのコメントを削除することができます。）',
	'confirm-password'	=> '確認パスワード',
	'email'			=> 'メールアドレス',
	'email-tip'		=> 'メールアドレス ( %s , 返信をお知らせする為のメールアドレスです。)',
	'website'		=> 'WEBサイト',
	'website-tip'		=> 'WEBサイト・URL ( %s )',
	'logged-in'		=> 'あなたは、正常にログインされています！',
	'logged-out'		=> '正常にログアウトされました!',
	'comment-needed'	=> '有効なコメントが入力ていません。下記のフォームに入力してください。',
	'reply-needed'		=> '有効な入力が応答していません。下記のフォームに入力してください。',
	'field-needed'		=> '%sフィールドは必須です。',
	'post-fail'		=> '失敗しました！十分な権限がありません。',
	'post-reply'		=> '返信する',
	'delete'		=> '削除する',
	'subscribe'		=> '返信をお知らせ',
	'subscribe-tip'		=> 'コメントへの返信をメールでお知らせ',
	'edit-comment'		=> 'コメントを編集する',
	'save-edit'		=> '編集を保存する',
	'no-email-warning'	=> 'メールアドレスを入力しないとコメントへの返信のお知らせを受け取ることができません。よろしいですか？',
	'invalid-email'		=> '入力したメールアドレスが無効です。',
	'delete-comment'	=> 'このコメントを削除してもよろしいですか？',
	'post-comment-on'	=> array ('コメントの投稿', 'コメントの投稿上「%s」'),
	'popular-comments'	=> array ('コメントほとんど人気です', '最も人気のコメント'),
	'showing-comments'	=> array ('%d 件のコメント', '%d 件のコメント'),
	'count-link'		=> array ('%dコメント', '%dのコメント'),
	'count-replies'		=> array ('返信含む %d件', '%d 件の返信含む'),
	'sort'			=> 'ソート',
	'sort-ascending'	=> 'コメント順',
	'sort-descending'	=> '新しいもの順',
	'sort-by-name'		=> '評価順',
	'sort-by-date'		=> '最新のコメント',
	'sort-by-likes'		=> '人気順',
	'threaded'		=> 'ツリー形式',
	'thread'		=> '%sへの返信',
	'thread-tip'		=> 'スレッドの先頭にジャンプ',
	'replies'		=> '回答',
	'edit'			=> '編集',
	'reply'			=> '返信',
	'like'			=> array ('いいね', 'いいね'),
	'liked'			=> 'いいね',
	'unlike'		=> '取消す',
	'like-comment'		=> '「いいね」する',
	'liked-comment'		=> '「いいね」を取消す',
	'dislike'		=> array ('厭悪', '厭悪言いました'),
	'disliked'		=> '嫌わ',
	'dislike-comment'	=> '「厭悪」このコメントを',
	'disliked-comment'	=> 'あなたが「厭悪」このコメントを',
	'commenter-tip'		=> '電子メールを介して通知されません',
	'subscribed-tip'	=> '電子メールを介して通知され',
	'unsubscribed-tip'	=> '匿名の場合はメール通知されません',
	'first-comment'		=> 'ぜひ最初のコメントを！',
	'show-other-comments'	=> array ('その他 %d 件のコメントを表示', '全 %d 件のコメントを表示'),
	'show-number-comments'	=> array ('%d 件のコメントを表示', '他 %d コメントを表示'),
	'date-years'		=> array ('%d年前', '%d年前'),
	'date-months'		=> array ('%d月前', '%dヶ月前'),
	'date-days'		=> array ('%d日前', '%d日前'),
	'date-today'		=> '%s 今日',
	'untitled'		=> 'タイトルなし'
);
