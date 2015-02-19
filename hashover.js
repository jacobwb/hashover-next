(function() {
	var head = document.getElementsByTagName('head')[0];
	var body = document.getElementsByTagName('body')[0];
	var scripts = document.getElementsByTagName('script');
	var this_script = scripts[scripts.length - 1];
	var this_queries = this_script.src.split('?')[1];
	var location = window.location.href.split(window.location.hash || '#');
	var script_src = '/hashover.php';
	var queries = { 'url': encodeURIComponent(location[0]) };
	var script_queries = '';

	// Set page URL to canonical link value if available
	if (document.querySelector) {
		var canonical = document.querySelector('link[rel="canonical"]');

		if (canonical != null) {
			queries['url'] = encodeURIComponent(canonical.getAttribute('href'));
		}
	} else {
		// Fallback for old web browsers without querySelector
		var links = document.getElementsByTagName('link');

		for (var i = 0, il = links.length; i < il; i++) {
			if (links[i].rel == 'canonical') {
				queries['url'] = encodeURIComponent(links[i].href);
				break;
			}
		}
	}

	// Add page title to script source
	if (document.title != null) {
		queries['title'] = document.title;
	}

	// Add additional script query strings
	if (this_queries != null) {
		var this_queries = this_queries.split('&');

		for (var q = 0, ql = this_queries.length; q < ql; q++) {
			var parts = this_queries[q].split('=');
			var query = parts.shift();
			var value = parts.join('=');

			if (query == 'api') {
				script_src = '/api/' + value + '.php';
			} else {
				queries[query] = value;
			}
		}
	}

	// Add all query strings to script source
	for (query in queries) {
		if (script_queries != '') {
			script_queries += '&';
		}

		script_queries += query + '=' + queries[query];
	}

	// Append a HTML div tag for HashOver comments to appear in
	if (document.getElementById('hashover') == null) {
		var div = document.createElement('div');
		div.id = 'hashover';
		this_script.parentNode.insertBefore(div, this_script);
	}

	// Append HashOver JavaScript tag to the page head/body
	var script = document.createElement('script');
	script.type = 'text/javascript';
	script.async = true;
	script.src = '/hashover' + script_src + '?' + script_queries;
	(head || body).appendChild(script);
})();
