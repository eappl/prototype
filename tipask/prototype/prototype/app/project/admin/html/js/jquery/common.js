/*
 * jQuery common Plugin
 *
 * Revision: $Id: common.js 15195 2014-07-23 07:18:26Z 334746 $
 */

(function($) {
$.checkAll = function(obj,target) {
	if ($(obj).attr('checked')) {
		$(':checkbox[name=\'' + target + '\[\]\']').attr('checked', true).parent().parent().addClass("current");
	} else {
		$(':checkbox[name=\'' + target + '\[\]\']').attr('checked', false).parent().parent().removeClass("current");
	};
};
})(jQuery);
