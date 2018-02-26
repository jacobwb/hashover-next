// Backend path (backendpath.js)
HashOverConstructor.backendPath = (function () {
	// Get the primary HashOver script tag
	var script = HashOverConstructor.getScript ();

	// Get the HashOver script source URL
	var scriptSrc = script.getAttribute ('src');

	// Parse and set HashOver path
	var root = scriptSrc.replace (/\/[^\/]*\/?$/, '');

	return root + '/backend';
}) ();
