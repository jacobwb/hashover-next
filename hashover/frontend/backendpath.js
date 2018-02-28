// Backend path (backendpath.js)
HashOverConstructor.backendPath = (function () {
	// Get the HashOver script source URL
	var scriptSrc = HashOverConstructor.script.getAttribute ('src');

	// Parse and set HashOver path
	var root = scriptSrc.replace (/\/[^\/]*\/?$/, '');

	return root + '/backend';
}) ();
