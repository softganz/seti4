<?php
/**
* Paper   :: Show Photo Information
* Created :: 2019-01-01
* Modify  :: 2023-07-24
* Version :: 2
*
* @param String $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/photo
*/

class PaperPhoto extends Page {
	var $nodeId;
	var $photoId;
	var $photoInfo;
	var $right;
	var $nodeInfo;

	function __construct($nodeInfo = NULL, $photoId = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'photoId' => $photoId,
			'photoInfo' => $photoId ? $nodeInfo->photos[$photoId] : NULL,
			'nodeInfo' => $nodeInfo,
			'right' => (Object) [
				'edit' => $nodeInfo->RIGHT & _IS_EDITABLE,
			],
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'PARAMETER ERROR');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => SG\getFirst($this->photoInfo->title, 'Photo'),
				'boxHeader' => true,
				'trailing' => $this->right->edit ? new Nav([
					'children' => [
						user_access('upload photo') ? '<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('api/paper/'.$this->nodeId.'/photo.change/'.$this->photoInfo->fid).'" data-rel="refresh" data-done="close"><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>{tr:Change photo}</span><input type="file" name="photo" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>' : NULL,
						'<a class="sg-action btn -link" href="'.url('paper/'.$this->nodeId.'/edit.photo.info/'.$this->photoInfo->fid).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">edit</i><span class="-hidden">{tr:Edit detail}</span></a>',
						'<a class="sg-action btn -link" href="'.url('api/paper/'.$this->nodeId.'/photo.delete/'.$this->photoInfo->fid).'" data-title="Delete photo!!!!!" data-confirm="Delete photo -> '.htmlspecialchars($this->photoInfo->file).' <-- !!! Are you sure?" data-rel="notify" data-done="close | remove:#photo-id-'.$this->photoInfo->fid.'"><i class="icon -material">delete</i><span class="-hidden">'.tr('Remove').'</span></a>',
					], // children
				]) : NULL, // Nav
			]), // AppBar
			'body' => new Container([
				'id' => 'photo-id-'.$this->photoInfo->fid,
				'class' => 'photo-items',
				'children' => [
					'<a href="'.$this->photoInfo->url.'" target=_blank><img class="photo" src="'.$this->photoInfo->url.'" width="100%" /></a>',
					new ListTile(['title' => 'Photo Property']),
					'<h4>'.$this->photoInfo->file.' <small>size '.$this->photoInfo->width.'x'.$this->photoInfo->height.' pixel in '.number_format($this->photoInfo->size).' bytes</small></h4>',

					$this->right->edit ? new Table([
						'children' => [
							[
								'url short address :',
								'<input class="form-text -fill" type="text" value="'.$this->photoInfo->url.'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
							],
							[
								'url full address :',
								'<input class="form-text -fill" type="text" value="'.cfg('domain').$this->photoInfo->url.'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
							],
							[
								'HTML tag :',
								'<input class="form-text -fill" type="text" value="'.htmlspecialchars('<img src="'.$this->photoInfo->url.'" alt="'.$this->photoInfo->title.'" />').'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
							],
							[
								'bb code :',
								'<input class="form-text -fill" type="text" value="'.htmlspecialchars('[img]"'.$this->photoInfo->url.'"[/img]').'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
							],
						], // children
					]) : NULL, // Table

					// new DebugMsg($this->photoInfo, '$this->photoInfo'),

					'<style type="text/css">
					.photo-items .photo {display: block; margin: 0 auto 16px;}
					.photo-items .widget-table>tbody>tr>td:first-child {white-space: nowrap; text-align: right;}
					.photo-items .widget-table>tbody>tr>td:nth-child(2) {width: 100%;}
					</style>',
				], // children
			]), // Container
		]);
	}
}
?>