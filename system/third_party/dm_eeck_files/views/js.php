var eeckfpath = "<?php echo $eeck_ckfpath?>";
var eecktgif = "<?php echo $tgif?>";
<?php if(isset($eeck_restype)):?>var eeckrestype = "<?php echo $eeck_restype?>";<?php endif;?>
var eecklang = {
	confirm: "<?php echo $this->lang->line('dm_eeck_remove_file')?>?",
	progress: "<?php echo $this->lang->line('dm_eeck_upload_in_progress')?>",
	error: "<?php echo $this->lang->line('dm_eeck_upload_error6')?>",
	select: "<?php echo $this->lang->line('dm_eeck_select_file_long')?>"
}