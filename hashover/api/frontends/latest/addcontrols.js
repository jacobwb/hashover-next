// Add various events to various elements in each comment (addcontrols.js)
HashOverLatest.prototype.addControls = function (json, popular)
{
	// Reference to this object
	var hashover = this;

	// Get permalink from JSON object
	var permalink = json.permalink;

	// Set onclick functions for external images
	if (this.setup['allows-images'] !== false) {
		// Get embedded image elements
		var embeddedImgs = document.getElementsByClassName ('hashover-embedded-image');

		for (var i = 0, il = embeddedImgs.length; i < il; i++) {
			embeddedImgs[i].onclick = function ()
			{
				hashover.openEmbeddedImage (this);
			};
		}
	}
};
