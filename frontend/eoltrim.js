// Trims leading and trailing newlines from a string (eoltrim.js)
HashOverConstructor.prototype.EOLTrim = function (string)
{
	return string.replace (this.rx.EOLTrim, '');
};
