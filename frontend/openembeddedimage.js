// Callback to close the embedded image (openembeddedimage.js)
HashOverConstructor.prototype.closeEmbeddedImage = function (image)
{
	// Reset source
	image.src = image.dataset.placeholder;

	// Reset title
	image.title = this.locale['external-image-tip'];

	// Remove loading class from wrapper
	this.classes.remove (image.parentNode, 'hashover-loading');
};

// Onclick callback function for embedded images (openembeddedimage.js)
HashOverConstructor.prototype.openEmbeddedImage = function (image)
{
	// Reference to this object
	var hashover = this;

	// If embedded image is open, close it and return false
	if (image.src === image.dataset.url) {
		this.closeEmbeddedImage (image);
		return false;
	}

	// Set title
	image.title = this.locale['loading'];

	// Add loading class to wrapper
	this.classes.add (image.parentNode, 'hashover-loading');

	// Change title and remove load event handler once image is loaded
	image.onload = function ()
	{
		// Set title to "Click to close" locale
		image.title = hashover.locale['click-to-close'];

		// Remove loading class from wrapper
		hashover.classes.remove (image.parentNode, 'hashover-loading');

		// Remove load event handler
		image.onload = null;
	};

	// Close embedded image if any error occurs
	image.onerror = function () {
		hashover.closeEmbeddedImage (this);
	};

	// Set placeholder image to embedded source
	image.src = image.dataset.url;
};
