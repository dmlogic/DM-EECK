<?php
$name = (isset($name)) ? $name : '';
$extensions = (isset($extensions)) ? $extensions : '';
$size = (isset($size)) ? $size : '';
?>
	<div class="eeck_resource">
		<p>
			<label class="eeck_ar"><span><?=$this->lang->line('dm_eeck_resource_name')?></span>
				<input type="text" name="eeck_resource_name[]" value="<?=$name?>" style="width:150px" /></label>
				(<?=$this->lang->line('dm_eeck_alpha_only')?>)
		</p>

		<p>
			<label class="eeck_ar"><span><?=$this->lang->line('dm_eeck_upload_destination')?></span> <?=$upload_location?></label>
		</p>
		
		<p>
			<label class="eeck_ar"><span><?=$this->lang->line('dm_eeck_max_upload_size')?></span> <input type="text" name="eeck_resource_size[]" value="<?=$size?>" style="width:50px" /> Mb</label>
			(0 = <?=$this->lang->line('dm_eeck_unlimited')?>)
		</p>
		<p>
			<label><span><?=$this->lang->line('dm_eeck_allowed_extensions')?></span> <textarea name="eeck_resource_extensions[]" rows="2" cols="40"><?=$extensions?></textarea></label>
		</p>

		<p class="eeck_del_res"><a href="javascript:void(0)" ><?=$this->lang->line('dm_eeck_allowed_delete')?></a> </p>
		
	</div>