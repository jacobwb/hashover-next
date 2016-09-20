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

// Spanish text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'Escriba un comentario aquí...',
	'reply-form'		=> 'Escriba su respuesta aquí...',
	'form-tip'		=> 'HTML Aceptado: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapa, Las URLs automágicamente se vuelven links, y [img] URL aquí [/img] mostrará una imagen externa.',
	'post-button'		=> 'Comentar',
	'login'			=> 'Iniciar Sesión',
	'login-tip'		=> 'Iniciar Sesión (opcional)',
	'logout'		=> 'Cerrar Sesión',
	'pending-name'		=> 'Pendiente...',
	'deleted-name'		=> 'Eliminado...',
	'error-name'		=> 'Error...',
	'pending-note'		=> 'Este comentario está pendiente de aprobación.',
	'deleted-note'		=> 'Este comentario ha sido eliminado.',
	'error-note'		=> 'Algo salió mal. No se pudo recuperar este comentario.',
	'options'		=> 'Opciones',
	'cancel'		=> 'Cancelar',
	'reply-to-comment'	=> 'Responder al Comentario',
	'edit-your-comment'	=> 'Editar tu Comentario',
	'optional'		=> 'Opcional',
	'required'		=> 'Requiere',
	'name'			=> 'Nombre',
	'name-tip'		=> 'Nombre (%s)',
	'password'		=> 'Contraseña',
	'password-tip'		=> 'Contraseña (%s, le permite editar o eliminar su comentario)',
	'confirm-password'	=> 'Confirmar Contraseña',
	'email'			=> 'E-mail',
	'email-tip'		=> 'Dirección de correo electrónico (para notificaciones, %s)',
	'website'		=> 'Sitio Web',
	'website-tip'		=> 'Sitio Web (%s)',
	'logged-in'		=> '¡Iniciaste sesión correctamente!',
	'logged-out'		=> '¡Cerraste sesión correctamente!',
	'comment-needed'	=> 'No hiciste ningún comentario valido. Utilice el siguiente formulario.',
	'reply-needed'		=> 'No hiciste ninguna respuesta valida. Utilice el siguiente formulario.',
	'field-needed'		=> 'Los "%s" campo es requiere.',
	'post-fail'		=> '¡Error! No tienes permisos suficientes.',
	'comment-deleted'	=> '¡Comentario Eliminado!',
	'post-reply'		=> 'Responder',
	'delete'		=> 'Borrar',
	'subscribe'		=> 'Notificarme si hay respuestas',
	'subscribe-tip'		=> 'Suscribirse a notificaciones por correo electrónico',
	'edit-comment'		=> 'Editar Comentario',
	'status-approved'	=> 'Aprobado',
	'status-pending'	=> 'Aprobación pendiente',
	'status-deleted'	=> 'Marcado eliminado',
	'save-edit'		=> 'Guardar',
	'no-email-warning'	=> 'Usted no recibirá notificación de las respuestas a su comentario sin de suministrar un e-mail.',
	'invalid-email'		=> 'La dirección de correo electrónico que ha introducido no es válida.',
	'delete-comment'	=> '¿Está seguro de que desea eliminar este comentario?',
	'post-comment-on'	=> array ('Publicar un Comentario', 'Publicar un Comentario en "%s"'),
	'popular-comments'	=> array ('Comentario mas popular', 'Comentarios mas populares'),
	'showing-comments'	=> array ('Mostrando %d comentario', 'Mostrando %d comentarios'),
	'count-link'		=> array ('%d comentario', '%d comentarios'),
	'count-replies'		=> array ('%d respuesta', '%d respuestas'),
	'sort'			=> 'Ordenar',
	'sort-ascending'	=> 'En orden',
	'sort-descending'	=> 'En orden inverso',
	'sort-by-date'		=> 'Reciente primero',
	'sort-by-likes'		=> 'Por likes',
	'sort-by-replies'	=> 'Por respuestas',
	'sort-by-discussion'	=> 'Por discusión',
	'sort-by-popularity'	=> 'Por popularidad',
	'sort-by-name'		=> 'Por comentarista',
	'sort-threads'		=> 'Hilos',
	'thread'		=> 'En respuesta a %s',
	'thread-tip'		=> 'Ir al inicio del tema',
	'comments'		=> 'Comentarios',
	'replies'		=> 'Respuestas',
	'edit'			=> 'Editar',
	'reply'			=> 'Responder',
	'like'			=> array ('Me gusta', 'Me gusta'),
	'liked'			=> 'Me gusta',
	'unlike'		=> 'Deshacer',
	'like-comment'		=> '\'Me gusta\' este Comentario',
	'liked-comment'		=> 'Quitar \'Me gusta\'',
	'dislike'		=> array ('No me gusta', 'No me gusta'),
	'disliked'		=> 'No me gusta',
	'dislike-comment'	=> '\'No me gusta\' este comentario',
	'disliked-comment'	=> 'Quitar \'No me gusta\' de este Comentario',
	'commenter-tip'		=> 'No seras notificado por correo electrónico',
	'subscribed-tip'	=> 'Seras notificado vía e-mail',
	'unsubscribed-tip'	=> 'No estas suscrito a las notificaciones por correo electrónico',
	'first-comment'		=> '¡Se el primero en comentar!',
	'show-other-comments'	=> array ('Mostrar %d Otro Comentario', 'Mostrar %d Otros Comentarios'),
	'show-number-comments'	=> array ('Mostrar %d Comentario', 'Mostrar %d Comentarios'),
	'date-years'		=> array ('Hace %d año', 'Hace %d años'),
	'date-months'		=> array ('Hace %d mes', 'Hace %d meses'),
	'date-days'		=> array ('Hace %d día', 'Hace %d días'),
	'date-today'		=> '%s de hoy',
	'untitled'		=> 'Untitled',
	'external-image-tip'	=> 'Clic para ver la imagen externa',
	'loading'		=> 'Cargando...',
	'click-to-close'	=> 'Clic para cerrar',
	'hashover-comments'	=> 'HashOver Comentarios',
	'rss-feed'		=> 'RSS Feed',
	'source-code'		=> 'Código Fuente'
);
