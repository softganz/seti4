<?php
function admin_site_theme($self) {
	$self->theme->title='Theme select';

	if (post('cancel')) location('admin/site');

	$ret='<div class="container">';

	$theme_folder=cfg('folder.abs').cfg('theme.folder');

	$d = dir($theme_folder);
	while (false !== ($entry = $d->read())) {
		if ( in_array($entry,array('.','..')) ) continue;
		if (!is_dir($theme_folder.'/'.$entry)) continue;
		$themes[] = $entry;
	}
	$d->close();

	// Show Theme Color Palatte
	$ret.='<div class="template-view"><div class="template -color1"><span></span></div><div class="template -color2"><span></span></div><div class="template -color3"><span></span></div><div class="template -color4"><span></span></div><div class="template -color5"><span></span></div></div>';

	$ret.='<div class="row">';
	$ret .= '<div class="col -md-4">';
	$ret .= '<p>Current theme name is <strong>'.cfg('theme.name').'</strong>.</p>';
	$ret.='<p>Please select <strong>new theme or <a href="'.url('admin/site/theme/clear').'">Restore theme to default</a></strong></p>';
	$ui=new Ui(NULL,'ui-card');
	foreach ($themes as $theme) {
		$theme_thumbnail_file=$theme_folder.'/'.$theme.'/theme.thumbnail.png';
		$theme_thumbnail=_url.cfg('theme.folder').'/'.$theme.'/theme.thumbnail.png';
		$ui->add('<h3>Theme name : '.$theme.'</h3>'
			.(file_exists($theme_thumbnail_file)?'<a href="'.url('admin/site/theme/select/'.$theme).'"><img src="'.$theme_thumbnail.'" alt="'.$theme.'" width="200" /></a>':'No theme photo.')
			.'<p><a href="'.url('admin/site/theme/select/'.$theme).'">Set as default</a></p>');
	}
	$ret.=$ui->build();
	$ret.='</div><!-- col -->';

	$cfg_name='theme.'.cfg('theme.name').'.css';

	$css=post('css');

	if (isset($css) && $css!=cfg($cfg_name)) cfg_db($cfg_name,$css);
	if ($_POST) {
		if (post('version')=='') {
			cfg_db_delete('theme.stylesheet.para');
		} else {
			cfg_db('theme.stylesheet.para',post('version'));
		}
	}

	$form=new Form('css',url(q()),'edit-css');

	$form->addField(
		'version',
		array(
			'type'=>'text',
			'label'=>'Theme Version',
			'name'=>'version',
			'value'=>cfg('theme.stylesheet.para'),
		)
	);
	$form->addField(
		'css',
		array(
			'name'=>'css',
			'type'=>'textarea',
			'label'=>'CSS ที่นำมาตกแต่ง :',
			'class'=>'-fill',
			'rows'=>18,
			'value'=>htmlspecialchars(cfg($cfg_name)),
			)
		);

	$form->addField(
		'submit',
		array(
			'type'=>'button',
			'items'=>array(
				'save'=>array(
					'type'=>'submit',
					'class'=>'-primary',
					'value'=>'<i class="icon -material">done_all</i><span>Save configuration</span>'
					),
				'cancel'=>array(
					'type'=>'cancel',
					'value'=>'<i class="icon -material">cancel</i><span>Cancel</span>'
					),
				'reset'=>array(
					'type'=>'reset',
					'value'=>'<i class="icon -material">restart_alt</i><span>Reset</span>'
					),
				),
			)
		);

	$ret.='<div class="col -md-8">';
	$ret.=$form->build();
	$ret.='</div><!-- col -->';

	$ret.='</div><!-- row -->';

	$ret.='</div><!-- container -->';

	head('<style type="text/css">
	.template-view {background-color:#000;padding:16px;}
	.template {width:20%;height:128px;display:inline-block;position:relative;}
	.template>span {height:32px;display: block; bottom:0;background-color:#000;position:absolute;width:100%;text-align:center;line-height:32px;color:#fff;}
	</style>
	<script type="text/javascript">
	$(document).ready(function(){
		$(".template").each(function(i){
			var rgb = $(this).css("backgroundColor");
			rgb = rgb.match(/^rgba?[\s+]?\([\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?,[\s+]?(\d+)[\s+]?/i);
			var hex = (rgb && rgb.length === 4) ? "#" +
			("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
			("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
			("0" + parseInt(rgb[3],10).toString(16)).slice(-2) : "";
			$(this).children().text(hex.toUpperCase());
		});
	});
	</script>');
/*
.template.-color1 {background:#870D38; }
.template.-color2 {background:#DD4063; }
.template.-color3 {background:#FDF9ED; }
.template.-color4 {background:#D2E442; }
.template.-color5 {background:#1A772F; }
*/
	return $ret;
}
?>