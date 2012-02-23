<fieldset>
<p>
	<label><strong><?=$this->lang->line('dm_eeck_current_resource_type')?></strong>
		<select id="eeck_res" style="padding:2px 4px">
			<?php foreach($restypes as $type) :
				$sel = ($type == $selected_type) ? 'selected="selected"' : ''; ?>
			<option value="<?=$type?>" <?=$sel?>><?=$type?></option>
			<?php endforeach ?>
		</select>
	</label>
</p>
</fieldset>
<div id="finder">

</div>

<script type="text/javascript">
var finder = new CKFinder();

// This is a sample function which is called when a file is selected in CKFinder.
function showFileInfo( fileUrl, data ) {
	var msg = '<?=$this->lang->line('dm_eeck_selected_url')?>: <a href="' + fileUrl + '">' + fileUrl + '</a><br /><br />';
	// Display additional information available in the "data" object.
	// For example, the size of a file (in KB) is available in the data["fileSize"] variable.
	if ( fileUrl != data['fileUrl'] )
		msg += '<b><?=$this->lang->line('dm_eeck_file_url')?>:</b> ' + data['fileUrl'] + '<br />';
	msg += '<b><?=$this->lang->line('dm_eeck_file_size')?>:</b> ' + data['fileSize'] + 'KB<br />';
	msg += '<b><?=$this->lang->line('dm_eeck_last_modified')?>:</b> ' + data['fileDate'];

	// this = CKFinderAPI object
	this.openMsgDialog( "<?=$this->lang->line('dm_eeck_selected_file')?>", msg );
}

function draw_editor(res) {

	if(typeof res == 'undefined') {
		res =  '<?=$selected_type?>';
	}

	$("#finder").html("");

	conf = {
		selectActionFunction: showFileInfo,
		height: 600,
		resourceType: res
	}
	finder.appendTo("finder",conf);

}


$(function(){
	draw_editor();
	
	$("#eeck_res").change(function(){
		draw_editor($(this).val() );
	})
})
</script>
