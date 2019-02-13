// Changes Element.textContent onmouseover and reverts onmouseout (mouseoverchanger.js)
HashOver.prototype.mouseOverChanger = function (element, over, out)
{
	// Reference to this object
	var hashover = this;

	if (over === null || out === null) {
		element.onmouseover = null;
		element.onmouseout = null;

		return false;
	}

	element.onmouseover = function ()
	{
		this.textContent = hashover.locale[over];
	};

	element.onmouseout = function ()
	{
		this.textContent = hashover.locale[out];
	};
};
