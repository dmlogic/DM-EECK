function eeckFinderPop(me,res,respath,matrix,folder) {

	// make new finder
	var eeckfinder = new CKFinder({"resourceType": res});
	eeckfinder.basePath = eeckfpath;
	if(typeof folder != 'undefined') {
		eeckfinder.startupPath = res+":"+folder;
	}

	// define callback data
	eeckfinder.selectActionData = {
			wrapper:me.closest("div.file-wrap").attr("id"),
			imgpath:respath,
			restype:res,
			is_matrix:matrix
			};

	// callback function
	eeckfinder.selectActionFunction = function( fileUrl, data ) {

		// find the elements we want to manipulate
		wrapper = $("#"+data["selectActionData"].wrapper);
		fileWrap = $("#"+data["selectActionData"].wrapper+" div.file_set");
		thumbWrap =  $("#"+data["selectActionData"].wrapper+" div.file_set p.filename");
		imgSrc =  $("#"+data["selectActionData"].wrapper+" div.file_set p.filename img");
		fileDesc = $("#"+data["selectActionData"].wrapper+" p.eeck_file_url");
		fileSize = $("#"+data["selectActionData"].wrapper+" p.eeck_file_size");
		myField = $("#"+data["selectActionData"].wrapper+" input[type='hidden']");

		// pass file url to required field
		if(data["selectActionData"].is_matrix == 1) {
			bits = fileUrl.split('/');
			fileDesc.text( unescape(bits.pop()) );
		} else {
			fileDesc.find("input").val( unescape(fileUrl) );
		}
		s = fileUrl+'?b='+data.fileSize+'&d='+data.fileDate;
		myField.val( s );
		fileWrap.show();

		// display the file size
		fileSize.find("span").text( data.fileSize+'k');
		fileSize.show();

		// display a thumbnail
		thumbWrap.attr("class","filename");

		// show actual thumbnail
		if( /(\.jpg|\.jpeg|\.gif|\.png|\.bmp)$/i.test(fileUrl) ) {
			thmb = fileUrl.replace(data["selectActionData"].imgpath+data["selectActionData"].restype+"/",data["selectActionData"].imgpath+"_thumbs/"+data["selectActionData"].restype+"/");
			thumbWrap.find("img").attr( "src",thmb);			

		// show file extension thumbnail
		} else {
			thumbWrap.find("img").attr( "src",eecktgif);
			bits = fileUrl.split('.');
			ext = bits.pop();
			thumbWrap.addClass("eeck-file").addClass("eeck-file-"+ext.toLowerCase());
		}

	};
	eeckfinder.popup();
	return false;
}
$(function(){

	// fancy
	$('div.file-wrap a.dm_fancy').fancybox();

	// file path selector
	$("input.eeck_selectme").live("focus",function(){
		$(this).select();
	});

	// remove file handler
	$(".eeck_remove_file a").live("click",function(e) {

		e.preventDefault();

		if(!confirm(eecklang.confirm)) {
			return false;
		}

		// get the ID
		wrap = $(this).closest("div.file-wrap");

		// clear the input
		wrap.find("input[type='hidden']").val("");

		// clear existing image
		wrap.find("img").attr( "src",eecktgif );

		// hide wrap
		$(this).closest("div.file_set").hide();

	});

	// toggle upload choices
	$(".file-wrap a.eeck_select_upload").live("click",function(e){
		e.preventDefault();
		$(this).parent("p").hide().next("p.eeck_upload").removeClass("eeck_offscreen");
	})
	$(".file-wrap a.eeck_upload_cancel").live("click",function(e){
		e.preventDefault();
		$(this).parent("p").addClass("eeck_offscreen").prev("p.eeck_select").show();
		$(this).prev("input").val("");
		$(this).parent("p").next("div.notice").remove();
	})

	// ensure form accepts files
	$('form#publishForm[enctype!=multipart/form-data]').attr('encoding', 'multipart/form-data');
	$('form#publishForm[enctype!=multipart/form-data]').attr('enctype', 'multipart/form-data');

	// entry form submit
	$('.file-wrap .eeck_file_upload').parents("form").submit( function() {

		// first validate any matrix fields
		valid = true;
		if(typeof eeckvalidation != "undefined") {

			$("div.eeck_matrix input[type='file']").each(function(){

				if($(this).val() != "") {
					bits = $(this).val().split('.');
					ext = bits.pop().toLowerCase();
					arr = $(this).attr("title").replace('Select ','');
					if($.inArray(ext,eeckvalidation[arr]) == -1) {
						$(this).parent("p").after('<div class="notice">'+eecklang.error+'</div>');
						valid = false;
					}
				}
			})
		}

		if(valid == true) {

			$('.file-wrap .eeck_file_upload:not([value!=""])').attr("disabled","disabled");

			$('.file-wrap .eeck_file_upload[value!=""]').closest("p").addClass("eeck_uploading").prepend(
			'<span>'+eecklang.progress+'</span>' );
		}

		return valid;

	});
})