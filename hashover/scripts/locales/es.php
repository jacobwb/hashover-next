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

	// Spanish text for forms, buttons, links, and tooltips
	$locale = array (
		'comment_form'		=> 'Escriba un comentario aquí (otros campos son opcionales)',
		'reply_form'		=> 'Escriba su respuesta aquí (otros campos opcionales)',
		'form_tip'		=> 'HTML Aceptado: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapa, Las URLs automágicamente se vuelven links, y [img] URL de imagen [/img] mostrará una imagen externa.',
		'post_button'		=> 'Comentar',
		'login'			=> 'Iniciar Sesión',
		'login_tip'		=> 'Iniciar Sesión (opcional)',
		'logout'		=> 'Cerrar Sesión',
		'pending_note'		=> 'Este comentario está pendiente de aprobación.',
		'deleted_note'		=> 'Este comentario ha sido eliminado.',
		'comment_pending'	=> 'Pendiente',
		'comment_deleted'	=> '¡Comentario Eliminado!',
		'options'		=> 'Opciones',
		'cancel'		=> 'Cancelar',
		'reply_to_comment'	=> 'Responder al Comentario',
		'edit_your_comment'	=> 'Editar tu Comentario',
		'name'			=> 'Nombre',
		'name_tip'		=> 'Nombre (opcional)',
		'password'		=> 'Contraseña',
		'password_tip'		=> 'Contraseña (opcional, le permite editar o eliminar su comentario)',
		'confirm_password'	=> 'Confirmar Contraseña',
		'email'			=> 'E-mail',
		'email_tip'		=> 'Dirección de correo electrónico (para notificaciones, opcional)',
		'website'		=> 'Sitio Web',
		'website_tip'		=> 'Sitio Web (opcional)',
		'logged_in'		=> '¡Iniciaste sesión correctamente!',
		'logged_out'		=> '¡Cerraste sesión correctamente!',
		'comment_needed'	=> 'No hiciste ningún comentario valido. Utilice el siguiente formulario.',
		'reply_needed'		=> 'No hiciste ninguna respuesta valida. Utilice el siguiente formulario.',
		'post_fail'		=> '¡Error! No tienes permisos suficientes.',
		'post_reply'		=> 'Responder',
		'delete'		=> 'Borrar',
		'subscribe'		=> 'Notificarme si hay respuestas',
		'subscribe_tip'		=> 'Suscribirse a notificaciones por correo electrónico',
		'edit_comment'		=> 'Editar Comentario',
		'save_edit'		=> 'Guardar',
		'no_email_warning'	=> 'Usted no recibirá notificación de las respuestas a su comentario sin de suministrar un e-mail.',
		'invalid_email'		=> 'La dirección de correo electrónico que ha introducido no es válida.',
		'delete_comment'	=> '¿Está seguro de que desea eliminar este comentario?',
		'post_comment_on'	=> array ('Publicar un Comentario', ' en "_TITLE_"'),
		'popular_comments'	=> array ('Comentario mas popular', 'Comentarios mas populares'),
		'showing_comments'	=> array ('Mostrando _NUM_ comentario', 'Mostrando _NUM_ comentarios'),
		'count_link'		=> array ('_NUM_ comentario', '_NUM_ comentarios'),
		'count_replies'		=> array ('_NUM_ respuesta', '_NUM_ respuestas'),
		'sort'			=> 'Ordenar',
		'sort_ascend'		=> 'En Orden',
		'sort_descend'		=> 'En Orden Inverso',
		'sort_byname'		=> 'Por Comentarista',
		'sort_bydate'		=> 'Reciente Primero',
		'sort_bylikes'		=> 'Por Popularidad',
		'threaded'		=> 'Estructura de árbol',
		'thread'		=> 'Inicio de Tema',
		'thread_tip'		=> 'Ir al inicio del tema',
		'replies'		=> 'Respuestas',
		'edit'			=> 'Editar',
		'reply'			=> 'Responder',
		'like'			=> array ('Me gusta', 'Me gusta'),
		'liked'			=> 'Me gusta',
		'unlike'		=> 'Deshacer',
		'like_comment'		=> '\'Me gusta\' este Comentario',
		'liked_comment'		=> 'Quitar \'Me gusta\'',
		'dislike'		=> array ('No me gusta', 'No me gusta'),
		'disliked'		=> 'No me gusta',
		'dislike_comment'	=> '\'No me gusta\' este comentario',
		'disliked_comment'	=> 'Quitar \'No me gusta\' de este Comentario',
		'commenter_tip'		=> 'No seras notificado por correo electrónico',
		'subscribed_tip'	=> 'Seras notificado vía e-mail',
		'unsubscribed_tip'	=> 'No estas suscrito a las notificaciones por correo electrónico',
		'first_comment'		=> '¡Se el primero en comentar!',
		'show_other_comments'	=> array ('Mostrar _NUM_ Otro Comentario', 'Mostrar _NUM_ Otros Comentarios'),
		'show_number_comments'	=> array ('Mostrar _NUM_ Comentario', 'Mostrar _NUM_ Comentarios'),
		'date_years'		=> array ('Hace _NUM_ año', 'Hace _NUM_ años'),
		'date_months'		=> array ('Hace _NUM_ mes', 'Hace _NUM_ meses'),
		'date_days'		=> array ('Hace _NUM_ día', 'Hace _NUM_ días'),
		'date_today'		=> '_TIME_ de hoy'
	);

?>
