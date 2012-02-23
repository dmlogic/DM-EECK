<div id="<?=$uid?>" class="file-wrap<?php if($matrix):?> eeck_matrix<?php endif; ?>">
<div <?=$disp?> class="file_set">
<div class="eeck_remove_file" ><a href="#" title="<?=$this->lang->line('dm_eeck_remove_file')?>"><?=$this->lang->line('dm_eeck_remove_file')?></a></div>
<p class="filename <?=$fclass?>"><?php if($is_image):?><a href="<?=$is_image?>" class="dm_fancy"><?php endif; ?><img src="<?=$src?>" alt="" /><?php if($is_image):?></a><?php endif;?></p>
<?php if ($matrix) : ?>
<p class="sub_filename eeck_file_url"><?=$file_url?></p>
<?php else : ?>
<p class="sub_filename eeck_file_url"><input value="<?=$file_url?>" type="text" readonly="readonly" class="eeck_selectme" style="width:400px" /></p>
<?php endif; ?>
<p class="sub_filename eeck_file_size" <?=$fdisp?>><em><?=$this->lang->line('dm_eeck_file_size')?>: <span><?=$file_size?></span></em></p>
</div>
<p class="eeck_select eeck_toolbar">
	<a href="#" onclick="return eeckFinderPop($(this),'<?=$resource_type?>','<?=$resource_path?>',<?=$matrix?><?=$start_folder?>);"><?=$this->lang->line('dm_eeck_select_file')?></a>
	<span class="eeck_or"><?=$this->lang->line('dm_eeck_or')?></span>
	<a href="#" class="eeck_select_upload"><?=$this->lang->line('dm_eeck_upload_new')?></a>
	<input type="hidden" name="<?=$field_name?>" value="<?=$file_data?>" />
	<?php if(!$matrix):?><?=$this->lang->line('dm_eeck_file')?><?php endif; ?>

</p>
<p class="eeck_upload eeck_offscreen eeck_toolbar">
	<input  type="file" class="eeck_file_upload" title="Select <?=$resource_type?>" name="upload_<?=$field_name?>" /> <a href="#" class="eeck_upload_cancel"><?=$this->lang->line('dm_eeck_cancel')?></a>
</p>
</div>