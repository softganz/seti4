<?php
/**
* Project :: Fund Board Home
* Created 2019-05-07
* Modify  2021-12-01
*
* @param Object $fundInfo
* @return Widget
*
* @usage project/fund/$orgId/board
*/
import('package:project/fund/widgets/widget.fund.nav');

class ProjectFundBoard extends Page {
	var $fundInfo;

	function __construct($fundInfo = NULL) {
		$this->fundInfo = $fundInfo;
	}

	function build() {
		$fundInfo = $this->fundInfo;
		if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');


		$isEditable = $fundInfo->right->edit;
		$isDelete = $fundInfo->right->admin;


		$stmt = 'SELECT b.*, bp.`name` `boardName`, p.`name` `positionName`, p.`weight`
			FROM %org_board% b
				LEFT JOIN %tag% bp ON bp.`catid` = b.`boardposition` AND bp.`taggroup` = "project:board"
				LEFT JOIN %tag% p ON p.`taggroup` = "project:boardpos" AND p.`catid` = b.`position`
			WHERE b.`orgid` = :orgid AND `status` = 1
			ORDER BY `boardposition`, `weight`, `posno`';

		$dbs = mydb::select($stmt,':orgid', $orgId);

		$someoneToAppointed = false;
		foreach ($dbs->items as $rs) {
			if ($rs->appointed < 1) {
				$someoneToAppointed = true;
				break;
			}
		}

		//$ret .= $someoneToAppointed ? 'Yes' : 'No';

		$stmt = 'SELECT COUNT(*) `totals` FROM %project_tr% WHERE `refid` = :orgid AND `formid` = "fund" AND `part` = "boardletter" AND `flag` IS NULL LIMIT 1';
		$hasNoticeNotSend = mydb::select($stmt, ':orgid', $orgId)->totals;

		//$ret .= 'hasNoticeNotSend = '.$hasNoticeNotSend.'<br />';


		if ($isEditable) {
			$stepUi = new Ui(NULL, 'ui-step');
			$isBeOver = $dbs->_empty || $dbs->_num_rows;
			$isBoardAdd = $dbs->_num_rows;
			$hasLetterToCreate = $someoneToAppointed;

			//$ret .= $hasLetterToCreate ? 'Yes' : 'No';
			$stepUi->add(
					'<a class="step -s1" '.($isBeOver ? '' : 'href="'.url('project/fund/'.$orgId.'/board.beover').'"').'><span class="step-num">1</span><span>ชุดเก่าหมดวาระ</span></a>',
					$isBeOver ? '{class: "-done"}' : ''
				);

			$stepUi->add(
					'<a class="sg-action step -s2" href="'.url('project/fund/'.$orgId.'/board.new').'" data-rel="box" data-width="470px"><span class="step-num">2</span><span>ป้อนชื่อชุดใหม่</span></a>',
					$isBoardAdd ? '{class: "-done"}' : ''
				);

			$stepUi->add(
					'<a class="'.($hasLetterToCreate ? 'sg-action' : '').' step -s3" '.($hasLetterToCreate ? 'href="'.url('project/fund/'.$orgId.'/board.letter.appoint').'"' : '').' data-rel="box"><span class="step-num">3</span><span>ออกหนังสือแต่งตั้ง</span></a>',
					$hasLetterToCreate ? '' : ($dbs->_empty ? '' : ($someoneToAppointed ? '' : '{class: "-done"}'))
				);

			$stepUi->add(
						'<a class="'.($hasNoticeNotSend ? 'sg-action' : '').' step -s4" '.($hasNoticeNotSend ? 'href="'.url('project/fund/'.$orgId.'/board.letter').'"' : '').' data-rel="box"><span class="step-num">4</span><span>แจ้ง สปสช.เขต</span></a>',
						$hasNoticeNotSend ? '' : ($dbs->_empty ? '' : ($someoneToAppointed ? '' : '{class: "-done"}'))
					);

			$ret .= '<nav class="nav -step -no-print"><hr />'.$stepUi->build().'</nav>';
		}





		$tables = new Table('item -board');
		$tables->thead = [
			'no' => '',
			'a -no-print' => '',
			'name -nowrap' => 'ชื่อ-นามสกุล',
			'position -nowrap' => 'ตำแหน่ง',
			'องค์ประกอบของคณะกรรมการ',
			'datein -date' => 'เริ่มดำรงตำแหน่ง',
			'datedue -date' => 'ครบวาระ',
			'status -center -nowrap -hover-parent' => 'สถานะ',
		];

		$no = 0;
		foreach ($dbs->items as $rs) {
			if ($isEditable) {
				$menu = '<nav class="nav iconset -hover">'
					. '<a class="sg-action" href="'.url('project/fund/'.$orgId.'/board.edit/'.$rs->brdid).'" data-rel="box" data-width="470px"><i class="icon -edit"></i></a> '
					. sg_dropbox(
						'<ul>'
						. '<li><a class="sg-action" href="'.url('project/fund/'.$orgId.'/board.out/'.$rs->brdid).'" data-rel="box" data-width="480"><i class="icon -material">arrow_forward</i><span>บันทึกออกจากการเป็นกรรมการ</span></a></li>'
						. ($isDelete?'<li><a class="sg-action" href="'.url('project/fund/'.$orgId.'/info/board.delete/'.$rs->brdid).'" data-rel="notify" data-title="ลบชื่อกรรมการ" data-confirm="ต้องการลบชื่อกรรมการนี้ กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material">delete</i><span>ลบชื่อกรรมการ</span></a></li>':'')
						. '</ul>',
						'{type:"click"}'
					)
					. '</nav>';
			}

			$tables->rows[]=array(
				++$no,
				'<a class="btn -link -circle32">'.($rs->appointed ? '<i class="icon -material -circle32 -green">how_to_reg</i>' : '<i class="icon -material -circle32 -gray">person</i>').'</a>',
				$rs->prename.$rs->name,
				$rs->boardName,
				$rs->positionName
				.($rs->posno>=1 ? ' คนที่  '.$rs->posno : '')
				.($rs->fromorg ? '<br />'.$rs->fromorg : ''),
				($rs->datein?sg_date($rs->datein,'ว ดด ปปปป'):''),
				($rs->datedue?sg_date($rs->datedue,'ว ดด ปปปป'):''),
				($rs->appointed ? 'แต่งตั้ง' : 'ยังไม่แต่งตั้ง')
				.$menu,
			);
		}
		$ret.=$tables->build();

		if ($isEditable) {
			$ret .= '<p class="-no-print"><b>หมายเหตุ : </b> การลบชื่อกรรมกรรมที่บันทึกผิดพลาดหรือทดลอง ให้เลือกเมนู "บันทึกออกจากการเป็นกรรมการ" ก่อน แล้วเข้าสู่หน้า "<a href="'.url('project/fund/'.$orgId.'/board.all').'">ทำเนียบกรรมการ</a>" และทำการลบชื่อกรรมการออกจากทำเนียบกรรมการ</p>';
		}
		//$ret .= print_o($dbs,'$dbs');

		if ($isEditable) {
			$ret.='<div class="-no-print" style="margin:20px 0;"><form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/fund/'.$orgId.'/upload',array('tagname'=>'letterofappointment')).'" data-rel="#loapp-photo" data-prepend="li"><span class="btn btn-success fileinput-button"><i class="icon -upload"></i><span>อัพโหลดหนังสือแต่งตั้ง</span><input type="file" name="photo[]" multiple="true" class="inline-upload" /></span><input class="-hidden" type="submit" value="upload" /></form></div>'._NL;

			$ret.='<ul id="loapp-photo" class="photocard -loapp -no-print">'._NL;
			// Get photo from database
			$photos=mydb::select('SELECT f.`fid`, f.`type`, f.`file`, f.`title` FROM %topic_files% f WHERE f.`orgid`=:orgid AND `tagname`="letterofappointment" ORDER BY `fid` DESC', ':orgid',$orgId);

			// Show photos
			foreach ($photos->items as $rs) {
				if ($rs->type == 'photo') {
					$photo=model::get_photo_property($rs->file);
					$photo_alt = $rs->title;
					$ret .= '<li class="-hover-parent">';
					$ret .= '<a class="sg-action" data-group="photo" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
					$ret .= '<img class="photoitem -'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" ';
					$ret .= ' />';
					$ret .= '</a>';
					if ($isEditable) {
						$ui = new Ui('span','iconset -hover');
						$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$rs->fid).'" data-title="ลบภาพ" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
						$ret .= $ui->build();
					}
					$ret .= '</li>'._NL;
				} else {
					$uploadUrl = cfg('paper.upload.document.url').$rs->file;
					$ret .= '<li class="-hover-parent">';
					$ret .= '<a href="'.$uploadUrl.'"><img src="//img.softganz.com/icon/pdf-icon.png" /></a>';
					if ($isEditable) {
						$ui = new Ui('span','iconset -hover');
						$ui->add('<a class="sg-action" href="'.url('project/edit/delphoto/'.$rs->fid).'" data-title="ลบไฟล์" data-confirm="ยืนยันว่าจะลบไฟล์นี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel -gray"></i></a>');
						$ret .= $ui->build();
					}
					$ret .= '</li>';
				}
			}
			$ret.='</ul><!-- loapp-photo -->';
		}

		head('<style type="text/css">
		.nav .sg-upload {display: block; float: left; height:21px; margin:0; }
		.nav .sg-upload .btn {margin:0; }
		.photocard {margin:0; padding:0; list-style-type:none;}
		.photocard>li {height:120px; margin:0 10px 10px 0; float:left; position;relative;}
		.photocard img {height:100%;}
		.photocard .iconset {right:10px; top:10px; z-index:1;}
		</style>');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'คณะกรรมการ',
				'leading' => _HEADER_BACK,
				'boxHeader' => 'true',
				'navigator' => new FundNavWidget($this->fundInfo),
				'trailing' => new DropBox([
					'children' => [
						$isEditable ? '<a class="sg-action" href="'.url('project/fund/'.$orgId.'/board.letter').'" data-rel="box" data-width="full"><i class="icon -material">assignment_turned_in</i><span>หนังสือแต่งตั้งคณะกรรมการ</span></a>' : NULL,
						$isEditable ? '<a class="sg-action" href="'.url('project/fund/'.$orgId.'/board.beover').'" data-rel="box" data-width="full"><i class="icon -material">reply_all</i><span>บันทึกคณะกรรมการหมดวาระ</span></a>' : NULL,
						$isEditable ? '<sep>' : NULL,
						'<a class="sg-action" href="'.url('project/fund/'.$orgId.'/board.all').'" data-rel="box" data-width="full"><i class="icon -material">assignment_ind</i><span>ทำเนียบกรรมการ</span></a>',
					],
				])
			]),
			'body' => new Container([
				'style' => 'padding: 16px 0;',
				'children' => [
					$ret,
					$isEditable ? new FloatingActionButton([
						'children' => ['<a class="sg-action btn -floating -circle48" href="'.url('project/fund/'.$orgId.'/board.new').'" data-rel="box" data-width="470px"><i class="icon -person-add -white"></i></a>'],
					]) : NULL,
				],
			]),
		]);
	}
}
?>