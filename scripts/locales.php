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

	// Text for forms, buttons, links, and tooltips in multiple languages
	$locale = array(
		'en' => array(
			'comment_form'	=> 'Type Comment Here (other fields optional)',	// "Comment" field's default text
			'reply_form'	=> 'Type Reply Here (other fields optional)',	// "Reply" field's default text
			'post_button'	=> 'Post Comment',				// "Post Comment" button's default text
			'del_note'	=> 'This comment has been deleted.',		// Notice of deleted comment
			'cmt_deleted'	=> 'Comment Deleted!',				// Notice of successful comment deletion
			'options'	=> 'Options',					// "Options" button text
			'cancel'	=> 'Cancel',					// "Cancel" button text
			'reply_to_cmt'	=> 'Reply To Comment',
			'edit_your_cmt'	=> 'Edit Your Comment',
			'nickname_tip'	=> 'Nickname or Twitter @username',
			'nickname'	=> 'Nickname or @user',
			'password_tip'	=> 'Password (only needed for editing/deleting comment later)',
			'password'	=> 'Password',
			'email'		=> 'E-mail Address',
			'website'	=> 'Website',
			'logged_in'	=> 'You have been successfully logged in!',
			'cmt_needed'	=> 'You failed to enter a proper comment. Use the form below.',
			'reply_needed'	=> 'You failed to enter a proper reply. Use the form below.',
			'post_fail'	=> 'Comment failed to post! You lack sufficient permission.',
			'cmt_tip'	=> 'Accepted HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLs automagically become links, and [img]URL here[/img] will display an external image.',
			'post_reply'	=> 'Post Reply',
			'delete'	=> 'Delete',
			'subscribe_tip'	=> 'Subscribe to E-mail Notifications',
			'subscribe'	=> 'Subscribe',
			'edit_cmt'	=> 'Edit Comment',
			'save_edit'	=> 'Save Edit',
			'no_email_warn'	=> 'You will not receive notification of replies to your comment without supplying an e-mail.',
			'delete_cmt'	=> 'Are you sure you want to delete this comment?',
			'post_cmt'	=> 'Post a Comment',
			'popular_cmts'	=> 'Most Popular',
			'showing_cmts'	=> 'Showing',
			'sort'		=> 'Sort',
			'sort_ascend'	=> 'In Order',
			'sort_descend'	=> 'In Reverse Order',
			'sort_byname'	=> 'By Commenter',
			'sort_bydate'	=> 'By Date (newest first)',
			'sort_bylikes'	=> 'By Likes',
			'thread'	=> 'Top of Thread',
			'thread_tip'	=> 'Jump to top of thread',
			'like_cmt'	=> '\'Like\' This Comment',
			'liked_cmt'	=> 'You \'Liked\' This Comment',
			'op_cmt_note'	=> 'You will not be notified via e-mail',
			'subbed_note'	=> 'will be notified via e-mail',
			'unsubbed_note' => 'is not subscribed to e-mail notifications'
		),

		'es' => array(
			'comment_form'	=> 'Escriba un comentario aquí (otros campos opcionales)',
			'reply_form'	=> 'Escriba Reply Aquí (otros campos opcionales)',
			'post_button'	=> 'Publicar Comentario',
			'del_note'	=> 'Este comentario ha sido eliminado.',
			'cmt_deleted'	=> 'Comentario Eliminado!',
			'options'	=> 'Opciones',
			'cancel'	=> 'Cancelar',
			'reply_to_cmt'	=> 'Responder al Comentario',
			'edit_your_cmt'	=> 'Editar tu Comentario',
			'nickname_tip'	=> 'Apodo o nombre de Twitter @username',
			'nickname'	=> 'Apodo o @user',
			'password_tip'	=> 'Contraseña (necesario sólo para la edición / borrado de comentario tarde)',
			'password'	=> 'Contraseña',
			'email'		=> 'E-mail Dirección',
			'website'	=> 'Web sitio',
			'logged_in'	=> 'Eras satisfactoriamente conectado!',
			'cmt_needed'	=> 'Usted fracasado para entrar una adecuada comentario. Utilice el siguiente formulario.',
			'reply_needed'	=> 'Usted fracasado para entrar una adecuada respuesta. Utilice el siguiente formulario.',
			'post_fail'	=> 'Fracasado para puesto comentario! Te falta permisos suficientes.',
			'cmt_tip'	=> 'Aceptado HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapa, URLs enlaces HTML automágicamente convertido, y [img] URL aquí [/img] mostrará una imagen externa.',
			'post_reply'	=> 'Enviar Respuesta',
			'delete'	=> 'Borrar',
			'subscribe_tip'	=> 'Suscribirse a notificaciones de correo electrónico',
			'subscribe'	=> 'Suscribir',
			'edit_cmt'	=> 'Redactar Comentario',
			'save_edit'	=> 'Guardar Editar',
			'no_email_warn'	=> 'Usted no recibirá notificación de las respuestas a su comentario sin de suministrar un e-mail.',
			'delete_cmt'	=> '¿Está seguro que desea eliminar este comentario?',
			'post_cmt'	=> 'Publicar un Comentario',
			'popular_cmts'	=> 'La Más Populares',
			'showing_cmts'	=> 'Demostración',
			'sort'		=> 'Ordenar',
			'sort_ascend'	=> 'En el Orden',
			'sort_descend'	=> 'En Orden Inverso',
			'sort_byname'	=> 'Por Comentarista',
			'sort_bydate'	=> 'Por Fecha (reciente primero)',
			'sort_bylikes'	=> 'Por Likes',
			'thread'	=> 'Inicio de Tema',
			'thread_tip'	=> 'Ir al inicio de la rosca',
			'like_cmt'	=> '\'Gusta\' Este Comentario',
			'liked_cmt'	=> 'Usted \'Gusta\' Este Comentario',
			'op_cmt_note'	=> 'No lo harás notificará por correo electrónico',
			'subbed_note'	=> 'será notificado vía e-mail',
			'unsubbed_note' => 'no es suscrito a las notificaciones por correo electrónico'
		),

		'jp' => array(
			'comment_form'	=> 'ここにコメントを入力し（その他のフィールドはオプショナル）',
			'reply_form'	=> 'ここに返信を入力し（その他のフィールドはオプショナル）',
			'post_button'	=> 'コメントポスト',
			'del_note'	=> 'このコメントは削除されました。',
			'cmt_deleted'	=> 'コメント削除た',
			'options'	=> 'のオプション',
			'cancel'	=> '取り消す',
			'reply_to_cmt'	=> 'コメントへ返信',
			'edit_your_cmt'	=> 'あなたのコメントを編集',
			'nickname_tip'	=> 'ニックネームTwitterまたは @username',
			'nickname'	=> 'ニックネームまたは @user',
			'password_tip'	=> 'パスワード（後でコメントを編集/削除するためにのみ）',
			'password'	=> 'パスワード',
			'email'		=> 'メールアドレス',
			'website'	=> 'ウェブサイト',
			'logged_in'	=> 'あなたは、正常にログインされています！',
			'cmt_needed'	=> 'あなたが適切なコメントを入力ていませんでした。下記のフォームを使用。',
			'reply_needed'	=> 'あなたが適切なを入力応答ていませんでした。下記のフォームを使用。',
			'post_fail'	=> 'コメントをするには失敗しました！あなたは十分な権限を欠いている。',
			'cmt_tip'	=> 'HTML容認：&lt;b&gt;、&lt;u&gt;、&lt;i&gt;、&lt;s&gt;、&lt;pre&gt;、&lt;ul&gt;、&lt;ol&gt;、&lt;li&gt;、&lt;blockquote&gt;、&lt;code&gt;はHTMLをエスケープ、URLは自動的にリンクになり、とここで[img]URLここ[/img]外部画像を表示します。',
			'post_reply'	=> 'ポスト返信',
			'delete'	=> '削除',
			'subscribe_tip'	=> '電子メール通知を購読',
			'subscribe'	=> '購読する',
			'edit_cmt'	=> 'コメントを編集',
			'save_edit'	=> '保存編集',
			'no_email_warn'	=> 'あなたは、電子メールをずにあなたのコメントへの返信の通知を受け取ることができません。',
			'delete_cmt'	=> 'あなたはこのコメントを削除してもよろしいですか？',
			'post_cmt'	=> 'コメントの投稿',
			'popular_cmts'	=> '人気',
			'showing_cmts'	=> '表示',
			'sort'		=> 'ソート',
			'sort_ascend'	=> '順番に',
			'sort_descend'	=> '逆の順番で',
			'sort_byname'	=> '評者によって',
			'sort_bydate'	=> '日によって（最初最新）',
			'sort_bylikes'	=> 'によって Likes',
			'thread'	=> 'ねじ山の頂',
			'thread_tip'	=> 'スレッドの先頭にジャンプ',
			'like_cmt'	=> '「Like」このコメントを',
			'liked_cmt'	=> '君「Liked」このコメントを',
			'op_cmt_note'	=> 'あなたが電子メールを介して通知されません',
			'subbed_note'	=> '電子メールを介して通知され',
			'unsubbed_note' => 'は、電子メール通知にサブスクライブされていない'
		)
	);

	// Set locale to language in settings
	$text = $locale[$language];

?>
