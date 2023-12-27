<?php
/**
* Paper   :: Document Management
* Created :: 2019-06-02
* Modify  :: 2023-12-27
* Version :: 4
*
* @param String $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/edit.docs
*/

use Softganz\DB;

class PaperEditDocs extends Page {
	var $nodeId;
	var $right;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'nodeInfo' => $nodeInfo,
			'right' => $nodeInfo->right,
		]);
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		if (!user_access('upload document')) return error(_HTTP_ERROR_FORBIDDEN,'Access denied');
		return true;
	}

	function build() {
		// show reference file list

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Document Management',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
				'trailing' => new Row([
					'child' => '<a class="sg-action btn -primary" href="#paper-edit-docs-upload" data-rel="box" data-width="640"><i class="icon -material">cloud_upload</i><span>{tr:UPLOAD NEW DOCUMENT}</span></a>',
				]),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile([
						'title' => 'All document relate to this topic',
					]), // ListTile
					$this->list(),
					$this->uploadTemplate(),
				], // children
			]), // Widget
		]);
	}

	function list() {
		$docs = DB::select([
			'SELECT `fid`, `file`, `folder`, `title`
			FROM %topic_files%
			WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type`="doc"',
			'var' => [':tpid' => $this->nodeId]
		]);

		return new Container([
			'id' => 'paper-edit-docs-list',
			'data-url' => url('paper/'.$this->nodeId.'/edit.docs'._MS_.'list'),
			'child' => new Table([
				'thead' => [
					'Document file',
					'amt -hover-parent' => 'File size (bytes)'
				],
				'children' => array_map(
					function($doc) {
						$ui = new Ui();
						$ui->add('<a href="'.url('api/paper/'.$this->nodeId.'/doc.delete', ['fileId' => $doc->fid]).'" class="sg-action" data-rel="none" data-title="Delete document!!!" data-confirm="Are you sure?" data-removeparent="tr"><i class="icon -material -gray">cancel</i></a>');
						$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
						$fileProperty = FileModel::docProperty($doc->file, $doc->folder);

						return [
							'<a href="'.(cfg('files.log')?url('files/'.$doc->fid):cfg('url').'upload/forum/'.sg_urlencode($doc->file)).'" target="_blank">'.($doc->title?$doc->title.' ('.$doc->file.')':$doc->file).'</a>',
							number_format($fileProperty->size)
							. $menu,
						];
					},
					$docs->items
				), // children
			]), // Table
		]);
		// $docs->count == 0 ? 'No attach document' : NULL,
	}

	private function uploadTemplate() {
		$maxsize = intval(ini_get('post_max_size')) < intval(ini_get('upload_max_filesize')) ? ini_get('post_max_size') : ini_get('upload_max_filesize');

		return new HtmlTemplate([
			'id' => 'paper-edit-docs-upload',
			'child' => new Widget([
				'children' => [
					new AppBar(['title' => 'Upload Document', 'boxHeader' => true, 'leading' => _HEADER_BACK, 'class' => '-box']),
					new Form([
						'variable' =>'info',
						'action' => url('api/paper/'.$this->nodeId.'/doc.add'),
						'id' => 'edit-topic',
						'enctype' => 'multipart/form-data',
						'class' => 'sg-form -upload -sg-paddingnorm',
						// 'rel' => 'notify',
						'done' => 'close | load->replace:#paper-edit-docs-list',
						'children' => [
							'doc' => [
								'name' => 'doc',
								'label' => '<i class="icon -material">attach_file</i>Select document file to upload',
								'type' => 'file',
								'size' => 50,
								'require' => true,
								'container' => ['class' => 'btn -upload -primary'],
							],
							'<div id="edit-document-filename"></div>',
							'noRename' => is_admin('paper') ? [
								'type' => 'checkbox',
								'options' => ['1' => 'ไม่เปลี่ยนชื่อไฟล์'],
							] : NULL,
							'title' => [
								'type' => 'text',
								'label' => 'Document title',
								'class' => '-fill',
								'maxlength' => 150,
								'placeholder' => 'ระบุชื่อเอกสาร',
							],
							'description' => [
								'type' => 'textarea',
								'label' => 'Document description',
								'class' => '-fill',
								'rows' => 3,
								'placeholder' => 'บรรยายรายละเอียดเพิ่มเติม',
							],
							'submit' => [
								'type'=>'button',
								'name'=>'upload',
								'value'=>'<i class="icon -material">cloud_upload</i><span>{tr:Upload}</span>',
								'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/docs').'" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
								'container' => '{class: "-sg-text-right"}',
							],
							'ข้อกำหนดในการส่งไฟล์เอกสารประกอบ</strong>
							<ul>
							<li>ไฟล์เอกสารจะต้องเป็นไฟล์ประเภท <strong>.'.implode(' , .',cfg('topic.doc.file_ext')).'</strong> เท่านั้น </li>
							<li>ขนาดไฟล์ต้องไม่เกิน <strong>'.ini_get('upload_max_filesize').'B</strong></li>
							<li>หากไฟล์เอกสารเป็นในรูปแบบอื่น ท่านควรแปลงให้เป็น Acrobat reader (pdf) ให้เรียบร้อยก่อนส่งขึ้นเว็บ</li>
							<li>หากต้องการเพิ่มไฟล์เอกสารประกอบ , แก้ไข หรือ ลบทิ้ง สามารถทำได้โดยการเข้าไปแก้ไขรายละเอียดเอกสารประกอบในภายหลัง</li>
							<li>กรณีที่หัวข้อถูกลบทิ้ง ไฟล์เอกสารประกอบทั้งหมดที่อ้างอิงอยู่กับหัวข้อนั้น ๆ จะถูกลบทิ้งทั้งหมด</li>
							</ul>',
							'<script type="text/javascript">
							$("#edit-doc").change(function() {
								var f = $(this).val().replace(/.*[\/\\\\]/, "")
								$("#edit-document-filename").text("เลือกไฟล์ : "+f)
								$("#edit-info-title").val(f)
							})
							</script>'
						], // children
					]), // Form
				],
			]), // Scaffold
		]);
	}
}
?>