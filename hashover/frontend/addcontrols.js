// Add various events to various elements in each comment (addcontrols.js)
HashOverConstructor.prototype.addControls = function (json, popular)
{
	// Reference to this object
	var hashover = this;

	function stepIntoReplies ()
	{
		if (json.replies !== undefined) {
			for (var reply = 0, total = json.replies.length; reply < total; reply++) {
				hashover.addControls (json.replies[reply]);
			}
		}
	}

	if (json.notice !== undefined) {
		stepIntoReplies ();
		return false;
	}

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

	// Check if collapsed comments are enabled
	if (this.setup['collapses-comments'] !== false) {
		// Get thread link of comment
		this.elements.exists ('thread-link-' + permalink, function (threadLink) {
			// Add onClick event to thread hyperlink
			threadLink.onclick = function ()
			{
				hashover.showMoreComments (threadLink, function () {
					var parentThread = permalink.replace (hashover.regex.thread, '$1');
					var scrollToElement = hashover.elements.get (parentThread, true);

					// Scroll to the comment
					scrollToElement.scrollIntoView ({ behavior: 'smooth' });
				});

				return false;
			};
		});
	}

	// Get reply link of comment
	this.elements.exists ('reply-link-' + permalink, function (replyLink) {
		// Add onClick event to "Reply" hyperlink
		replyLink.onclick = function ()
		{
			hashover.replyToComment (permalink);
			return false;
		};
	});

	// Check if the comment is editable for the user
	this.elements.exists ('edit-link-' + permalink, function (editLink) {
		// If so, add onClick event to "Edit" hyperlinks
		editLink.onclick = function ()
		{
			hashover.editComment (json);
			return false;
		};
	});

	// Check if the comment doesn't belong to the logged in user
	if (json['user-owned'] === undefined) {
		// If so, check if likes are enabled
		if (this.setup['allows-likes'] !== false) {
			// If so, check if the like link exists
			this.elements.exists ('like-' + permalink, function (likeLink) {
				// Add onClick event to "Like" hyperlinks
				likeLink.onclick = function ()
				{
					hashover.likeComment ('like', permalink);
					return false;
				};

				// And add "Unlike" mouseover event to liked comments
				if (hashover.classes.contains (likeLink, 'hashover-liked') === true) {
					hashover.mouseOverChanger (likeLink, 'unlike', 'liked');
				}
			});
		}

		// Check if dislikes are enabled
		if (this.setup['allows-dislikes'] !== false) {
			// If so, check if the dislike link exists
			this.elements.exists ('dislike-' + permalink, function (dislikeLink) {
				// Add onClick event to "Dislike" hyperlinks
				dislikeLink.onclick = function ()
				{
					hashover.likeComment ('dislike', permalink);
					return false;
				};
			});
		}
	}

	// Recursively execute this function on replies
	stepIntoReplies ();
};
