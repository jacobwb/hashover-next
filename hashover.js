// Copyright (C) 2010-2015 Jacob Barkdull
// This file is part of HashOver.
//
// HashOver is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// HashOver is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with HashOver.  If not, see <http://www.gnu.org/licenses/>.
//
//--------------------
//
// Get Complete Source Code:
//
// The source code for each PHP script used in HashOver is normally 
// accessible directly from each script. You can simply visit the script 
// file with the "source" query and the script will generate and display 
// its own source code.
//
// Like so:
//
//     http://tildehash.com/hashover/scripts/htmltag.php?source
//
// Besides that, HashOver is available as a ZIP archive:
//
//     http://tildehash.com/hashover.zip
//
// Besides that, HashOver is available on GitHub:
//
//     https://github.com/jacobwb/hashover
//
//--------------------
//
// Documentation:
//
//     http://tildehash.com/?page=hashover


(function () {
	var scripts = document.getElementsByTagName ('script');
	var scriptNumber = scripts.length;
	var thisScript = scripts[scriptNumber - 1];
	var thisQueries = thisScript.src.split ('?')[1];
	var location = window.location.href.split ('#');
	var scriptSrc = '/scripts/hashover-javascript.php';
	var scriptQueries = 'url=' + encodeURIComponent (location[0]);
	var api = false;

	// Set page URL to canonical link value if available
	if (typeof (document.querySelector) === 'function') {
		var canonical = document.querySelector ('link[rel="canonical"]');

		if (canonical !== null && canonical.href !== undefined) {
			scriptQueries = 'url=' + encodeURIComponent (canonical.href);
		}
	} else {
		// Fallback for old web browsers without querySelector
		var head = document.head || document.getElementsByTagName ('head')[0];
		var links = head.getElementsByTagName ('link');

		for (var i = 0, il = links.length; i < il; i++) {
			if (links[i].rel === 'canonical') {
				scriptQueries = 'url=' + encodeURIComponent (links[i].href);
				break;
			}
		}
	}

	// Add page title to script source
	if (document.title !== '') {
		scriptQueries += '&title=' + encodeURIComponent (document.title);
	}

	// Add additional script query strings
	if (thisQueries !== undefined) {
		thisQueries = thisQueries.split ('&');

		// Add all query strings to script source
		for (var q = 0, ql = thisQueries.length; q < ql; q++) {
			var parts = thisQueries[q].split ('=');
			var query = parts[0];
			var value = parts[1];

			if (value !== undefined) {
				if (query === 'api') {
					scriptSrc = '/api/' + value + '.php';
					api = true;
				} else {
					scriptQueries += '&' + query;
					scriptQueries += '=' + value;
				}
			}
		}
	}

	// HTML div tag for HashOver comments to appear in
	if (document.getElementById ('hashover') === null) {
		var div = document.createElement ('div');
		    div.id = 'hashover';

		// Insert HTML div tag before current script tag
		thisScript.parentNode.insertBefore (div, thisScript);
	}

	// Append HashOver JavaScript tag to the page body
	var script = document.createElement ('script');
	    script.type = 'text/javascript';
	    script.src  = '/hashover' + scriptSrc;
	    script.src += '?' + scriptQueries;
	    script.src += '&' + 'hashover-script=' + scriptNumber;
	    script.id = 'hashover-script-' + scriptNumber;
	    script.async = true;

	// Append to parent element of current script tag
	thisScript.parentNode.appendChild (script);
}) ();
