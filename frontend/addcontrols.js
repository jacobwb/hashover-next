// Add various events to various elements in each comment (addcontrols.js)
HashOverConstructor.prototype.addControls = function (comment)
{
	// Reference to this object
	var hashover = this;

	// Adds the same event handlers to each comment reply
	function stepIntoReplies ()
	{
		// Check if the comment has replies
		if (comment.replies !== undefined) {
			// If so, add event handlers to each reply
			for (var i = 0, il = comment.replies.length; i < il; i++) {
				hashover.addControls (comment.replies[i]);
			}
		}
	}

	// Check if comment is a notice
	if (comment.notice !== undefined) {
		// If so, handle replies
		stepIntoReplies ();

		// And do nothing else
		return false;
	}

	// Get permalink from comment
	var permalink = comment.permalink;

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

	// Get thread link of comment
	this.elementExists ('thread-link-' + permalink, function (threadLink) {
		// Add onClick event to thread hyperlink
		threadLink.onclick = function ()
		{
			// Callback to execute after uncollapsing comments
			var callback = function ()
			{
				// Afterwards, get the parent comment permlink
				var parentThread = permalink.replace (hashover.rx.thread, '$1');

				// Get the parent comment element
				var scrollToElement = hashover.getElement (parentThread);

				// Scroll to the parent comment
				scrollToElement.scrollIntoView ({
					behavior: 'smooth',
					block: 'start',
					inline: 'start'
				});
			};

			// Check if collapsed comments are enabled
			if (hashover.setup['collapses-comments'] !== false) {
				// If so, show uncollapsed comments
				hashover.showMoreComments (threadLink, callback);
			} else {
				// If not, execute callback directly
				callback ();
			}

			return false;
		};
	});

	// Get reply link of comment
	this.elementExists ('reply-link-' + permalink, function (replyLink) {
		// Add onClick event to "Reply" hyperlink
		replyLink.onclick = function () {
			hashover.replyToComment (permalink);
			return false;
		};
	});

	// Check if the comment is editable for the user
	this.elementExists ('edit-link-' + permalink, function (editLink) {
		// If so, add onClick event to "Edit" hyperlinks
		editLink.onclick = function () {
			hashover.editComment (comment);
			return false;
		};
	});

	// Check if the comment doesn't belong to the logged in user
	if (comment['user-owned'] === undefined) {
		// If so, check if likes are enabled
		if (this.setup['allows-likes'] !== false) {
			// If so, check if the like link exists
			this.elementExists ('like-' + permalink, function (likeLink) {
				// Add onClick event to "Like" hyperlinks
				likeLink.onclick = function () {
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
			this.elementExists ('dislike-' + permalink, function (dislikeLink) {
				// Add onClick event to "Dislike" hyperlinks
				dislikeLink.onclick = function () {
					hashover.likeComment ('dislike', permalink);
					return false;
				};
			});
		}
	}

	// Recursively execute this function on replies
	stepIntoReplies ();
};
