// Root path (rootpath.js)
HashOverConstructor.rootPath = (function () {
	// Get the HashOver script source URL
	var scriptSrc = HashOverConstructor.script.getAttribute ('src');

	// Get HashOver root path
	var root = scriptSrc.replace (/\/[^\/]*\/?$/, '');

	// And return HashOver root path
	return root;
}) ();
