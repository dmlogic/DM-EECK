var eeckfilecounter = 0;
var eeckvalidation = {};

(function($) {
	var eeck_show_finder = function(cdata){
		eeckfilecounter++;
		var myid = 'eeck_matrix_file_'+eeckfilecounter+Math.floor(Math.random()*100000000);
		var myfield = $('div.file-wrap', cdata.dom.$td);
		myfield.attr('id', myid);
	};
	Matrix.bind('dm_eeck_files', 'display', eeck_show_finder);
})(jQuery);