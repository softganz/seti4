<?php
/**
* Project :: Action Expense
* Created 2019-10-24
* Modify  2022-02-07
*
* @param Object $projectInfo
* @param Int $actionId
* @return Widget
*
* @usage project/{id}/info.expense
*/

import('widget:project.info.appbar.php');

class ProjectInfoExpense extends Page {
	var $projectId;
	var $actionId;
	var $locked = false;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $actionId = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->actionId = $actionId;
		$this->right = (Object) [
			'editTran' => false,
			'upload' => false,
		];
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		if ($this->actionId) {
			$actionInfo = R::Model('project.action.get', ['projectId' => $this->projectId, 'actionId' => $this->actionId]);
		}

		if (!$actionInfo) return R::PageWidget('project.info.expense.list', [$this->projectInfo]);

		$this->locked = $actionInfo->flag == 2;

		$isAdmin = user_access('administer projects') || $this->projectInfo->right->isAdmin;
		$isEdit = false;
		$isOwner = project_model::is_owner_of($this->projectId) || project_model::is_trainer_of($this->projectId);
		$isAccessExpense = $isAdmin
			|| user_access('access full expense')
			|| $isOwner
			|| ($this->actionId && $actionInfo->uid == i()->uid);

		if (!$isAccessExpense) return '<p class="notify">ขออภัย ท่านไม่สามารถดูค่าใช้จ่ายของโครงการได้</p>';

		if ($this->projectInfo->info->project_statuscode == 1) {
			$this->right->editTran = !$this->locked && ($isAdmin || $isOwner || $actionInfo->uid == i()->uid);
			$this->right->upload = $isAdmin || $isOwner || $actionInfo->uid == i()->uid;
		}

		$expCodeList = R::Model('project.expense.code.get',NULL,NULL,'{resultType:"group"}');

		$stmt = 'SELECT
			  *
			, tr.`num1` `amt`, tr.`num2` `tax`, tr.`detail1` `description`
			FROM %tag% t
				LEFT JOIN %project_tr% tr
					ON tr.`tpid`=:tpid AND tr.`calid` = :calid AND tr.`refid` = t.`catid` AND tr.`formid` = "expense" AND `part` = "exptr"
			WHERE t.`taggroup` = "project:expcode" AND t.`name` != ""
			ORDER BY `catparent`,`catid`';

		$expenseDbs = mydb::select($stmt,':tpid',$this->projectId,':calid',$actionInfo->calid);

		if ($actionInfo->gallery || $actionInfo->rcvPhotos) {
			// Get photo from database
			$stmt = 'SELECT
				f.`fid`, f.`type`, f.`file`, f.`title`, f.`tagname`
				FROM %topic_files% f
				WHERE f.`tpid` = :tpid AND (f.`gallery` = :gallery OR f.`refid` = :refid) AND `tagname` = :tagname';
			$expensePhoto = mydb::select($stmt, ':tpid', $this->projectId, ':refid', $this->actionId, ':tagname', 'project,rcv', ':gallery', SG\getFirst($actionInfo->gallery,-1));
		}

		//$ret .= print_o($actionInfo,'$actionInfo');



		// View Model
		$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>บันทึกค่าใช้จ่ายกิจกรรม</h3></header>';
		// Activity information
		$ret .= '<h3>กิจกรรม : '.$actionInfo->title.'</h3>';
		$ret .= '<p>วันที่ '.sg_date($actionInfo->actionDate,'ว ดด ปปปป').'</p>';

		// Activity summary expense by group
		$ret .= '<div class="project-expense -summary">';
		$ret .= '<h4>สรุปการใช้เงินในกิจกรรม</h4>';

