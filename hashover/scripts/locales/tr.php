<?php

// Copyright (C) 2015-2017 Jacob Barkdull
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

// Turkish text for forms, buttons, links, and tooltips
$locale = array (
	'comment-form'		=> 'Yorumunuzu buraya yazın...',
	'reply-form'		=> 'Cevabınızı buraya yazın...',
	'form-tip'		=> 'Accepted HTML: &lt;b&gt;, &lt;u&gt;, &lt;i&gt;, &lt;s&gt;, &lt;pre&gt;, &lt;ul&gt;, &lt;ol&gt;, &lt;li&gt;, &lt;blockquote&gt;, &lt;code&gt; escapes HTML, URLs automagically become links, and [img]URL here[/img] will display an external image.',
	'post-button'		=> 'Gönder',
	'login'			=> 'Giriş Yap',
	'login-tip'		=> 'Giriş Yap (opsiyonel)',
	'logout'		=> 'Çıkış Yap',
	'be-first-name'		=> 'Henüz yorumu yok.',
	'pending-name'		=> 'Bekleyen...',
	'deleted-name'		=> 'Silindi...',
	'error-name'		=> 'Hata...',
	'be-first-note'		=> 'İlk yorumu siz yapın!',
	'pending-note'		=> 'Bu yorum onayı bekliyor.',
	'deleted-note'		=> 'Bu yorum silindi.',
	'error-note'		=> 'Bir şeyler yanlış gitti. Bu yorumu alınamadı.',
	'options'		=> 'Ayarlar',
	'cancel'		=> 'İptal',
	'reply-to-comment'	=> 'Yorumu cevapla',
	'edit-your-comment'	=> 'Yorumunuzu değiştirin',
	'optional'		=> 'Opsiyonel',
	'required'		=> 'Gerekiyor',
	'name'			=> 'İsim',
	'name-tip'		=> 'İsim (%s)',
	'password'		=> 'Şifre',
	'password-tip'		=> 'Şifre (%s, Düzenlemek veya bu yorumunu silmek için izin verir)',
	'confirm-password'	=> 'Şifreyi Onayla',
	'email'			=> 'E-posta adresi',
	'email-tip'		=> 'E-posta adresi (%s, e-posta ile uyarılar için)',
	'website'		=> 'Website',
	'website-tip'		=> 'Website (%s)',
	'logged-in'		=> 'Giriş yaptınız!',
	'logged-out'		=> 'Çıkış yaptınız!',
	'comment-needed'	=> 'Düzgün bir yorum olmadı bu? Lütfen aşağıdaki formu kullanın.',
	'reply-needed'		=> 'Düzgün bir cevap olmadı bu? Lütfen aşağıdaki formu kullanın.',
	'field-needed'		=> '"%s" alanı gerekiyor.',
	'post-fail'		=> 'Gönderirken hatalar oluştu. Yeterli izinlere sahip değilsiniz.',
	'comment-deleted'	=> 'Yorum silindi!',
	'post-reply'		=> 'Cevapla',
	'delete'		=> 'Sil',
	'subscribe'		=> 'Cevaplardan beni haberdar edin',
	'subscribe-tip'		=> 'E-posta uyarılarına kayıt ol',
	'edit-comment'		=> 'Yorumu değiştir',
	'status'		=> 'Durum',
	'status-approved'	=> 'Onaylı',
	'status-pending'	=> 'Onay bekleyen',
	'status-deleted'	=> 'İşaretli silindi',
	'save-edit'		=> 'Değişiklikleri kaydet',
	'no-email-warning'	=> 'E-posta girişi yapmanız yorumunuza gelen cevaplara uyarı alamayacaksınız.',
	'invalid-email'		=> 'Girdiğiniz e-posta adresi geçerli değil.',
	'delete-comment'	=> 'Bu yorumu silmek istediğinizden emin misiniz',
	'post-comment-on'	=> array ('Bir yorum yap', 'Bir yorum yap on "%s"'),
	'popular-comments'	=> array ('En popüler yorum', 'En popüler yorumlar'),
	'showing-comments'	=> array ('%d tane yorum gösteriliyor', '%d yorum gösteriliyor'),
	'count-link'		=> array ('%d yorum', '%d yorum'),
	'count-replies'		=> array ('%d cevap', '%d cevap'),
	'sort'			=> 'Sırala',
	'sort-ascending'	=> 'Sırayla',
	'sort-descending'	=> 'Ters',
	'sort-by-date'		=> 'Yeni olan önce',
	'sort-by-likes'		=> 'Beğenme göre',
	'sort-by-replies'	=> 'Cevapların göre',
	'sort-by-discussion'	=> 'Tartışma göre',
	'sort-by-popularity'	=> 'Popülariteye göre',
	'sort-by-name'		=> 'Yorum yapana göre',
	'sort-threads'		=> 'Ipler',
	'thread'		=> '%s\'a cevaben',
	'thread-tip'		=> 'Konunun başına dön',
	'comments'		=> 'Yorum',
	'replies'		=> 'Cevap',
	'edit'			=> 'Düzenle',
	'reply'			=> 'Cevap',
	'like'			=> array ('Beğenme', 'Beğenme'),
	'liked'			=> 'Beğendi',
	'unlike'		=> 'Unlike',
	'like-comment'		=> 'Bu yorumu \'beğen\'',
	'liked-comment'		=> 'Bu yorumu \'beğendiniz\'',
	'dislike'		=> array ('Hoşlanmayan', 'Hoşlanmayan'),
	'disliked'		=> 'Beğenmedi',
	'dislike-comment'	=> 'Bu yorumu \'beğenme\' ',
	'disliked-comment'	=> 'Bu yorumu  \'beğenmediniz\'',
	'commenter-tip'		=> 'E-posta ile bilgilendirilmeyeceksiniz',
	'subscribed-tip'	=> 'e-posta ile bilgilendirilecek.',
	'unsubscribed-tip'	=> 'e-posta ile bilgilendirmeye kayıt olmamış',
	'show-other-comments'	=> array ('Diğer %d yorumu göster', 'Diğer %d yorumu göster'),
	'show-number-comments'	=> array ('Diğer %d yorumu göster', 'Diğer %d yorumu göster'),
	'date-years'		=> array ('%d yıl önce', '%d yıl önce'),
	'date-months'		=> array ('%d ay önce', '%d ay önce'),
	'date-days'		=> array ('%d gün önce', '%d gün önce'),
	'date-today'		=> '%s bugün',
	'untitled'		=> 'Başlıksız',
	'external-image-tip'	=> 'Harici resmi görüntülemek için tıklayın',
	'loading'		=> 'Yükleniyor...',
	'click-to-close'	=> 'Kapatmak için tıklayın',
	'hashover-comments'	=> 'HashOver Yorum',
	'rss-feed'		=> 'RSS Kaynağı',
	'source-code'		=> 'Kaynak Kodu'
);
