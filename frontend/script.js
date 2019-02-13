// Get the current HashOver script tag (script.js)
HashOverConstructor.script = (function () {
	// Get various scripts
	var loaderScript = document.getElementById ('hashover-loader');
	var scripts = document.getElementsByTagName ('script');

	// Use either the current script or an identified loader script
	var currentScript = document.currentScript || loaderScript;

	// Otherwise, fallback to the last script encountered
	return currentScript || scripts[scripts.length - 1];
}) ();
