// Copyright (C) 2018-2019 Jacob Barkdull
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


// Wait for the page HTML to be parsed
document.addEventListener ('DOMContentLoaded', function () {
	// Get show sidebar button
	var sidebarButton = document.getElementById ('sidebar-button');

	// Get sidebar
	var sidebar = document.getElementById ('sidebar');

	// Get view links
	var viewLinks = document.getElementsByClassName ('view-link');

	// Get message element
	var message = document.getElementById ('message');

	// Execute a given function for each view link
	function eachViewLink (callback)
	{
		for (var i = 0, il = viewLinks.length; i < il; i++) {
			callback (viewLinks[i]);
		}
	}

	// Remove active class from all view links
	function clearViewTabs ()
	{
		eachViewLink (function (link) {
			link.classList.add ('view-link');
		});
	}

	// Sidebar button click event handler
	function openSidebar (event)
	{
		// Add class to show sidebar
		document.body.classList.add ('sidebar-shown');

		// Click/touch event handler
		var clickHandler = function (event)
		{
			// Do nothing if target is a sidebar element
			if (sidebar.contains (event.target)) {
				return true;
			}

			// Otherwise, remove class from body
			this.classList.remove ('sidebar-shown');

			// Remove touchend event
			document.body.removeEventListener ('touchend', clickHandler);

			// And remove click event
			this.onclick = null;
		};

		// Add touchend event to body element
		document.body.addEventListener ('touchend', clickHandler, false);

		// Add click event to body element
		document.body.onclick = clickHandler;

		// Stop event from propagation
		if (event.stopPropagation) {
			event.stopPropagation ();
		} else {
			event.cancelBubble ();
		}
	}

	// Automatically select the proper view tab on page load
	window.onload = function ()
	{
		// Remove active class from all view links
		clearViewTabs ();

		// Select active proper tab for currently loaded view
		eachViewLink (function (link) {
			var href = link.getAttribute ('href');
			var viewUrl = href.replace (/\.\.\//g, '');
			var regex = new RegExp (viewUrl);
			var frameUrl = window.location.href;

			if (regex.test (decodeURIComponent (frameUrl))) {
				link.classList.add ('active');
			}
		});
	};

	// Add click event to sidebar button
	if (sidebarButton !== null) {
		sidebarButton.onclick = openSidebar;
	}

	// Hide message after 5 seconds
	if (message !== null) {
		setTimeout (function () {
			message.className = message.className.replace (/success|error/, 'hide');
		}, 1000 * 5);
	}
}, false);
