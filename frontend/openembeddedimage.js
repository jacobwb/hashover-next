// Callback to close the embedded image (openembeddedimage.js)
HashOverConstructor.prototype.closeEmbeddedImage = function (image)
{
	// Set image load event handler
	image.onload = function ()
	{
		// Reset title
		this.title = hashover.locale['external-image-tip'];

		// Remove loading class from wrapper
		hashover.classes.remove (this.parentNode, 'hashover-loading');

		// Remove open class from wrapper
		hashover.classes.remove (this.parentNode, 'hashover-embedded-image-open');

		// Remove load event handler
		this.onload = null;
	};

	// Reset source
	image.src = image.dataset.placeholder;
};

// Onclick callback function for embedded images (openembeddedimage.js)
HashOverConstructor.prototype.openEmbeddedImage = function (image)
{
	// Reference to this object
	var hashover = this;

	// Check if embedded image is open
	if (image.src === image.dataset.url) {
		// If so, close it
		this.closeEmbeddedImage (image);

		// And return void
		return;
	}

	// Set title
	image.title = this.locale['loading'];

	// Add loading class to wrapper
	this.classes.add (image.parentNode, 'hashover-loading');

	// Set image load event handler
	image.onload = function ()
	{
		// Set title to "Click to close" locale
		this.title = hashover.locale['click-to-close'];

		// Remove loading class from wrapper
		hashover.classes.remove (this.parentNode, 'hashover-loading');

		// Add open class to wrapper
		hashover.classes.add (this.parentNode, 'hashover-embedded-image-open');

		// Remove load event handler
		this.onload = null;
	};

	// Close embedded image if any error occurs
	image.onerror = function () {
		hashover.closeEmbeddedImage (this);
	};

	// Set placeholder image to embedded source
	image.src = image.dataset.url;
};
