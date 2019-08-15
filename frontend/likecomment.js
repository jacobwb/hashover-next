// For liking comments (likecomment.js)
HashOver.prototype.likeComment = function (action, permalink)
{
	// Reference to this object
	var hashover = this;

	// Get get from permalink
	var file = this.permalinkFile (permalink);

	// Get like/dislike button
	var actionLink = this.getElement (action + '-' + permalink);

	// Get likes/dislikes count element
	var likesElement = this.getElement (action + 's-' + permalink);

	// Path to like/dislike backend script
	var likePath = this.setup['http-backend'] + '/like.php';

	// Set request queries
	var queries = [
		'url=' + encodeURIComponent (this.instance['page-url']),
		'thread=' + encodeURIComponent (this.instance['thread-name']),
		'comment=' + encodeURIComponent (file),
		'action=' + action
	];

	// When loaded update like count
	this.ajax ('POST', likePath, queries, function (likeResponse) {
		// If a message is returned display it to the user
		if (likeResponse.message !== undefined) {
			alert (likeResponse.message);
			return;
		}

		// If an error is returned display a standard error to the user
		if (likeResponse.error !== undefined) {
			alert (likeResponse.error);
			return;
		}

		// Get number of likes
		var likesKey = (action !== 'dislike') ? 'likes' : 'dislikes';
		var likes = likeResponse[likesKey] || 0;

		// Check if button is marked as a like button
		if (hashover.classes.contains (actionLink, 'hashover-' + action) === true) {
			// If so, choose liked/disliked locale keys
			var title = (action === 'like') ? 'liked-comment' : 'disliked-comment';
			var content = (action === 'like') ? 'liked' : 'disliked';

			// Change class to indicate comment has been liked/disliked
			hashover.classes.add (actionLink, 'hashover-' + action + 'd');
			hashover.classes.remove (actionLink, 'hashover-' + action);

			// Change title and class to indicate comment has been liked/disliked
			actionLink.title = hashover.locale[title];
			actionLink.textContent = hashover.locale[content];

			// Add listener to change link text to "Unlike" on mouse over
			if (action === 'like') {
				hashover.mouseOverChanger (actionLink, 'unlike', 'liked');
			}
		} else {
			// If not, choose like/dislike locale keys
			var title = (action === 'like') ? 'like-comment' : 'dislike-comment';
			var content = (action === 'like') ? 'like' : 'dislike';

			// Change class to indicate comment has been unliked/undisliked
			hashover.classes.add (actionLink, 'hashover-' + action);
			hashover.classes.remove (actionLink, 'hashover-' + action + 'd');

			// Change title and class to indicate comment has been unliked/undisliked
			actionLink.title = hashover.locale[title];
			actionLink.textContent = hashover.locale[content];

			// Add listener to change link text to "Unlike" on mouse over
			if (action === 'like') {
				hashover.mouseOverChanger (actionLink, null, null);
			}
		}

		// Check if comment has likes
		if (likes > 0) {
			// If so, check if there is more than one like/dislike
			if (likes !== 1) {
				// If so, use plural like/dislike locale
				var likeLocale = (action !== 'like') ? 'dislikes' : 'likes';
			} else {
				// If not, use singlur like/dislike locale
				var likeLocale = (action !== 'like') ? 'dislike' : 'like';
			}

			// Change number of likes/dislikes
			likesElement.textContent = likes + ' ' + hashover.locale[likeLocale];

			// And set font weight bold
			likesElement.style.fontWeight = 'bold';
		} else {
			// If not, remove like count
			likesElement.textContent = '';

			// And set font weight normal
			likesElement.style.fontWeight = '';
		}
	}, true);
};
