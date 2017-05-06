// @licstart  The following is the entire license notice for the
// JavaScript code in this page.
//
// Copyright (C) 2010-2017 Jacob Barkdull
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
// @licend  The above is the entire license notice
// for the JavaScript code in this page.
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
	"use strict";

	var loaderScript = document.getElementById ('hashover-loader');
	var thisScript = document.currentScript || loaderScript;
	var scripts = document.getElementsByTagName ('script');
	var scriptNumber = 0;

	var location = window.location.href.split ('#');
	var scriptQueries = 'url=' + encodeURIComponent (location[0]);
	var scriptPath = '/scripts/hashover-javascript.php';
	var api = false;
	var div, script;

	// Check if we get have the script element
	if (thisScript) {
		// If so, find the script's index on the page
		for (var i = 0, il = scripts.length; i < il; i++) {
			if (scripts[i] === thisScript) {
				scriptNumber = i;
				break;
			}
		}
	} else {
		// If not, use the last script encountered
		scriptNumber = scripts.length;
		thisScript = scripts[scriptNumber - 1];
	}

	// Get URL queries
	var thisQueries = thisScript.src.split ('?')[1];

	// Simple JavaScript port of PHP's dirname
	function dirname (string)
	{
		return string.replace (/\/[^\/]*\/?$/, '');
	}

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
		var parts;
		var query;
		var value;

		// Split script URL queries by ampersands
		thisQueries = thisQueries.split ('&');

		// Add all query strings to script source
		for (var q = 0, ql = thisQueries.length; q < ql; q++) {
			parts = thisQueries[q].split ('=');
			query = parts[0];
			value = parts[1];

			if (value !== undefined) {
				if (query === 'api') {
					scriptPath = '/api/' + value + '.php';
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
		div = document.createElement ('div');
		div.id = 'hashover';

		// Insert HTML div tag before current script tag
		thisScript.parentNode.insertBefore (div, thisScript);
	}

	// Append HashOver JavaScript tag to the page body
	if (thisScript.getAttribute ('src')) {
		script = document.createElement ('script');
		script.type = 'text/javascript';
		script.src  = dirname (thisScript.getAttribute ('src')) + scriptPath;
		script.src += '?' + scriptQueries;
		script.src += '&' + 'hashover-script=' + scriptNumber;
		script.id = 'hashover-script-' + scriptNumber;
		script.async = true;

		// Append to parent element of current script tag
		thisScript.parentNode.appendChild (script);
	}
}) ();
