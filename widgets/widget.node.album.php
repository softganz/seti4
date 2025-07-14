<?php
/**
 * Node    :: Page
 * Created :: 2025-07-11
 * Modify  :: 2025-07-14
 * Version :: 4
 *
 * @param Array $args
 * @return Widget
 *
 * @usage extends NodeAlbumWidget
 */

class NodeAlbumWidget extends Page {
	protected $tagName = 'node,album,';
	protected $class = 'node-album';
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
		$docs = is_callable($args['docs']) ? $args['docs']() : FileModel::items([
			'nodeId' => $this->nodeId,
			'tagNameLike' => $this->tagName.'%',
		])->items;

		return new Scaffold([
			'appBar' => $args['appBar'],
			'body' => new Container([
				'class' => $this->class,
				'children' => [
					new ListTile([
						'title' => 'คู่มือ',
						'leading' => new Icon('menu_book'),
						'trailing' => $args['uploadButton']('manual'),
					]),
					$this->showDocs('manual', $docs),

					new ListTile([
						'title' => 'แบบฟอร์ม',
						'leading' => new Icon('fact_check'),
						'trailing' => $args['uploadButton']('form'),
					]),
					$this->showDocs('form', $docs),

					new ListTile([
						'title' => 'เอกสาร',
						'leading' => new Icon('article'),
						'trailing' => $args['uploadButton']('doc'),
					]),
					$this->showDocs('doc', $docs),
				], // children
			]), // Widget
		]);
	}

	function upload(Array $args = NULL) {
		$data = $args['data'] ? $args['data'] : NodeModel::getAlbum($this->docId, $this->nodeId);

		if ($data->tagName) {
			list(, , $docType) = explode(',', $data->tagName);
		} else {
			$docType = Request::get('type');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'อัพโหลดเอกสาร',
				'leading' => new Icon('cloud_upload'),
				'boxHeader' => true
			]),
			'body' => new Form([
				'class' => 'sg-form -upload',
				'action' => SG\getFirst($args['action'], Url::link('api/node/info/'.$this->nodeId.'/album.save')),
				'enctype' => 'multipart/form-data',
				'checkValid' => true,
				'rel' => 'none',
				'done' => 'close | load',
				'children' => [
					'docId' => ['type' => 'hidden', 'value' => $data->docId],
					'coverId' => ['type' => 'hidden', 'value' => $data->coverId],
					'docType' => [
						'type' => $docType ? 'hidden' : 'select',
						'class' => '-fill',
						'label' => 'ประเภทเอกสาร',
						'require' => true,
						'value' => $docType,
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

	protected function showDocs(String $type, Array $docs, Array $args = []) {
		return new Container([
			'children' => array_map(
				function($item) use($type, $docs, $args) {
					if ($item->type != 'doc') return NULL;
					if ($item->tagName != $this->tagName.$type) return NULL;

					$docInfo = FileModel::docProperty($item->fileName, $item->folder);
					$coverPhoto = self::getCoverPhoto($item->id, $docs);
	
					$coverUrl = NULL;
					if ($coverPhoto) {
						$coverPhotoInfo = FileModel::photoProperty($coverPhoto->fileName, $coverPhoto->folder);
						$coverUrl = $coverPhotoInfo->url;
					} else {
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
							// new DebugMsg($coverPhoto, '$coverPhoto'),
							// new DebugMsg($docInfo, '$docInfo'),
							// new DebugMsg($item, '$item')
						], // children
					]);
				},
				$docs
			)
		]);
	}

	private static function getCoverPhoto(Int $docId, Array $docs) {
		$coverPhoto = NULL;

		foreach ($docs as $doc) {
			if ($doc->refId === $docId) {
				$coverPhoto = (Object) [
					'fileName' => $doc->fileName,
					'folder' => $doc->folder
				];
				break;
			}
		}

		return $coverPhoto;
	}
}
?>