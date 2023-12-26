<?php
/**
* Paper   :: Edit Photo
* Created :: 2019-06-01
* Modify  :: 2023-12-26
* Version :: 3
*
* @param String $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/edit.photo
*/

import('widget:album.php');

class PaperEditPhoto extends Page {
	var $nodeId;
	var $photoId;
	var $right;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'nodeInfo' => $nodeInfo,
			'photoId' => post('photoId'),
			'right' => $nodeInfo->right,
		]);
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'PARAMETER ERROR');
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		return true;
	}

	function build() {
		if ($this->nodeInfo->photos) krsort($this->nodeInfo->photos);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '{tr:Photo management}',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$this->show(),
					$this->uploadTemplate(),
				], // children
			]), // Widget
		]);
	}

	function show() {
		return new Container([
			'id' => 'paper-edit-photo-show',
			'class' => 'photos',
			'data-url' => url('paper/'.$this->nodeId.'/edit.photo'._MS_.'show'),
			'children' => [
				new ListTile(['title' => 'All photo relate to this topic.', 'leading' => new Icon('photo')]),
				new Album([
					'forceBuild' => true,
					'id' => 'paper-photo',
					'class' => 'paper-photo-album',
					'upload' => new Button([
						'type' => 'default',
						'class' => 'sg-action',
						'href' => '#paper-edit-photo-upload',
						'text' => 'UPLOAD',
						'icon' => new Icon('cloud_upload'),
						'rel' => 'box',
						'attribute' => ['data-width' => '640'],
					]),
					'children' => array_map(
						function($photo) {
							// debugMsg($photo, '$photo');
							$fileInfo = FileModel::photoProperty($photo->file, $photo->folder);
							// debugMsg($fileInfo, '$fileInfo');
							return [
								'img' => $fileInfo->url,
								'id' => 'photo-id-'.$photo->fid,
								'link' => new Button([
									'class' => 'sg-action',
									'href' => url('paper/'.$this->nodeId.'/edit.photo'._MS_.'detail', ['photoId' => $photo->fid]),
									// 'href' => '#paper-edit-photo-detail',
									'rel' => 'box',
									'attribute' => ['data-width' => '640'],
								]),
								// 'navigator' => $this->right->edit ? '<a class="sg-action -hover" href="'.url('api/project/fund/info/'.$this->orgId.'/file.delete', ['fileId' => $photo->id]).'" data-rel="none" data-done="remove:parent .-hover-parent" data-title="ลบไฟล์" data-confirm="ต้องการไฟล์ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : NULL,
							];
						},
						(Array) $this->nodeInfo->photos
					),
				]), // Album
			], // children
		]);
	}

	function renderPhoto($photo) {
		$ret = '<li id="photo-id-'.$photo->fid.'" class="ui-item photo-items">';
		$ret .= '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/photo/'.$photo->fid).'" data-rel="box" data-width="640" data-height="80%"><img src="'.$photo->_src.'" height="140" /></a>';
		$ret .= '</li><!-- photo-items -->';
		return $ret;
	}

	private function uploadTemplate() {
		return new Widget([
			'tagName' => 'template',
			'id' => 'paper-edit-photo-upload',
			'child' => new TabBar([
				'class' => 'paper-upload-tabs',
				'attribute' => ['data-width' => 100],
				'children' => [
					[
						'id' => 'single',
						'active' => true,
						'action' => new Button(['href' => '#single', 'text' => tr('Upload photo')]),
						'content' => $this->uploadSingleForm(),
					],
					[
						'id' => 'multiple',
						'action' => new Button(['href' => '#multiple', 'text' => tr('Upload multiple photos')]),
						'content' => $this->uploadMultipleForm(),
					],
				], // children
			]), // TabBar
		]);
	}

	private function uploadSingleForm() {
		return new Form([
			'action' => url('api/paper/'.$this->nodeId.'/photo.add'),
			'enctype' => 'multipart/form-data',
			'id' => 'edit-topic',
			'class' => 'sg-form -upload',
			'rel' => 'notify',
			'done' => 'close | load->replace:#paper-edit-photo-show',
			'children' => [
				'photo' => [
					'type' => 'file',
					'label' => '<i class="icon -material">attach_file</i>{tr:Select photo file to upload}',
					'size' => 50,
					'container' => ['class' => 'btn -upload'],
				],
				'<div id="edit-photo-filename"></div>',
				'noRename' => user_access('administer papers') ? [
					'type' => 'checkbox',
					'options' => ['1' => 'ไม่เปลี่ยนชื่อไฟล์'],
				] : NULL,
				'title' => [
					'type' => 'text',
					'label' => '{tr:Photo title}',
					'class' => '-fill',
					'maxlength' => 150,
					'value' => $photo->title
				],
				'description' => [
					'type' => 'textarea',
					'label' => '{tr:Photo description}',
					'class' => '-fill',
					'rows' => 3,
					'value' => $photo->description
				],
				'submit' => [
					'type' => 'button',
					'value' => '<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
					'container' => '{class: "-sg-text-right"}',
				],
				$this->rule(),
			], // children
		]);
	}

	private function uploadMultipleForm() {
		return new Form([
			'action' => url('api/paper/'.$this->nodeId.'/photo.add'),
			'enctype' => 'multipart/form-data',
			'id' => 'edit-topic',
			'class' => 'sg-form -upload',
			'rel' => 'notify',
			'done' => 'close | load->replace:#paper-edit-photo-show',
			'children' => [
				'photo' => [
					'type' => 'file',
					'name' => 'photo[]',
					'label' => '<i class="icon -material">attachment</i>{tr:Select photo file to upload}',
					'size' => 50,
					'multiple' => true,
				],
				'submit' => [
					'type' => 'button',
					'value' => '<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
					'container' => '{class: "-sg-text-right"}',
				],
				$this->rule(),
			], // children
		]); // Form
	}

	private function rule() {
		return '<p><strong>ข้อกำหนดในการส่งไฟล์ภาพ</strong><ul><li>ไฟล์ภาพประเภท jpg,gif,png ขนาดไม่เกิน <strong>'.cfg('photo.max_file_size').'KB</strong></li><li>ท่านควรย่อภาพให้ได้ขนาดที่ต้องการใช้งานก่อนส่งขึ้นเว็บ</li><li>หากต้องการเพิ่มชื่อภาพ , คำอธิบายภาพ หรือ ส่งภาพเพิ่มเติม สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดภาพในภายหลัง</li><li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์ภาพทั้งหมดที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li></ul></p>';
	}

	function info() {
		if (empty($this->photoId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');

		// $ret = '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back" data-width="640" data-height="80%"><i class="icon -material">arrow_back</i></a></nav><h3>{tr:Photo information}</h3></header>';

		$info = $this->nodeInfo->photos[$this->photoId];

		return new Form([
			'variable' => 'photoinfo',
			'action' => url('api/paper/'.$this->nodeId.'/detail.update/'.$this->photoId),
			'class' => 'sg-form',
			'rel' => 'notify',
			'done' => 'back',
			'children' => [
				'fid' => ['type' => 'hidden', 'value' => $this->photoId],
				'title' => [
					'type' => 'text',
					'label' => 'ชื่อภาพ',
					'class' => '-fill',
					'value' => $info->title,
				],
				'description' => [
					'type' => 'textarea',
					'label' => 'บรรยายภาพ',
					'class' => '-fill',
					'rows' => '6',
					'value' => $info->description,
				],
				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="sg-action btn -link -cancel" href="#" data-rel="none" data-done="back"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	function detail() {
		$photoInfo = FileModel::get($this->photoId);
		if (empty($photoInfo->fileId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');
		if ($photoInfo->info->nodeId != $this->nodeId) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่ใช่ภาพของหัวข้อนี้');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => SG\getFirst($photoInfo->title, 'Photo Detail'),
				'boxHeader' => true,
				'trailing' => $this->right->edit ? new Nav([
					'children' => [
						user_access('upload photo') ? '<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('api/paper/'.$this->nodeId.'/photo.change/'.$this->photoId).'" data-rel="none" data-done="close | load->replace:#paper-edit-photo-show"><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>{tr:Change photo}</span><input type="file" name="photo" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>' : NULL,
						'<a class="sg-action btn -link" href="'.url('paper/'.$this->nodeId.'/edit.photo'._MS_.'info', ['photoId' => $this->photoId]).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">edit</i><span class="-hidden">{tr:Edit detail}</span></a>',
						'<a class="sg-action btn -link" href="'.url('api/paper/'.$this->nodeId.'/photo.delete/'.$this->photoId).'" data-title="Delete photo!!!!!" data-confirm="Delete photo -> '.htmlspecialchars($photoInfo->fileName).' <-- !!! Are you sure?" data-rel="notify" data-done="close | remove:#photo-id-'.$this->photoId.'"><i class="icon -material">delete</i><span class="-hidden">'.tr('Remove').'</span></a>',
					], // children
				]) : NULL, // Nav
			]), // AppBar
			'body' => new Container([
				'id' => 'photo-id-'.$this->photoId,
				'class' => 'photo-items',
				'children' => [
					'<a href="'.$photoInfo->property->src.'" target=_blank><img class="photo" src="'.$photoInfo->property->src.'" width="100%" /></a>',
					new ListTile(['title' => 'Photo Property']),
					'<h4>'.$photoInfo->fileName.' <small>size '.$photoInfo->property->width.'x'.$photoInfo->property->height.' pixel in '.number_format($photoInfo->property->size).' bytes</small></h4>',

					$this->right->edit ? new Table([
						'children' => [
							[
								'url short address :',
								'<input class="form-text -fill" type="text" value="'.$photoInfo->property->src.'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
							],
							[
								'url full address :',
								'<input class="form-text -fill" type="text" value="'.cfg('domain').$photoInfo->property->src.'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
							],
							[
								'HTML tag :',
								'<input class="form-text -fill" type="text" value="'.htmlspecialchars('<img src="'.$photoInfo->property->src.'" alt="'.$photoInfo->title.'" />').'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
							],
							[
								'bb code :',
								'<input class="form-text -fill" type="text" value="'.htmlspecialchars('[img]"'.$photoInfo->property->src.'"[/img]').'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
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