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

	// Get logo
	var logo = document.getElementById ('logo');

	// Get view links
	var viewLinks = document.getElementsByClassName ('view-link');

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
			link.className = 'view-link';
		});
	}

	// Add click event to sidebar button
	sidebarButton.onclick = function (event)
	{
		// Add class to show sidebar
		document.body.className = 'sidebar-shown';

		// Click/touch event handler
		var clickHandler = function (event)
		{
			// Do nothing if target is a sidebar element
			if (sidebar.contains (event.target)) {
				return true;
			}

			// Otherwise, remove class from body
			this.className = '';

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
	};

	// Automatically select the proper view tab on page load
	window.onload = function ()
	{
		// Set logo height to auto
		logo.style.height = 'auto';

		// Remove active class from all view links
		clearViewTabs ();

		// Select active proper tab for currently loaded view
		eachViewLink (function (link) {
			var regex = new RegExp (link.getAttribute ('href'));
			var frameUrl = window.location.href;

			if (regex.test (decodeURIComponent (frameUrl))) {
				link.className += ' active';
			}
		});
	};
}, false);
