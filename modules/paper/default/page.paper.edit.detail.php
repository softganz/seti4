<?php
/**
* Paper  :: Edit Detail
* Created :: 2019-06-01
* Modify  :: 2024-08-22
* Version :: 7
*
* @param String $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/edit.detail
*/

class PaperEditDetail extends Page {
	var $nodeId;
	var $right;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'nodeInfo' => $nodeInfo,
			'backend' => NodeModel::getBackend($nodeInfo->nodeId),
			'right' => (Object) array_merge(
				(Array) $nodeInfo->right,
				[
					'editBackend' => is_admin(),
					'editCss' => is_admin(),
					'editScript' => is_admin(),
					'editData' => is_admin()
				]
			)
		]);
		load_lib('class.editor.php','lib');
		// print_o($this->right, '$this->right',1);
	}

	function rightToBuild() {
		if (!$this->nodeInfo->nodeId) return error(_HTTP_ERROR_BAD_REQUEST, 'PARAMETER ERROR');
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if ($this->nodeInfo->property->input_format == 'php' && !user_access('input format type php')) return error(_HTTP_ERROR_FORBIDDEN,'Access denied:ไม่สามารถแก้ไขหัวข้อที่มีรูปแบบข้อมูลเป็นโปรแกรมได้');
		return true;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แก้ไขรายละเอียด',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new TabBar([
						'class' => 'paper-upload-tabs',
						'style' => 'margin: 8px',
						'children' => [
							$this->right->edit ? [
								'id' => 'edit-detail',
								'active' => true,
								'action' => new Button(['href' => '#edit-detail', 'text' => 'รายละเอียด']),
								'content' => $this->detail(),
							] : NULL,
							$this->right->editBackend ? [
								'id' => 'edit-backend',
								'action' => new Button(['href' => '#edit-backend', 'text' => 'PHP Back End']),
								'content' => $this->backend(),
							] : NULL,
							$this->right->editCss ? [
								'id' => 'edit-css',
								'action' => new Button(['href' => '#edit-css', 'text' => 'CSS']),
								'content' => $this->css(),
							] : NULL,
							$this->right->editScript ? [
								'id' => 'edit-script',
								'action' => new Button(['href' => '#edit-script', 'text' => 'Scripts']),
								'content' => $this->script(),
							] : NULL,
							$this->right->editData ? [
								'id' => 'edit-data',
								'action' => new Button(['href' => '#edit-data', 'text' => 'JSON Data']),
								'content' => $this->data(),
							] : NULL,
						], // children
					]), // TabBar
					$this->formScript(),
					// new DebugMsg($this, '$this')
				], // children
			]), // Widget
		]);
	}

	function detail() {
		$type = BasicModel::get_topic_type($this->nodeInfo->info->type);
		return new Form([
			'variable' => 'topic',
			'action' => url('api/paper/'.$this->nodeId.'/detail.update'),
			'class' => 'sg-form',
			'checkValid' => true,
			'rel' => 'notify',
			'children' => [
				'title' => $type->has_title ? [
					'type' => 'text',
					'name' => 'topic[title]',
					'label' => $type->title_label,
					'class' => '-fill',
					'maxlength' => 150,
					'require' => true,
					'value' => $this->nodeInfo->title,
				] : NULL,
				'body' => $type->has_body ? [
					'type' => 'textarea',
					'name' => 'detail[body]',
					'label' => $type->body_label,
					'class' => '-fill',
					'rows' => 16,
					'value' => $this->nodeInfo->info->body,
					'pretext' => editor::softganz_editor('edit-detail-body'),
					'description' => 'คำแนะนำ : เนื่องจากได้มีการเปลี่ยนแปลงวิธีการขึ้นบรรทัดใหม่ ซึ่งมีรายละเอียดดังนี้<ul><li>วิธีการขึ้นบรรทัดใหม่โดยไม่เว้นช่องว่างระหว่างบรรทัด ให้เคาะเว้นวรรค (Space bar) ที่ท้ายบรรทัดจำนวนหนึ่งครั้ง</li><li>วิธีการขึ้นย่อหน้าใหม่ซึ่งจะมีการเว้นช่องว่างห่างจากบรรทัดด้านบนเล็กน้อย ให้เคาะ Enter จำนวน 2 ครั้ง</li><li>หากข้อความของท่านยาวเกินไป จะทำให้ไม่สามารถนำข้อความทั้งหมดไปแสดงในหน้าแรก ให้ใส่ &lt;!--break--&gt แทรกไว้ในตำแหน่งที่ต้องการให้ตัดไปแสดงผล</li></ul>',
				] : NULL,
				$this->nodeInfo->photos ? (function($topicInfo) {
					$photo_list = _NL.'<div id="edit-topic-body-control-photo" class="editor" title="edit-detail-body">';
					foreach ($topicInfo->photos as $photo) {
						$photo_title = tr('Photo name').' : '.\SG\getFirst($photo->pic_name,substr($photo->file,0,strrpos($photo->file,'.')));
						$photo_desc = tr('Photo Description').' : '.\SG\getFirst($photo->pic_name,substr($photo->file,0,strrpos($photo->file,'.')));
						if ($topicInfo->property->input_format == 'markdown') {
							$onclick = 'editor.insert("![ '.$photo_desc.' ]('.$photo->_url.' \"'.$photo_title.'\" '.(cfg('topic.photo.detail.class')?' class=\"'.cfg('topic.photo.detail.class').'\"':'').')");return false';
						} else {
							$onclick = 'editor.insert("<img src=\"'.$photo->_url.'\" alt=\"'.htmlspecialchars($photo_desc).'\" title=\"'.htmlspecialchars($photo_title).'\"'.(cfg('topic.photo.detail.class')?' class=\"'.cfg('topic.photo.detail.class').'\"':'').' />");return false';
						}
						$photo_list .= '<img src="'.$photo->src.'" class="photo" onclick=\''.$onclick.'\' alt="'.$photo->file.'" title="'.sg_client_convert('คลิกเพื่อวางภาพนี้').' -> '.$photo->file.' ('.$photo->_size->width.'x'.$photo->_size->height.' pixels '.number_format($photo->_filesize).' bytes)" height="50" /> ';
					}
					$photo_list .= '</div>'._NL;

					return $photo_list;
				})($this->nodeInfo) : NULL,

				(function($tpid) {
					$doc_list = '';
					$docs = mydb::select('SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type` = "doc"',':tpid', $tpid);

					if ($docs->_num_rows) {
						$doc_list .= _NL.'<div id="edit-topic-body-control-docs" class="editor" title="edit-detail-body">';
						foreach ($docs->items as $doc) {
							$docDesc = preg_replace('/[\"\']/','','"'.\SG\getFirst($doc->title,$doc->file).'"');
							$docUrl = cfg('files.log') ? url('files/'.$doc->fid) : cfg('url').'upload/forum/'.sg_urlencode($doc->file);
							if ($this->nodeInfo->property->input_format == 'markdown') {
								$onclick = 'editor.insert("['.$docDesc.']('.$docUrl.')");return false';
							} else {
								$onclick = 'editor.insert("<a href=\"'.$docUrl.'\" title=\"'.htmlspecialchars($docDesc).'\">'.$docDesc.'</a>");return false';
							}
							$doc_list .= '<a class="btn -link" href="javascript:void(0)" onclick=\''.$onclick.'\' title="คลิกเพื่อวางไฟล์นี้">'.$docDesc.'</a> , ';
						}
						$doc_list = rtrim($doc_list,' , ');
						$doc_list .= '</div>'._NL;
					}
					return $doc_list ? $doc_list : NULL;
				})($this->nodeId),

				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	function backend() {
		return new Form([
			'variable' => 'topic',
			'action' => url('api/paper/'.$this->nodeId.'/detail.update'),
			'class' => 'sg-form',
			'checkValid' => true,
			'rel' => 'notify',
			'children' => [
				'phpBackend' => [
					'type' => 'textarea',
					'name' => 'detail[phpBackend]',
					'class' => '-monospace -fill',
					'rows' => 32,
					'value' => $this->backend->phpBackend,
				],

				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="btn -link" onClick="copyBackend()"><i class="icon -material">content_copy</i><span>Copy Template</span></a> '
						. '<a class="btn -link" onClick="getBackend(\'backend\')"><i class="icon -material">refresh</i><span>Refresh</span></a> '
						. '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
				$this->backendTemplate(),
			], // children
		]);
	}

	function css() {
		return new Form([
			'variable' => 'topic',
			'action' => url('api/paper/'.$this->nodeId.'/detail.update'),
			'class' => 'sg-form',
			'checkValid' => true,
			'rel' => 'notify',
			'children' => [
				'css' => [
					'type' => 'textarea',
					'name' => 'detail[css]',
					'class' => '-monospace -fill',
					'rows' => 32,
					'value' => $this->backend->css,
				],

				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="btn -link" onClick="getBackend(\'css\')"><i class="icon -material">refresh</i><span>Refresh</span></a> '
						. '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	function script() {
		return new Form([
			'variable' => 'topic',
			'action' => url('api/paper/'.$this->nodeId.'/detail.update'),
			'class' => 'sg-form',
			'checkValid' => true,
			'rel' => 'notify',
			'children' => [
				'script' => [
					'type' => 'textarea',
					'name' => 'detail[script]',
					'class' => '-monospace -fill',
					'rows' => 32,
					'value' => $this->backend->script,
				],

				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="btn -link" onClick="getBackend(\'script\')"><i class="icon -material">refresh</i><span>Refresh</span></a> '
						. '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}

	function data() {
		return new Form([
			'variable' => 'topic',
			'action' => url('api/paper/'.$this->nodeId.'/detail.update'),
			'class' => 'sg-form',
			'checkValid' => true,
			'rel' => 'notify',
			'children' => [
				'data' => [
					'type' => 'textarea',
					'name' => 'detail[data]',
					'class' => '-monospace -fill',
					'rows' => 32,
					'value' => json_encode(json_decode($this->backend->data), JSON_PRETTY_PRINT  + JSON_UNESCAPED_UNICODE),
				],

				'save' => [
					'type' => 'button',
					'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
					'pretext' => '<a class="btn -link" onClick="getBackend(\'data\')"><i class="icon -material">refresh</i><span>Refresh</span></a> '
						. '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
			], // children
		]);
	}


	private function backendTemplate() {
		return '<template id="backend-template">use Softganz\DB;
class Paper'.$this->nodeId.'Api extends PageApi {
	var $nodeInfo;
	var $action;
	var $nodeId;

	function __construct($nodeInfo = NULL, $action = NULL) {
		parent::__construct([
			\'nodeId\' => $nodeInfo->nodeId,
			\'nodeInfo\' => $nodeInfo,
			\'right\' => (Object) []
		]);
	}

	// @usage /api/paper/{nodeId}/node/foo
	function foo() {
		return apiSuccess([
			\'text\' => \'FOO \'.Paper'.$this->nodeId.'Model::get($this->nodeId),
			\'nodeId\' => $this->nodeInfo->nodeId,
		]);
	}
}

class Paper'.$this->nodeId.'Model extends Model {
	static function get($id) {
		return $id;
	}
}</template>';
	}

	private function formScript() {
		return '<style type="text/css">
		.widget-tabbar>div {border: 1px #ccc solid; border-top: none; border-radius: 0 0 8px 8px;}
		</style>
		<script>
			const divs = document.querySelectorAll(".-monospace");

			divs.forEach(el => el.addEventListener("keydown", event => {
				// console.log(event.target.getAttribute("data-el"));
				if (event.key == "Tab") {
					event.preventDefault();
					let target = event.target;
					var start = target.selectionStart;
					var end = target.selectionEnd;

					// set textarea value to: text before caret + tab + text after caret
					target.value = target.value.substring(0, start) + "\t" + target.value.substring(end);

					// put caret at right position again
					target.selectionStart = target.selectionEnd = start + 1;
				}

			}));

			function copyBackend() {
				let template = document.getElementById("backend-template");
				let target = document.getElementById("edit-detail-phpbackend");
				target.innerHTML = "<?php\n" + template.innerHTML + "\r?>";
			}

			function getBackend(element) {
				event.preventDefault();
				$.get(SG.url("api/paper/'.$this->nodeId.'/backend"), function(){

				})
				.done(function(data){
					if (element === "backend") {
						$("#form-item-edit-detail-phpbackend textarea").val(data.phpBackend);
					} else if (element === "css") {
						$("#form-item-edit-detail-css textarea").val(data.css);
					} else if (element === "script") {
						$("#form-item-edit-detail-script textarea").val(data.script);
					} else if (element === "data") {
						$("#form-item-edit-detail-data textarea").val(data.data.json);
					}
				});
				return false;
			}
		</script>';
	}
}
?>