<?php
/**
 * Node    :: Page
 * Created :: 2025-07-11
 * Modify  :: 2025-07-11
 * Version :: 1
 *
 * @param Array $args
 * @return Widget
 *
 * @usage extends NodeManualWidget
 */

class NodeManualWidget extends Page {
	protected $tagName = 'node,manual,';
	protected $class = 'node-manual';
	var $nodeId;
	var $right;

	function __construct(Array $args = NULL) {
		parent::__construct();
		foreach($args as $key => $value) $this->{$key} = $value;
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
					new Nav([
						'mainAxisAlignment' => 'end',
						'children' => [
							$args['uploadButton']
						], // children
					]), // Nav
					new ListTile([
						'title' => 'คู่มือ',
						'leading' => new Icon('menu_book'),
					]),
					$this->showDocs('manual', $docs),

					new ListTile([
						'title' => 'แบบฟอร์ม',
						'leading' => new Icon('menu_book'),
					]),
					$this->showDocs('form', $docs),

					new ListTile([
						'title' => 'เอกสาร',
						'leading' => new Icon('menu_book'),
					]),
					$this->showDocs('document', $docs),
				], // children
			]), // Widget
		]);
	}

	function upload(Array $args = NULL) {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'อัพโหลดเอกสาร',
				'leading' => new Icon('cloud_upload'),
				'boxHeader' => true
			]),
			'body' => new Form([
				'class' => 'sg-form -upload',
				'action' => SG\getFirst($args['action'], Url::link('api/node/info/'.$this->nodeId.'/manual.add')),
				'enctype' => 'multipart/form-data',
				'checkValid' => true,
				'rel' => 'none',
				'done' => 'close | load',
				'children' => [
					'docType' => [
						'type' => 'select',
						'class' => '-fill',
						'label' => 'ประเภทเอกสาร',
						'require' => true,
						'choice' => ['manual' => 'คู่มือ', 'form' => 'แบบฟอร์ม', 'document' => 'เอกสาร'],
					],
					'docName' => [
						'type' => 'text',
						'label' => 'ชื่อเอกสาร',
						'class' => '-fill',
						'require' => true,
						'placeholder' => 'ระบุชื่อเอกสาร',
					],
					'docFile' => [
						'type' => 'file',
						'class' => '-fill',
						'label' => 'ไฟล์เอกสาร',
						'require' => true,
					],
					'coverFile' => [
						'type' => 'file',
						'class' => '-fill',
						'label' => 'ภาพปก',
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

	private static function getCoverPhoto(Int $fileId, Array $docs) {
		$coverPhoto = NULL;

		foreach ($docs as $doc) {
			if ($doc->refId === $fileId) {
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