		$tables = new Table();
		if (R()->appAgent) {
			$tables->thead = array('ประเภทรายจ่าย', 'amount -money' => 'จำนวนเงิน');
			$tables->rows[] = array('ค่าตอบแทน', number_format($actionInfo->exp_meed,2));
			$tables->rows[] = array('ค่าจ้าง', number_format($actionInfo->exp_wage,2));
			$tables->rows[] = array('ค่าใช้สอย', number_format($actionInfo->exp_supply,2));
			$tables->rows[] = array('ค่าวัสดุ', number_format($actionInfo->exp_material,2));
			$tables->rows[] = array('ค่าสาธารณูปโภค', number_format($actionInfo->exp_utilities,2));
			$tables->rows[] = array('อื่น ๆ', number_format($actionInfo->exp_other,2));
			$tables->rows[] = array('<b>รวมรายจ่าย</b>', '<b>'.number_format($actionInfo->exp_total,2).'</b>');
		} else {
			$tables->addclass('-center');
			$tables->thead = '<thead><tr><th colspan="6">ประเภทรายจ่าย</th><th rowspan="2">รวมรายจ่าย</th></tr><tr><th>ค่าตอบแทน</th><th>ค่าจ้าง</th><th>ค่าใช้สอย</th><th>ค่าวัสดุ</th><th>ค่าสาธารณูปโภค</th><th>อื่น ๆ</th></tr></thead>';

			$tables->rows[] = array(
				number_format($actionInfo->exp_meed,2),
				number_format($actionInfo->exp_wage,2),
				number_format($actionInfo->exp_supply,2),
				number_format($actionInfo->exp_material,2),
				number_format($actionInfo->exp_utilities,2),
				number_format($actionInfo->exp_other,2),
				'<b>'.number_format($actionInfo->exp_total,2).'</b>',
			);
		}
		$ret .= $tables->build();

		$ret .= '</div><!-- -summary -->';




		// Activity expense transaction
		$ret .= '<div class="project-expense -tran">';
		$ret .= '<h4>รายการใช้เงินในกิจกรรม</h4>';
		$tables = new Table();
		$tables->thead = ['no'=>'','รายการ','money amt'=>'จำนวนเงิน (บาท)','money tax'=>'ภาษีหัก ณ ที่จ่าย (บาท)','i -hover-parent'=>''];

		$totalSumExpense = 0;
		foreach ($expCodeList as $expGroupId => $expGroupInfo) {
			$tables->rows['gr:'.$expGroupId] = [
				++$i,reset($expGroupInfo)->groupName,
				number_format(1,2),
				'',
				$this->right->editTran ? '<nav class="nav -icons"><a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info.expense.add/'.$this->actionId, ['gr' => $expGroupId]).'" data-rel="box" data-width="640"><i class="icon -material">add_circle_outline</i></a></nav>' : NULL,
				'config'=>array('class'=>'subheader'),
			];

			$grTotal = 0;

