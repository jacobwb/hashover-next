// Convert URL to embed image HTML (embedimage.js)
HashOverConstructor.prototype.comments.embedImage = function (url)
{
	// Reference to the parent object
	var hashover = this.parent;

	// Get image extension from URL
	var urlExtension = url.split ('#')[0];
	    urlExtension = urlExtension.split ('?')[0];
	    urlExtension = urlExtension.split ('.');
	    urlExtension = urlExtension.pop ();

	// Check if the image extension is an allowed type
	if (hashover.setup['image-extensions'].indexOf (urlExtension) > -1) {
		// If so, create a wrapper element for the embedded image
		var embeddedImage = hashover.elements.create ('span', {
			className: 'hashover-embedded-image-wrapper'
		});

		// Append an image tag to the embedded image wrapper
		embeddedImage.appendChild (hashover.elements.create ('img', {
			className: 'hashover-embedded-image',
			src: hashover.setup['image-placeholder'],
			title: hashover.locale['external-image-tip'],
			alt: 'External Image',

			dataset: {
				placeholder: hashover.setup['image-placeholder'],
				url: url
			}
		}));

		// And return the embedded image HTML
		return embeddedImage.outerHTML;
	}
};
