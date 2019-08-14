// Add Like/Dislike link and count to template (addratings.js)
HashOver.prototype.addRatings = function (comment, template, action, commentKey)
{
	// The opposite action
	var opposite = (action === 'like') ? 'dislike' : 'like';

	// Get instantiated prefix
	var prefix = this.prefix ();

	// Check if the comment doesn't belong to the logged in user
	if (comment['user-owned'] === undefined) {
		// Check whether this comment was liked/disliked by the visitor
		if (comment[action + 'd'] !== undefined) {
			// If so, setup indicators that comment was liked/disliked
			var className = 'hashover-' + action + 'd';
			var title = this.locale[action + 'd-comment'];
			var text = this.locale[action + 'd'];
		} else {
			// If not, setup indicators that comment can be liked/disliked
			var className = 'hashover-' + action;
			var title = this.locale[action + '-comment'];
			var text = this.locale[action];
		}

		// Append class to indicate dislikes are enabled
		if (this.setup['allows-' + opposite + 's'] === true) {
			className += ' hashover-' + opposite + 's-enabled';
		}

		// Add like/dislike link to HTML template
		template[action + '-link'] = this.strings.parseTemplate (
			this.ui[action + '-link'], {
				hashover: prefix,
				permalink: commentKey,
				class: className,
				title: title,
				text: text
			}
		);
	}

	// Check if the comment has been likes/dislikes
	if (comment[action + 's'] !== undefined) {
		// Add likes/dislikes to HTML template
		template[action + 's'] = comment[action + 's'];

		// Check if there is more than one like/dislike
		if (comment[action + 's'] !== 1) {
			// If so, use "X Likes/Dislikes" locale
			var count = comment[action + 's'] + ' ' + this.locale[action + 's'];
		} else {
			// If not, use "X Like/Dislike" locale
			var count = comment[action + 's'] + ' ' + this.locale[action];
		}
	}

	// Add like count to HTML template
	template[action + '-count'] = this.strings.parseTemplate (
		this.ui[action + '-count'], {
			hashover: prefix,
			permalink: commentKey,
			text: count || ''
		}
	);
};
