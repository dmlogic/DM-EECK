
$(function(){
	$("#cat_image").closest("p").after(	'<p><a href="#" id="eeck_cat_image_select">'+eecklang.select+'</a></p>' );
	$("#eeck_cat_image_select").live("click",function(e){
		e.preventDefault();

		// make new finder
		var eeckfinder = new CKFinder();
		eeckfinder.basePath = eeckfpath;

		eeckfinder.resourceType = eeckrestype;

		// callback function
		eeckfinder.selectActionFunction = function( fileUrl ) {

			$("#cat_image").val(fileUrl)

		};
		eeckfinder.popup();
	})

})