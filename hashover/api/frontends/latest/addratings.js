// Add Like/Dislike link and count to template (addratings.js)
HashOverLatest.prototype.addRatings = function (comment, template, action, commentKey)
{
	// Check if the comment has been likes/dislikes
	if (comment[action + 's'] !== undefined) {
		// Add likes/dislikes to HTML template
		template[action + 's'] = comment[action + 's'];

		// Get "X Like/Dislike(s)" locale
		var plural = (comment[action + 's'] === 1 ? 0 : 1);
		var count = comment[action + 's'] + ' ' + this.locale[action][plural];
	}

	// Add like count to HTML template
	template[action + '-count'] = this.strings.parseTemplate (
		this.ui[action + '-count'], {
			permalink: commentKey,
			text: count || ''
		}
	);
};
