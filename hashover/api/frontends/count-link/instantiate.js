// Instantiate HashOver count link API (instantiate.js)
window.hashoverCountLink = new HashOverCountLink ();

// Process count links after the DOM is parsed
HashOverCountLink.onReady (function () {
	window.hashoverCountLink.processLinks ();
});