			foreach ($expenseDbs->items as $rs) {
				if ($rs->catparent != $expGroupId) continue;
				$submenu = '';
				if ($this->right->editTran && $rs->trid) {
					$ui = new Ui();
					$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info.expense.add/'.$this->actionId,array('gr'=>$rs->catparent,'tr'=>$rs->trid)).'" data-rel="box" data-width="640"><i class="icon -material">edit</i></a>');
					$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/expense.remove/'.$rs->trid).'" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="notify" data-done="load->replace: #project-info-expense"><i class="icon -material">cancel</i></a>');
					$submenu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
				}

				$tables->rows[] = [
					'<td></td>',
					'&nbsp;'.$rs->name.($rs->description?'<br />&nbsp;('.$rs->description.')':''),
					number_format($rs->amt,2),
					number_format($rs->tax,2),
					''
					. $submenu,
				];

				$grTotal += $rs->amt;
			}
			$totalSumExpense += $grTotal;
			$tables->rows['gr:'.$expGroupId][2] = number_format($grTotal,2);
		}
		$tables->tfoot[] = array('<td></td>','รวมรายจ่าย','<td class="col-money">'.number_format($totalSumExpense,2).'</td>','', '');

		$ret .= $tables->build();

		if ($this->right->editTran && number_format($totalSumExpense,2) != number_format($actionInfo->exp_total,2)) {
			$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="'.url('project/info/api/'.$this->projectId.'/expense.calculate/'.$this->actionId).'" data-rel="notify" data-done="load->replace: #project-info-expense | moveto: 0,0" data-title="คำนวณยอดสรุป" data-confirm="ต้องการคำนวนยอดสรุปการใช้เงินในกิจกรรม"><i class="icon -material">published_with_changes</i><span>คำนวณยอดสรุปการใช้เงินในกิจกรรม</span></a></nav>';
		}

		$ret .= '</div><!-- tran -->';



		// Show photos
		$photoAlbum = new Ui(NULL, 'ui-album -justify-left');

		foreach ($expensePhoto->items as $item) {
			$cardStr = '';
			$ui = new Ui('span');
			$ui->addConfig('nav', '{class: "nav -icons -hover"}');
			if ($this->right->upload) {
				$ui->add('<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-title="ลบภาพ" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material">cancel</i></a>');
			}
			if ($item->type=='photo') {
				$photo = model::get_photo_property($item->file);
				$photo_alt = $item->title;
				$cardStr .= '<a class="photoitem" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">'
					. '<img class="photo -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" />'
					. '</a>'
					. $ui->build();
			}
			$photoAlbum->add(
				$cardStr,
				'{class: "-hover-parent"}'
			);
		}
		if ($this->right->upload) {
			$photoAlbum->add(
				'<form class="sg-upload -sg-text-center" method="post" enctype="multipart/form-data" action="'.url('project/'.$this->projectId.'/info/expense.photo.upload/'.$this->actionId).'" data-rel=".ui-album" data-before="li">'
				. '<span class="btn -link fileinput-button"><i class="icon -material">add_a_photo</i>'
				. '<br /><span class="-sg-block-center">'.$cameraStr.'ส่งภาพใบเสร็จรับเงิน</span>'
				. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
				. '<input class="-hidden" type="submit" value="upload" />'
				. '</form>',
				['class' => '-upload-btn']
			);
		}

		$ret .= '<div class="project-expense -rcvphoto">';
		$ret .= '<h4>ใบเสร็จรับเงิน</h4>';
		$ret .= $photoAlbum->build(true);
		$ret .= '<div class="photocard -projectrcv">'._NL;
		if (debug('method')) $ret.=$rs->expensePhoto.print_o($rs,'$rs');
		if ($this->right->upload) {
			$ret .= '<div class="-sg-text-center" style="margin:20px 0;">'
				. '<img src="https://chart.googleapis.com/chart?cht=qr&chl='._DOMAIN.url('project/'.$this->projectId.'/info.expense/'.$this->actionId).'&chs=180x180&choe=UTF-8&chld=L|2" alt="">'
				. '<p>อัพโหลดใบเสร็จรับเงินโดยการถ่ายภาพจากสมาร์ทโฟนโดยใช้สมาร์ทโฟนสแกนคิวอาร์โค๊ดนี้ แล้วกด "ส่งภาพใบเสร็จรับเงิน" เลือกกล้องถ่ายรูป</p>'
				. '</div>';
		}

		$ret .= '</div><!--photo-->'._NL;

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Container([
				'id' => 'project-info-expense',
				'class' => 'project-expense -title',
				'attribute' => ['data-url' => url('project/'.$this->projectId.'/info.expense/'.$this->actionId)],
				'children' => [
					$ret,
					$this->_script(),
				], // children
			]), // Container
		]);
	}

	function _script() {
		return '<style type="text/css">
		@media (min-width:45em) { /* 720/16 = 44 tablet & iPad */
			.project-expense.-tran {width:50%; float: left; margin-right: 40px; }
			.project-expense.-rcvphoto {}
			.project-expense.-rcvphoto:after {content:""; display: block; clear: both;}
			.photocard.-projectrcv>ul {clear: none;}
		}
		</style>';
	}
}
?>