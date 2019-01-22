// Pre-compiled regular expressions (regex.js)
HashOverConstructor.prototype.rx = new (function () {
	this.urls		= '((http|https|ftp):\/\/[a-z0-9-@:;%_\+.~#?&\/=]+)',
	this.links		= new RegExp (this.urls + '( {0,1})', 'ig'),
	this.thread		= /^(c[0-9r]+)r[0-9\-pop]+$/,
	this.imageTags		= new RegExp ('\\[img\\](<a.*?>' + this.urls + '</a>)\\[/img\\]', 'ig'),
	this.EOLTrim		= /^[\r\n]+|[\r\n]+$/g,
	this.paragraphs		= /(?:\r\n|\r|\n){2}/g,
	this.email		= /\S+@\S+/,
	this.integer		= /^\d+$/
}) ();
