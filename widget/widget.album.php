<?php
/**
 * Widget   :: Album
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-10-09
 * Modified :: 2026-11-12
 * Version  :: 8
 *
 * @param Array $args
 *
 * @uses new Album([
 * 	'id' => String,
 * 	'class' => String,
 * 	'img' => String,
 * 	'link' => Widget,
 * 	'title' => Widget,
 * 	'itemClass' => String,
 * 	'navigator' => Widget,
 * 	'children' => [],
 * ])
 */

class Album extends Widget {
	protected string $widgetName = 'Album';
	public $tagName = 'ul';
	public ?array $childContainer = ['tagName' => 'li', 'class' => '-item'];
	public bool $forceBuild;
	public mixed $img;
	public mixed $title;
	public mixed $navigator;
	public mixed $upload;

	function __construct(
		$args = [],
		?string $id = null,
		?string $class = null,
		?string $style = null,
		?array $children = [],
		mixed $img = null,
		mixed $title = null,
		mixed $navigator = null,
		bool $forceBuild = false,
		?string $itemClass = null,
		mixed $upload = null
	) {
		parent::__construct(
			array_replace(
				[
					'id' => $id,
					'class' => $class,
					'style' => $style,
					'children' => $children,
					'img' => $img,
					'title' => $title,
					'navigator' => $navigator,
					'forceBuild' => $forceBuild,
					'itemClass' => $itemClass,
					'upload' => $upload
				],
				$args
			)
		);
	}
	// new Album([
	// 	'forceBuild' => true,
	// 	'id' => 'tor-photo',
	// 	'class' => 'fund-album -sg-paddingmore',
	// 	'itemClass' => '-hover-parent',
	// 	'upload' => $this->right->edit ? new Form([
	// 		'class' => 'sg-upload',
	// 		'enctype' => 'multipart/form-data',
	// 		'action' => Url::link('api/project/' . $this->projectId . '/info..file.upload'),
	// 		'rel' => '#tor-photo',
	// 		'attribute' => ['data-after' => 'li'],
	// 		'children' => [
	// 			'folder' => ['type' => 'hidden', 'value' => 'tor'],
	// 			'tagName' => ['type' => 'hidden', 'value' => 'TOR'],
	// 			'title' => ['type' => 'hidden', 'value' => 'บันทึกข้อตกลง'],
	// 			'debug' => debug('upload') ? ['type' => 'hidden', 'value' => 'upload'] : null,
	// 			'<span class="btn fileinput-button"><i class="icon -material">cloud_upload</i><span>อัพโหลดบันทึกข้อตกลง</span><input type="file" name="file" class="inline-upload" accept=".jpg, .jpeg, image/jpeg, .pdf, application/pdf" /></span><input class="-hidden" type="submit" value="upload" />'
	// 		], // children
	// 	]) : NULL, // Form
	// 	'children' => array_map(
	// 		function($photo) use ($inputName, $criteria, $tagName) {
	// 			$isPhoto = $photo->type === 'photo';
	// 			if ($isPhoto) {
	// 				$fileInfo = FileModel::photoProperty($photo->fileName, $photo->folder);
	// 			} else {
	// 				$fileInfo = FileModel::docProperty($photo->fileName, $photo->folder);
	// 			}
	// 			return [
	// 				'img' => $photo->type == 'photo' ? $fileInfo->src : '//img.softganz.com/icon/icon-file.png',
	// 				'link' => new Button([
	// 					'class' => $isPhoto ? 'sg-action' : NULL,
	// 					'href' => $fileInfo->src,
	// 					'rel' => $isPhoto ? 'img' : NULL,
	// 					'target' => $isPhoto ? NULL : '_blank',
	// 				]),
	// 				// 'title' => 'Book Bank Photo',
	// 				'navigator' => $this->right->edit ? new Nav([
	// 					'class' => '-hover',
	// 					'child' => '<a class="sg-action" href="' . Url::link('api/project/' . $this->projectId . '/info..file.delete/' . $photo->id) . '" data-rel="none" data-done="remove:parent .-hover-parent" data-title="ลบไฟล์" data-confirm="ต้องการไฟล์ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>'
	// 					]) : NULL,
	// 			];
	// 		},
	// 		FileModel::items([
	// 			'orgId' => $this->orgId,
	// 			'tagName' => 'PROJECT,TOR',
	// 			'orderDir' => 'DESC'
	// 		])->items
	// 	), // children
	// ]), // Album
	// Container for each child of children
	// @override
	protected function renderChildContainerStart($key, $args = [], $child = []) {
		if (is_object($child)) {
			$args['class'] = $child->class;
			$args['id'] = $child->id;
		} else {
			$args['class'] = $child['class'] ?? null;
			$args['id'] = $child['id'] ?? null;
		}
		return parent::renderChildContainerStart($key, $args, $child);
	}

	#[\Override]
	protected function renderEachChildWidget(
		$widget,
		$key = null,
		$callbackFunction = [],
		$options = []
	) {
		if (is_object($widget)) {
			return parent::renderEachChildWidget($widget, $key);
		}

		$imageTag = '<img class="photoitem -photo" src="' . $widget['img'] . '" />';
		$ret = '';
		if ($widget['link']) {
			$widget['link']->text = $imageTag;
			$ret .= parent::renderEachChildWidget($widget['link']);
		} else {
			$ret .= $imageTag;
		}
		if (isset($widget['title'])) {
			$ret .= parent::renderEachChildWidget($widget['title']);
		}
		if (isset($widget['detail'])) {
			$ret .= parent::renderEachChildWidget($widget['detail']);
		}
		if (isset($widget['navigator'])) {
			if (is_array($widget['navigator'])) {
				$ret .= new Nav($widget['navigator'])->build();
			} else {
				$ret .= parent::renderEachChildWidget($widget['navigator']);
			}
		}
		return $ret;
	}

	function build() {
		if (!empty($this->upload)) {
			$this->children = array_replace_recursive(
				['upload' => &$this->upload],
				$this->children
			);
		}
		return parent::build();
	}
} // End of class Album
?>
