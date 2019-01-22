// Convert URL to embed image HTML (embedimage.js)
HashOverConstructor.prototype.embedImage = function (m, link, url)
{
	// Reference to this object
	var hashover = this;

	// Remove hash from image URL
	var urlExtension = url.split ('#')[0];

	// Remove queries from image URL
	urlExtension = urlExtension.split ('?')[0];

	// Get file extendion
	urlExtension = urlExtension.split ('.');
	urlExtension = urlExtension.pop ();

	// Check if the image extension is an allowed type
	if (this.setup['image-extensions'].indexOf (urlExtension) > -1) {
		// If so, create a wrapper element for the embedded image
		var embeddedImage = this.createElement ('span', {
			className: 'hashover-embedded-image-wrapper'
		});

		// Append an image tag to the embedded image wrapper
		embeddedImage.appendChild (this.createElement ('img', {
			className: 'hashover-embedded-image',
			src: this.setup['image-placeholder'],
			title: this.locale['external-image-tip'],
			alt: 'External Image',

			dataset: {
				placeholder: hashover.setup['image-placeholder'],
				url: url
			}
		}));

		// And return the embedded image HTML
		return embeddedImage.outerHTML;
	}

	// Otherwise, return original link
	return link;
};
