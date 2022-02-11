<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ad_view($self, $adId = NULL) {
	$adInfo = ad_model::get_ad_by_id($adId);
	if ($adInfo->_empty) return message('error','Data not found');

	$isEdit = user_access('administer contents,administer papers','edit own paper',$adInfo->uid);

	$self->theme->title = $adInfo->title;

	if ($isEdit) {
		user_menu('edit','<i class="icon -edit"></i>','#');
		user_menu('edit','edit_detail','Edit advertisment detail',url('ad/edit/'.$adInfo->aid.'/detail'),'rel=main');
		user_menu('edit','add_photo','Change image/photo',url('ad/edit/'.$adInfo->aid.'/photo'),'{class: "sg-action", data-rel:"main"}');

		user_menu('edit','edit_default','Make to <strong>'.($adInfo->default=='yes'?'normal':'default').'</strong>',url('ad/edit/'.$adInfo->aid.'/default'));
		user_menu('edit','edit_activate',($adInfo->active=='yes'?'Deactivate':'Activate').' this advertisment',url('ad/edit/'.$adInfo->aid.'/activate'));

		user_menu('edit','edit_deletephoto','Delete image/photo',url('ad/edit/'.$adInfo->aid.'/deletephoto'),'attr=class="sg-action" data-rel="#main" data-ret="'.url('ad').'" data-confirm="Delete image/photo. Please confirm?"');
		user_menu('edit','edit_delete','Delete this advertisment',url('ad/edit/'.$adInfo->aid.'/delete'),'attr=class="sg-action" data-rel="#main" data-confirm="Delete this advertisment. Please confirm?"');
		// data-ret="'.url('ad').'"
	}

	user_menu('home',tr('home'),url());
	user_menu('ad',tr('ad'),url('ad'));
	user_menu('id',$adInfo->aid,url('ad/'.$adInfo->aid));
	if (user_access('create ad content')) user_menu('new',tr('Create new advertisment'),url('ad/post'));
	$self->theme->navigator=user_menu();

	if ($adInfo->file) $ret.='<div class="photo">'.ad_model::__show_img_str($adInfo).'</div>';


	$tables = new Table();
	$tables->rows[]=array('Ad Location',$adInfo->location);
	$tables->rows[]=array('From ',$adInfo->start.' to '.$adInfo->stop);
	$tables->rows[]=array('Link to url',$adInfo->url);
	$tables->rows[]=array('File',$adInfo->file);
	$tables->rows[]=array('Width x Height',$adInfo->width.' x '.$adInfo->height.' pixels');
	$tables->rows[]=array('Views',$adInfo->views);
	$tables->rows[]=array('Clicks',$adInfo->clicks);
	$tables->rows[]=array('Status',$adInfo->default=='yes'?'This is default ad on <strong>'.$adInfo->location.'</stronf>':'');
	$tables->rows[]=array('Status','This ad is <strong>'.($adInfo->active=='yes'?'Active':'Inactive').'</strong>');
	$tables->rows[]=array('',$adInfo->body?'<strong>Advertisment body</strong><p>'.sg_text2html($adInfo->body).'</p>':'');

	$ret .= $tables->build();
	return $ret;
}
?>