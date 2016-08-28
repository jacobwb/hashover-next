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

// Brazilian Portuguese  text for forms, buttons, links, and tooltips
$locale = array(
	'comment-form'		=> 'Digite aqui seu comentário...',
	'reply-form'		=> 'Digite a resposta aqui...',
	'form-tip'		=> 'HTML permitido: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; evite HTML, URLs automaticamente se tornam links, e [img]URL aqui[/img] irão mostrar uma imagem externa.',
	'post-button'		=> 'Enviar comentário.',
	'login'			=> 'Fazer login',
	'login-tip'		=> 'Fazer login (opcionais)',
	'logout'		=> 'Sair',
	'pending-note'		=> 'Esse comentário está pendente de aprovação.',
	'deleted-note'		=> 'Este comentário foi deletado.',
	'comment-pending'	=> 'Pendente...',
	'comment-deleted'	=> 'Comentário apagado!',
	'options'		=> 'Opções',
	'cancel'		=> 'Cancelar',
	'reply-to-comment'	=> 'Responder ao comentário',
	'edit-your-comment'	=> 'Editar seu comentário',
	'optional'		=> 'Opcionais',
	'required'		=> 'Obrigatório',
	'name'			=> 'Nome',
	'name-tip'		=> 'Nome (%s)',
	'password'		=> 'Senha',
	'password-tip'		=> 'Senha (%s, permite que você edite ou inclua este comentário)',
	'confirm-password'	=> 'Confirme a Senha',
	'email'			=> 'E-mail',
	'email-tip'		=> 'E-mail Address (%s, para as notificações de e-mail)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (%s)',
	'logged-in'		=> 'Você logou com sucesso!',
	'logged-out'		=> 'Você tem saiu com sucesso!',
	'comment-needed'	=> 'Você não enviou um comentário de maneira correta. Use o formulário abaixo.',
	'reply-needed'		=> 'Você não respondeu ao comentário de maneira correta. Use o formulário abaixo.',
	'field-needed'		=> 'O campo %s é obrigatório.',
	'post-fail'		=> 'Falha! Você não possui permissões suficientes.',
	'post-reply'		=> 'Enviar resposta',
	'delete'		=> 'Apagar',
	'subscribe'		=> 'Notifique-me sobre respostas',
	'subscribe-tip'		=> 'Assine para receber notícias por email.',
	'edit-comment'		=> 'Editar comentários',
	'save-edit'		=> 'Salvar edições',
	'no-email-warning'	=> 'Você não receberá notificações ou respostas do seu comentário sem fornecer um endereço de email.',
	'invalid-email'		=> 'O endereço de e-mail digitado é inválido.',
	'delete-comment'	=> 'Tem certeza que deseja apagar este comentário?',
	'post-comment-on'	=> array ('Enviar um comentário', 'Enviar um comentário na "%s"'),
	'popular-comments'	=> array ('Mais Populares Comentário', 'Mais Populares Comentários'),
	'showing-comments'	=> array ('Mostrando %d Comentário', 'Mostrando %d Comentários'),
	'count-link'		=> array ('%d Comentário', '%d Comentários'),
	'count-replies'		=> array ('%d contando resposta', '%d contando respostas'),
	'sort'			=> 'Ordenar',
	'sort-ascending'	=> 'Em ordem',
	'sort-descending'	=> 'Em ordem onversa',
	'sort-by-date'		=> 'Recentes primeiro',
	'sort-by-likes'		=> 'Por likes',
	'sort-by-replies'	=> 'Por respostas',
	'sort-by-discussion'	=> 'Por discussão',
	'sort-by-popularity'	=> 'Por popularidade',
	'sort-by-name'		=> 'Por comentarista',
	'sort-threads'		=> 'Threads',
	'thread'		=> 'Em resposta a %s',
	'thread-tip'		=> 'Ir para o início da conversa',
	'comments'		=> 'Comentários',
	'replies'		=> 'Respostas',
	'edit'			=> 'Editar',
	'reply'			=> 'Resposta',
	'like'			=> array ('Gostei', 'Likes'),
	'liked'			=> 'Gostou',
	'unlike'		=> 'Desgostei',
	'like-comment'		=> '\'Gostei\' deste comentário',
	'liked-comment'		=> 'Você \'Gostou\' Deste comentário',
	'dislike'		=> array ('Desgostei', 'Dislikes'),
	'disliked'		=> 'Desgostou',
	'dislike-comment'	=> '\'Desgostei\' deste comentário',
	'disliked-comment'	=> 'Você \'Desgostou\' deste comentário',
	'commenter-tip'		=> 'Você não será avisado por email.',
	'subscribed-tip'	=> 'será avisado por email.',
	'unsubscribed-tip'	=> 'não assinou para receber notificações por email',
	'first-comment'		=> 'Seja o primeiro a comentar!',
	'show-other-comments'	=> array ('Mostrar %d outro Comentário', 'Mostrar %d outro Comentários'),
	'show-number-comments'	=> array ('Mostrar %d Comentário', 'Mostrar %d Comentários'),
	'date-years'		=> array ('%d ano atrás', '%d anos atrás'),
	'date-months'		=> array ('%d mes atrás', '%d meses atrás'),
	'date-days'		=> array ('%d dia atrás', '%d dias atrás'),
	'date-today'		=> '%s hoje',
	'untitled'		=> 'Sem Título',
	'external-image-tip'	=> 'Clique para ver imagem externa',
	'loading'		=> 'Carregando...',
	'click-to-close'	=> 'Clique para fechar',
	'hashover-comments'	=> 'HashOver Comentários',
	'rss-feed'		=> 'RSS Feed',
	'source-code'		=> 'Código Fonte'
);
