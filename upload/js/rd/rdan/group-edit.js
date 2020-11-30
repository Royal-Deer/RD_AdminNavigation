/** @param {jQuery} $ jQuery Object */
!function($, window, document)
{
	"use strict";

	XF.RDANExpandAll = XF.Click.newHandler({
		eventNameSpace: 'RDANExpandAll',
		options: {},
		$container: null,
		init: function()
		{
			this.$container = $('.nestable-container');
		},

		click: function(e)
		{
			e.preventDefault();
			this.$container.nestable('expandAll');
		}
	});

	XF.RDANCollapseAll = XF.Click.newHandler({
		eventNameSpace: 'RDANCollapseAll',
		options: {},
		$container: null,
		init: function()
		{
			this.$container = $('.nestable-container');
		},

		click: function(e)
		{
			e.preventDefault();
			this.$container.nestable('collapseAll');
		}
	});

	// ################################## --- ###########################################

	XF.Click.register('expand-all', 'XF.RDANExpandAll');
	XF.Click.register('collapse-all', 'XF.RDANCollapseAll');
}
(jQuery, window, document);
