<?php
/**
 * Node    :: Page
 * Created :: 2025-07-11
 * Modify  :: 2025-07-16
 * Version :: 10
 *
 * @param Array $args
 * @return Widget
 *
 * @usage extends NodeAlbumWidget
 */

class NodeAlbumWidget extends Page {
	protected $tagName = 'node,album,';
	protected $class = 'node-album';
	var $albumName = 'manual,form,doc,photo';
	var $nodeId;
	var $docId;
	var $right;

	function __construct(Array $args = NULL) {
		parent::__construct($args);
		unset($this->theme);
	}

	function rightToBuild() {
		return true;
	}

	#[\Override]
	function build($args = NULL) {
		$albumNames = ['manual' => 'คู่มือ', 'form' => 'แบบฟอร์ม', 'doc' => 'เอกสาร', 'photo' => 'ภาพถ่าย'];

		$docs = is_callable($args['docs']) ? $args['docs']() : NodeModel::getAlbums([
			'nodeId' => $this->nodeId,
			'tagNameLike' => $this->tagName.'%',
		]);

		return new Scaffold([
			'appBar' => $args['appBar'],
			'body' => new Container([
				'id' => 'node-album',
				'class' => $this->class,
				'attribute' => ['data-url' => $args['albumUrl']],
				'children' => array_map(
					function($albumName) use($albumNames, $docs, $args) {
						// Check invalid album name
						// debugMsg($album);
						if (!$this->validAlbumName($albumName)) return 'Invalid album name <b>'.$albumName.'</b>';
						// debugMsg($out, '$out');
						return new Widget([
							'children' => [
								new ListTile([
									'title' => SG\getFirst($albumNames[$albumName], $albumName),
									'leading' => new Icon('menu_book'),
									'trailing' => $args['uploadButton']($albumName),
								]),
								$this->showAlbum($albumName, $docs),
							]
						]);
					},
					explode(',', $this->albumName)
				), // childrn
			]), // Widget
		]);
	}

	private function validAlbumName(String $albumName = NULL) {
		return preg_match('/^[a-z0-9]{1,6}$/', $albumName);
	}

	function upload(Array $args = NULL) {
		$data = $args['data'] ? $args['data'] : NodeModel::getAlbum($this->docId, $this->nodeId);

		if ($data->tagName) {
			list(, , $albumName) = explode(',', $data->tagName);
		} else {
			$albumName = $this->albumName;
		}

		if (!$this->validAlbumName($albumName)) return error(_HTTP_ERROR_BAD_REQUEST, 'Invalid album name <b>'.$albumName.'</b>');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'อัพโหลดเอกสาร: '.$this->albumName,
				'leading' => new Icon('cloud_upload'),
				'boxHeader' => true
			]),
			'body' => new Form([
				'class' => 'sg-form -upload',
				'action' => SG\getFirst($args['action'], Url::link('api/node/info/'.$this->nodeId.'/album.save')),
				'enctype' => 'multipart/form-data',
				'checkValid' => true,
				'rel' => 'none',
				'done' => 'close | load->replace:#node-album',
				'children' => [
					'docId' => ['type' => 'hidden', 'value' => $data->docId],
					'coverId' => ['type' => 'hidden', 'value' => $data->coverId],
					'docType' => [
						'type' => $albumName ? 'hidden' : 'select',
						'class' => '-fill',
						'label' => 'ประเภทเอกสาร',
						'require' => true,
						'value' => $albumName,
						'choice' => ['manual' => 'คู่มือ', 'form' => 'แบบฟอร์ม', 'doc' => 'เอกสาร'],
					],
					'docName' => [
						'type' => 'text',
						'label' => 'ชื่อเอกสาร',
						'class' => '-fill',
						'require' => true,
						'value' => $data->title,
						'placeholder' => 'ระบุชื่อเอกสาร',
					],
					'docFile' => [
						'type' => 'file',
						'class' => '-fill',
						'label' => 'ไฟล์เอกสาร',
						'require' => $this->docId ? false : true,
					],
					'coverFile' => [
						'type' => 'file',
						'class' => '-fill',
						'label' => 'ภาพปก',
						'accept' => 'image/jpeg,image/png;capture=camcorder',
					],
					'submit' => [
						'type' => 'button',
						'class' => '-primary -fill',
						'value' => '<i class="icon -material">cloud_upload</i><span>อัพโหลดเอกสาร</span>',
						'container' => ['style' => 'margin: 32px 0 16px 0;']
					]
				], // children
			]), // Form
		]);
	}

	protected function showAlbum(String $albumName, Array $docs, Array $args = []) {
		return new Container([
			'children' => array_map(
				function($item) use($albumName, $docs, $args) {
					if ($item->tagName != $this->tagName.$albumName) return NULL;

					$docInfo = FileModel::docProperty($item->docFile, $item->docFolder);
	
					$coverUrl = NULL;
					if ($item->coverPhoto) {
						// Show photo as cover
						$coverPhotoInfo = FileModel::photoProperty($item->coverPhoto, $item->coverFolder);
						$coverUrl = $coverPhotoInfo->url;
					} else if (in_array(strtolower(sg_file_extension($item->docFile)), ['jpg', 'jpeg', 'png', 'gif'])) {
						// Show document photo as cover
						$coverPhotoInfo = FileModel::photoProperty($item->docFile, $item->docFolder);
						$coverUrl = $coverPhotoInfo->url;
					} else {
						// Show icon as cover
						$coverUrl = '//img.softganz.com/icon/pdf-icon.png';
					}

					$menu = is_callable($args['menu']) ? $args['menu']($item) : NULL;

					return new Card([
						'class' => 'sg-action -hover-parent',
						'href' => $docInfo->url,
						'attribute' => ['target' => '_blank'],
						'children' => [
							new DOM(['img', 'src' => $coverUrl, 'width' => 128, 'height' => 128, 'class' => '-cover-photo']),
							new DOM(['span', 'child' => $item->title]),
							$menu,
							// new DebugMsg($docInfo, '$docInfo'),
							// new DebugMsg($item, '$item')
						], // children
					]);
				},
				$docs
			)
		]);
	}
}
?>