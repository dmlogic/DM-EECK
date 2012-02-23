<style type="text/css">
.eeck-file { padding:30px; background: url(<?=$theme_path?>default.icon.gif) no-repeat center;}
<?php foreach($extensions as $key => $ext) :?>
<?php if (is_array($ext) ) : ?>
<?php foreach($ext as $e) : ?>
.eeck-file-<?=$e?> { background-image: url(<?=$theme_path?><?=$key?>.gif); }
<?php endforeach; ?>
<?php else: ?>
.eeck-file-<?=$ext?> { background-image: url(<?=$theme_path?><?=$ext?>.gif); }
<?php endif ; ?>
<?php endforeach; ?>
.eeck_remove_file a { background-image:url(<?=$ee_theme_path?>cp_themes/default/images/fancybox/fancy_close.png); }
.eeck_select a, .eeck_select_upload, .eeck_upload_cancel  { background-image: url(<?=$ee_theme_path?>cp_themes/default/images/icon-upload-file.png)}
a.eeck_select_upload { 	background-image:url(<?=$ee_theme_path?>cp_themes/default/images/icon-add.png) !important; }
a.eeck_upload_cancel { 	background-image:url(<?=$ee_theme_path?>cp_themes/default/images/list_item_site.gif) !important; }
.eeck_uploading span { background-image:url(<?=$ee_theme_path?>cp_global_images/loader.gif); }
</style>