// Generate file from permalink (permalinkfile.js)
HashOverConstructor.prototype.permalinkFile = function (permalink)
{
	// Remove leading 'c'
	var file = permalink.slice (1);

	// Replace 'r' by '-'
	file = file.replace (/r/g, '-');

	// Remove "-pop" if present
	file = file.replace ('-pop', '');

	return file;
};
