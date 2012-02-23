<script type="text/javascript">
$(function(){
	$("#eeck_add_res a").click(function(){
		$("#eeck_add_res").before($("#spare_resources_field").html());
	})
	$(".eeck_del_res a").live("click",function(){
		if(confirm("<?=$this->lang->line('dm_eeck_remove_res')?>")) {
			yuk = $(this).closest("div")
			yuk.slideUp("fast",function(){yuk.remove()});

		}
	})
})
</script>

<?php $t = '<table class="mainTable padTable" border="0" cellspacing="0" cellpadding="0">'?>

<?=$t?>
	<tbody>
		<tr>
			<th colspan="2"><?=$this->lang->line('dm_eeck_ck_editor_settings')?></th>
		</tr>
		<tr>
			<td><label for="eeck_ckepath" class="eeck_lw"><?=$this->lang->line('dm_eeck_ck_editor_location')?></label></td>
			<td><?=$eeck_ckepath?></td>
		</tr>
		<tr>
			<td><label for="eeck_config_settings" class="eeck_lw"><?=$this->lang->line('dm_eeck_default_config_file')?></label></td>
			<td><?=$eeck_config_settings?></td>
		</tr>
	</tbody>
</table>

<?=$t?>
	<tbody>
		<tr>
			<th colspan="2"><?=$this->lang->line('dm_eeck_ck_finder_settings')?></th>
		</tr>
		<tr>
			<td><label for="eeck_ckfpath" class="eeck_lw"><?=$this->lang->line('dm_eeck_ck_finder_location')?></label></td>
			<td><?=$eeck_ckfpath?></td>
		</tr>
		<tr>
			<td><label for="eeck_finderskin" class="eeck_lw"><?=$this->lang->line('dm_eeck_ck_finder_skin_name')?></label></td>
			<td><?=$eeck_finderskin?>
				<p>(<?=$this->lang->line('dm_eeck_ck_finder_skin_note')?>)</p>
			</td>
		</tr>
		<tr>
			<td><label for="eeck_twidth" class="eeck_lw"><?=$this->lang->line('dm_eeck_max_twidth')?></label></td>
			<td><?=$eeck_twidth?> pixels</td>
		</tr>
		<tr>
			<td><label for="eeck_twidth" class="eeck_lw"><?=$this->lang->line('dm_eeck_max_theight')?></label></td>
			<td><?=$eeck_theight?> pixels</td>
		</tr>
		<tr>
			<td><label for="eeck_twidth" class="eeck_lw"><?=$this->lang->line('dm_eeck_max_tquality')?></label></td>
			<td><?=$eeck_tquality?> %</td>
		</tr>
	</tbody>
</table>


<?=$t?>
	<tbody>
		<tr>
			<th colspan="2"><?=$this->lang->line('dm_eeck_resource_types')?></th>
		</tr>
		<tr>
			<td>
				<?=$eeck_resourcetypes?>
				<p id="eeck_add_res"><a href="javascript:void(0)"><?=$this->lang->line('dm_eeck_add_resource_type')?></a></p>

			</td>
		</tr>
	</tbody>
</table>

<div id="spare_resources_field">
	<?=$spare_resources_field?>
</div>