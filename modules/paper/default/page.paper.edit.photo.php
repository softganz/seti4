<?php
/**
* Paper   :: Edit Photo
* Created :: 2019-06-01
* Modify  :: 2023-07-24
* Version :: 2
*
* @param String $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/edit.photo
*/

import('widget:album.php');
import('model:file.php');

class PaperEditPhoto extends Page {
	var $nodeId;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'nodeInfo' => $nodeInfo
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'PARAMETER ERROR');

		// if ($fileId) {
		// 	return __paper_edit_photo_render($nodeInfo, $nodeInfo->photos[$fileId]);
		// }

		// $ret .= '<style type="text/css">
		// .ui-card.album>.ui-item {width: 200px; height: 140px; overflow: hidden;}
		// .photos .widget-table>tbody>tr>td:first-child {white-space: nowrap}
		// .photos .widget-table>tbody>tr>td:nth-child(2) {width: 100%;}
		// </style>';

		// $ret .= '<script type="text/javascript">
		// $("#edit-photo").change(function() {
		// 	var f = $(this).val().replace(/.*[\/\\\\]/, "")
		// 	$("#edit-photo-filename").text("เลือกไฟล์ : "+f)
		// })
		// </script>';

		if ($this->nodeInfo->photos) krsort($this->nodeInfo->photos);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => '{tr:Photo management}',
				'trailing' => '<a class="sg-action btn -primary" href="'.url('paper/'.$this->nodeId.'/edit.photo.add').'" data-rel="box" data-width="640" data-height="640"><i class="icon -material">cloud_upload</i><span>{tr:UPLOAD NEW PHOTO}</span></a>',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'body' => new Container([
				'class' => 'photos',
				'children' => [
					'<p><strong>All photo relate to this topic</strong></p>',
					new Album([
						'forceBuild' => true,
						'id' => 'paper-photo',
						'class' => 'paper-photo-album',
						// 'itemClass' => '-hover-parent',
						// 'upload' => $this->right->edit ? new Form([
						// 	'class' => 'sg-upload',
						// 	'enctype' => 'multipart/form-data',
						// 	'action' => url('api/project/fund/info/'.$this->orgId.'/upload', ['tagName' => $this->tagName, 'refId' => $this->budgetYear, 'fileNameLength' => 25]),
						// 	'rel' => '#financialplan-photo',
						// 	'attribute' => ['data-after' => 'li'],
						// 	'children' => [
						// 		'<span class="btn fileinput-button"><i class="icon -material">cloud_upload</i><span>อัพโหลดแผนการเงิน</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/jpeg" /></span><input class="-hidden" type="submit" value="upload" />'
						// 	], // children
						// ]) : NULL, // Form

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
										'href' => url('paper/'.$this->nodeId.'/photo/'.$photo->fid),
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
			]), // Container
		]);
	}

	function renderPhoto($photo) {
		$ret = '<li id="photo-id-'.$photo->fid.'" class="ui-item photo-items">';
		$ret .= '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/photo/'.$photo->fid).'" data-rel="box" data-width="640" data-height="80%"><img src="'.$photo->_src.'" height="140" /></a>';
		$ret .= '</li><!-- photo-items -->';
		return $ret;
	}
}
?>