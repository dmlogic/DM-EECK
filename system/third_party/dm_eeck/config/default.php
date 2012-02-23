<?php
$name = 'Default settings file';

// this is contents of a javascript object, format as such
$editor_config = "

height: '400px'
,startupOutlineBlocks:true

,toolbar:
	[
		['Maximize', 'Source', '-',
		'Templates', '-',
		'PasteText', 'PasteFromWord', '-',
		'SpellChecker', '-', 'Undo', 'Redo', '-',
		'ShowBlocks', '-',
		'Bold', 'Italic', '-',
		'Subscript','Superscript', ],'-',
		['NumberedList', 'BulletedList','-','SpecialChar',], 
		'/',
		['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'Outdent', 'Indent','HorizontalRule', 'Blockquote', '-',
		'Link', 'Unlink', 'Image','-',
		'Format', 'Styles']
	]
	
// add styling to the editor content to match your site
//,contentsCss: '/assets/ckeditor/ckstyles.css'

// add styles like so, see http://docs.cksource.com/CKEditor_3.x/Developers_Guide/Styles
//,stylesCombo_stylesSet: 'my_styles:/assets/ckeditor/ckstyles.js'

// add templates like so, see http://docs.cksource.com/CKEditor_3.x/Developers_Guide/Templates
//,templates_files: [ '/assets/ckeditor/cktemplates.js' ]
";