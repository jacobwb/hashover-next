<?php

	// Copyright (C) 2014 Jacob Barkdull
	//
	//	I, Jacob Barkdull, hereby release this work into the public domain. 
	//	This applies worldwide. If this is not legally possible, I grant any 
	//	entity the right to use this work for any purpose, without any 
	//	conditions, unless such conditions are required by law.


	// Display source code
	if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
		if (isset($_GET['source'])) {
			header('Content-type: text/plain; charset=UTF-8');
			exit(file_get_contents(basename(__FILE__)));
		}
	}

	// Spanish text for forms, buttons, links, and tooltips
	$locale = array(
		'comment_form'	=> 'Escriba un comentario aquí (otros campos opcionales)',
		'reply_form'	=> 'Escriba Reply Aquí (otros campos opcionales)',
		'post_button'	=> 'Publicar Comentario',
		'login'		=> 'Iniciar Sesión',
		'login_tip'	=> 'Iniciar Sesión (opcional)',
		'logout'	=> 'Cerrar Sesión',
		'del_note'	=> 'Este comentario ha sido eliminado.',
		'cmt_deleted'	=> 'Comentario Eliminado!',
		'options'	=> 'Opciones',
		'cancel'	=> 'Cancelar',
		'reply_to_cmt'	=> 'Responder al Comentario',
		'edit_your_cmt'	=> 'Editar tu Comentario',
		'name'		=> 'Nombre',
		'name_tip'	=> 'Nombre (opcional)',
		'password'	=> 'Contraseña',
		'password_tip'	=> 'Contraseña (opcional, le permite editar o eliminar este comentario)',
		'email'		=> 'E-mail Dirección',
		'email_tip'	=> 'Dirección de correo electrónico (para las notificaciones de correo electrónico opcional)',
		'website'	=> 'Web sitio',
		'website_tip'	=> 'Web sitio (opcional)',
		'logged_in'	=> 'Estabas éxito iniciado la sesión en!',
		'logged_out'	=> 'Estabas éxito cerrado la sesión!',
		'cmt_needed'	=> 'Usted fracasado para entrar una adecuada comentario. Utilice el siguiente formulario.',
		'reply_needed'	=> 'Usted fracasado para entrar una adecuada respuesta. Utilice el siguiente formulario.',
		'post_fail'	=> 'Fracaso! Te falta permisos suficientes.',
		'cmt_tip'	=> 'Aceptado HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapa, URLs enlaces HTML automágicamente convertido, y [img] URL aquí [/img] mostrará una imagen externa.',
		'post_reply'	=> 'Enviar Respuesta',
		'delete'	=> 'Borrar',
		'subscribe'	=> 'Notificarme respuestas',
		'subscribe_tip'	=> 'Suscribirse a notificaciones de correo electrónico',
		'edit_cmt'	=> 'Editar Comentario',
		'save_edit'	=> 'Guardar Editar',
		'no_email_warn'	=> 'Usted no recibirá notificación de las respuestas a su comentario sin de suministrar un e-mail.',
		'invalid_email'	=> 'La dirección de correo electrónico que ha introducido no es válido.',
		'delete_cmt'	=> '¿Está seguro que desea eliminar este comentario?',
		'post_cmt_on'	=> array('Publicar un Comentario', ' en "_TITLE_"'),
		'popular_cmts'	=> array('Más Populares Comentario', 'Más Populares Comentarios'),
		'showing_cmts'	=> array('Mostrando _NUM_ Comentario', 'Mostrando _NUM_ Comentarios'),
		'count_replies'	=> array('_NUM_ contando respuesta', '_NUM_ contando respuestas'),
		'sort'		=> 'Ordenar',
		'sort_ascend'	=> 'En el Orden',
		'sort_descend'	=> 'En Orden Inverso',
		'sort_byname'	=> 'Por Comentarista',
		'sort_bydate'	=> 'Reciente Primero',
		'sort_bylikes'	=> 'Por Popularidad',
		'threaded'	=> 'Estructura del árbol',
		'thread'	=> 'Inicio de Tema',
		'thread_tip'	=> 'Ir al inicio de la rosca',
		'replies'	=> 'Respuestas',
		'edit'		=> 'Editar',
		'reply'		=> 'Responder',
		'like'		=> array('Gustar', 'Gustos'),
		'liked'		=> 'Me gustó',
		'like_cmt'	=> '\'Gusta\' Este Comentario',
		'liked_cmt'	=> 'Usted \'Gusta\' Este Comentario',
		'dislike'	=> array('Disgusta', 'Disgustos'),
		'disliked'	=> 'Disgustó',
		'dislike_cmt'	=> '\'Disgustó\' Este Comentario',
		'disliked_cmt'	=> 'Usted \'Disgustó\' Este Comentario',
		'op_cmt_note'	=> 'No lo harás notificará por correo electrónico',
		'subbed_note'	=> 'será notificado vía e-mail',
		'unsubbed_note' => 'no es suscrito a las notificaciones por correo electrónico',
		'first_cmt'	=> '¡Ser el primero en comentar!',
		'other_cmts'	=> array('Desde el _NUM_ Otras Comentario', 'Mostrar _NUM_ Otro Comentarios'),
		'show_num_cmts'	=> array('Desde el _NUM_ Comentario', 'Mostrar _NUM_ Comentarios'),
		'date_years'	=> array('Hace _NUM_ año', 'Hace _NUM_ años'),
		'date_months'	=> array('Hace _NUM_ mes', 'Hace _NUM_ meses'),
		'date_days'	=> array('Hace _NUM_ día', 'Hace _NUM_ días'),
		'date_today'	=> '_TIME_ de hoy'
	);

?>
