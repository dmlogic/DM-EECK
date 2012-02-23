var eeckconf = {};
var eeckcounter = 0;

(function($) {

	// handle the display of an editor with a unique ID
	var eeck_show_editor = function(cdata){

		// get the CK config object
		var config = eeckconf[cdata.col.id];

		// make a new, unique ID for the field
		eeckcounter++;
		var myid = cdata.field.id+'_'+cdata.row.id+'_'+cdata.col.id+'_'+eeckcounter+Math.floor(Math.random()*100000000);

		// get the textarea and apply the ID
		var myfield = $('textarea', cdata.dom.$td);
		myfield.attr('id', myid);

		// replace with the editor
		CKEDITOR.replace(myid, config);
	};

	// convert a textarea to an editor on display event
	Matrix.bind('dm_eeck', 'display', eeck_show_editor);

	// now, sorting knackers the above, so we kinda have to do everything again
	// first get our current HTML back from the old CK editor
	Matrix.bind('dm_eeck', 'beforeSort', function(cdata){

		// find our textarea
		var myfield = $('textarea', cdata.dom.$td);

		// and populate it content from the editor
		myfield.val($('iframe:first', cdata.dom.$td)[0].contentDocument.body.innerHTML);
	});

	// now, re-apply a new editor
	Matrix.bind('dm_eeck', 'afterSort', function(cdata) {

		// find our textarea
		myfield = $('textarea', cdata.dom.$td);

		// zap the matrix cell and then put our textarea back in it
		cdata.dom.$td.empty().append(myfield);

		// finally, display the editor again
		eeck_show_editor(cdata);
	});

})(jQuery);