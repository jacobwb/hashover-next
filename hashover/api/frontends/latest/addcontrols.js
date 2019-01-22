// Add various events to various elements in each comment (addcontrols.js)
HashOverLatest.prototype.addControls = function (json, popular)
{
	// Reference to this object
	var hashover = this;

	// Set onclick functions for external images
	if (this.setup['allows-images'] !== false) {
		// Main element
		var main = this.instance['main-element'];

		// Get embedded image elements
		var embeds = main.getElementsByClassName ('hashover-embedded-image');

		// Run through each embedded image element
		for (var i = 0, il = embeds.length; i < il; i++) {
			embeds[i].onclick = function () {
				hashover.openEmbeddedImage (this);
			};
		}
	}
};
