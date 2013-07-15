(function(window)
{

	function Boom()
	{
		this.init();
	}

	var p = Boom.prototype;

	p.init = function()
	{
		alert('hello');
	}

	window.Boom = Boom;

})(window);