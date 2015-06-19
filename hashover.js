// Copyright (C) 2010-2015 Jacob Barkdull
//
//	This file is part of HashOver.
//
//	HashOver is free software: you can redistribute it and/or modify
//	it under the terms of the GNU Affero General Public License as
//	published by the Free Software Foundation, either version 3 of the
//	License, or (at your option) any later version.
//
//	HashOver is distributed in the hope that it will be useful,
//	but WITHOUT ANY WARRANTY; without even the implied warranty of
//	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//	GNU Affero General Public License for more details.
//
//	You should have received a copy of the GNU Affero General Public License
//	along with HashOver.  If not, see <http://www.gnu.org/licenses/>.
//
//--------------------
//
// Get Complete Source Code
//
//	The source code for each PHP script used in HashOver is normally 
//	accessible directly from each script. You can simply visit the script 
//	file with the "source" query and the script will generate and display 
//	its own source code.
//
//	Like so:
//
//	    http://tildehash.com/hashover/scripts/htmltag.php?source
//
//	Besides that, HashOver is available as a ZIP archive:
//
//	    http://tildehash.com/hashover.zip
//
//	Besides that, HashOver is available on GitHub:
//
//	    https://github.com/jacobwb/hashover
//
//--------------------
//
// Documentation
//
//	http://tildehash.com/?page=hashover


(function () {
	var head = document.head || document.getElementsByTagName ('head')[0];
	var body = document.body || document.getElementsByTagName ('body')[0];
	var scripts = document.getElementsByTagName ('script');
	var script_number = scripts.length;
	var this_script = scripts[script_number - 1];
	var this_queries = this_script.src.split ('?')[1];
	var location = window.location.href.split ('#');
	var script_src = '/scripts/javascript-mode.php';
	var script_queries = 'url=' + encodeURIComponent (location[0]);
	var api = false;

	// Set page URL to canonical link value if available
	if (typeof (document.querySelector) === 'function') {
		var canonical = document.querySelector ('link[rel="canonical"]');

		if (canonical != null) {
			script_queries = 'url=' + encodeURIComponent (canonical.href);
		}
	} else {
		// Fallback for old web browsers without querySelector
		var links = head.getElementsByTagName ('link');

		for (var i = 0, il = links.length; i < il; i++) {
			if (links[i].rel == 'canonical') {
				script_queries = 'url=' + encodeURIComponent (links[i].href);
				break;
			}
		}
	}

	// Add page title to script source
	if (document.title != null) {
		script_queries += '&title=' + document.title;
	}

	// Add additional script query strings
	if (this_queries != null) {
		var this_queries = this_queries.split ('&');

		// Add all query strings to script source
		for (var q = 0, ql = this_queries.length; q < ql; q++) {
			var parts = this_queries[q].split ('=');
			var query = parts[0];
			var value = parts[1];

			if (query == 'api' && value != null) {
				script_src = '/api/' + value + '.php';
				api = true;
			} else {
				script_queries += '&' + query;
				script_queries += (value) ? '=' + value : '';
			}
		}
	}

	// HTML div tag for HashOver comments to appear in
	if (document.getElementById ('hashover') == null) {
		var div = document.createElement ('div');
		    div.id = 'hashover';

		// Insert HTML div tag before current script tag
		this_script.parentNode.insertBefore (div, this_script);
	}

	// Append HashOver JavaScript tag to the page body
	var script = document.createElement ('script');
	    script.type = 'text/javascript';
	    script.src  = '/hashover' + script_src;
	    script.src += '?' + script_queries;
	    script.src += '&' + 'hashover-script=' + script_number;
	    script.id = 'hashover-script-' + script_number;
	    script.async = true;

	// Append to parent element of current script tag
	if (this_script.parentNode) {
		this_script.parentNode.appendChild (script);
	} else {
		// If no parent, append to body element
		body.appendChild (script);
	}
}) ();
