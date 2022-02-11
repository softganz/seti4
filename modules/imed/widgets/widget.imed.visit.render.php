<?php
/**
* iMed Widget : Render Visit Unit
* Created 2021-08-20
* Modify  2021-08-20
*
* @param Object $visitInfo
* @param Object $options
* @return Widget
*
* @usage import('model:imed.visit.render')
* @uasge new ImedVisitRenderWidget($visitInfo, $options)
*/

$debug = true;

class ImedVisitRenderWidget extends Widget {
	var $seqId;
	var $psnId;
	var $options;
	var $visitInfo;

	function __construct($visitInfo, $options = '{}') {
		$this->seqId = SG\getFirst($visitInfo->seqId, $visitInfo->seq);
		$this->psnId = SG\getFirst($visitInfo->psnId, $visitInfo->psnid, $visitInfo->pid);
		$this->visitInfo = $visitInfo;
		$this->options = $options;
	}

	function build() {
		$defaults = '{debug:false, showEdit: true, refApp: null}';
		$options = SG\json_decode($this->options, $defaults);
		$debug = $options->debug;

		$visitInfo = $this->visitInfo;
		$seqId = $this->seqId;
		$psnId = $this->psnId;

		// debugMsg($this,'$this');
		// debugMsg($options, '$options');

		if (empty($seqId)) return '...';

		$isEdit = $options->showEdit && (is_admin('imed') || i()->uid == $visitInfo->uid);

		$isAdmin = is_admin('imed');

		$barthel = R::Model('imed.barthel.level', $visitInfo->score);

		$q9Level = '';
		if (!is_null($visitInfo->q2_score)) {
			if ($visitInfo->q9_score < 7) $q9Level = '-level-0';
			else if ($visitInfo->q9_score <= 12) $q9Level = '-level-1';
			else if ($visitInfo->q9_score <= 18) $q9Level = '-level-2';
			else if ($visitInfo->q9_score >= 19) $q9Level = '-level-3';
		}

		$posterUrl = '<a class="sg-action" href="'.url('imed/u/'.$visitInfo->uid, ['ref' => $options->refApp]).'" data-rel="box" data-webview="'.$visitInfo->ownerName.'" data-width="full" data-height="80%">';

		switch ($options->refApp) {
			case 'app':
				$patientUrl = '<a class="sg-action" href="'.url('imed/app/'.$psnId).'" data-webview="'.$visitInfo->patient_name.'">';
				break;

			case 'psyc':
				$patientUrl = '<a class="sg-action" href="'.url('imed/psyc/'.$psnId).'" data-webview="'.$visitInfo->patient_name.'">';
				break;

			default:
				$patientUrl = '<a class="sg-action" href="'.url('imed/patient/'.$psnId.'/view').'" data-rel="box" data-width="480" data-max-height="80%" x-role="patient" data-pid="'.$psnId.'">';
				break;
		}

		$photoAlbum = new Ui([
			'type' => 'album',
			'class' => '-justify-left',
			'forceBuild' => true,
		]);

		if ($visitInfo->photos) {
			foreach (explode(',',$visitInfo->photos) as $photoItem) {
				list($photoFileID,$photoFileName) = explode('|', trim($photoItem));
				if (!$photoFileID) continue;

				$photoInfo = imed_model::upload_photo($photoFileName);

				$Ui = new Ui('span');
				$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
				if ($isEdit) {
					$Ui->add('<a class="sg-action -no-print" href="'.url('imed/api/visit/'.$psnId.'/photo.delete/'.$seqId, array('f'=>$photoFileID)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-before="remove:parent li"><i class="icon -material">cancel</i></a>');
				}

				$photoAlbum->add(
					'<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
					. $Ui->build(),
					array(
						'class' => 'sg-action -hover-parent',
						'href' => $photoInfo->_url,
						'data-rel' => 'img',
						'data-group' => 'imed-'.$seqId,
						'onclick' => '',
					)
				);
			}
		}

		if ($visitInfo->needItems) {
			$stmt = 'SELECT n.*, c.`name` FROM %imed_need% n LEFT JOIN %imed_stkcode% c ON c.`stkid` = n.`needtype` WHERE `needid` IN ( :needid )';
			$needDbs = mydb::select($stmt, ':needid', 'SET:'.$visitInfo->needItems);

			$urgencyList = array(1 => 'รอได้', 5 => 'เร่งด่วน', 9=> 'เร่งด่วนมาก');

			$needWidget = new Container([
				'class' => '-needs',
			]);
			foreach ($needDbs->items as $rs) {
				$needWidget->children(
					new ListTile([
						'class' => '-need -level-'.$rs->urgency.' '.($rs->status ? '-done' : '-wait'),
						'title' => 'ต้องการ '.$rs->name.' '
							. ($rs->status ? 'ดำเนินการเรียบร้อย' : $urgencyList[$rs->urgency]),
							// . ($rs->detail ? '<p>('.nl2br($rs->detail).')</p>' : '')
						'leading' => '<a class="sg-action -status" '.($isEdit ? 'href="'.url('imed/api/visit/'.$psnId.'/need.status/'.$seqId, ['id' => $rs->needid, 'ref' => $options->refApp]).'" data-rel="#imed-visit-'.$seqId.'" data-ret="'.url('imed/visit/'.$psnId.'/item/'.$seqId) : '').'"><i class="icon -material">'.($rs->status ? 'done_all' : 'done').'</i></a>',
						'trailing' => $isEdit ? new Row([
							'tagName' => 'nav',
							'class' => 'nav -icons',
							'children' => [
								'<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/form.need/'.$seqId, ['id' => $rs->needid, 'ref' => $options->refApp]).'" data-rel="box" data-width="full"><i class="icon -material -gray">edit</i></a>',
								'<a class="sg-action" href="'.url('imed/api/visit/'.$psnId.'/need.delete/'.$seqId, array('id'=>$rs->needid)).'" data-rel="none" data-before="remove:parent .-need" data-title="ลบความต้องการ" data-confirm="ต้องการลบความต้องการ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>',
							], // children
						]) : NULL, // Row
					])
				);
			}
			// if ($needDbs->count() % 2) $needWidget->add('&nbsp;', '{class: "-empty"}');
		}


		$hasVitalsign = $visitInfo->weight || $visitInfo->height || $visitInfo->temperature || $visitInfo->pulse || $visitInfo->respiratoryrate || $visitInfo->sbp || $visitInfo->dbp;
		$hasBarthel = !is_null($visitInfo->score);

		$infoBar = '';
		if ($visitInfo->weight != 0) $infoBar .= '<span>น้ำหนัก '.$visitInfo->weight.' ก.ก. </span>';
		if ($visitInfo->height != 0) $infoBar .= '<span>ส่วนสูง '.$visitInfo->height.' ซ.ม. </span>';
		if ($visitInfo->temperature != 0) $infoBar .= '<span>อุณหภูมิ '.$visitInfo->temperature.'<sup>&deg;</sup>C </span>';
		if ($visitInfo->pulse != 0) $infoBar .= '<span>ชีพจร '.$visitInfo->pulse.' ครั้ง/นาที </span>';
		if ($visitInfo->respiratoryrate != 0) $infoBar .= '<span>อัตราการหายใจ '.$visitInfo->respiratoryrate.' ครั้ง/นาที </span>';
		if ($visitInfo->sbp) $infoBar .= '<span>ความดันโลหิต '.$visitInfo->sbp.'/'.$visitInfo->dbp.' มม.ปรอท </span>';
		if ($visitInfo->fbs) $infoBar .= '<span>น้ำตาลในเลือด  '.$visitInfo->fbs.' mg/dL </span>';
		if ($hasBarthel) $infoBar .= '<span>Barthel ADL Index = '.$visitInfo->score.' '.$barthel->text.' </span>';
		if (!is_null($visitInfo->q2_score)) $infoBar .= '<span>ภาวะซึมเศร้า = '.SG\getFirst($visitInfo->q9_score,'ไม่มี').' </span>';


		$cameraStr = R()->appAgent ? 'ถ่ายภาพ' : 'อัพโหลดภาพถ่าย';

		return new Widget([
			'children' => [
				new ListTile([
					'crossAxisAlignment' => 'start',
					'title' => $posterUrl.$visitInfo->ownerName.'</a>',
					'subtitle' => '<span class="-visit-patient">'
						. ($visitInfo->service ? ' {tr:'.$visitInfo->service.'}' : '')
						. ($psnId ? ' '.$patientUrl.$visitInfo->prename.''.$visitInfo->patient_name.'</a>':'')
						.'</span><!-- -visit-patient -->'
						. '<span class="timestamp"> เมื่อ '
						. ($visitInfo->timedata == $visitInfo->created ? sg_date($visitInfo->timedata,'ว ดด ปปปป H:i').' น.' : sg_date($visitInfo->timedata,'ว ดด ปปปป')). ' '
						. ($isAdmin ? '@'.sg_date($visitInfo->created,'H:i').' on '.$visitInfo->appagent : '')
						. '</span><!-- timestamp -->',
					'leading' => $posterUrl.'<img class="profile-photo" src="'.model::user_photo($visitInfo->username).'" width="32" height="32" alt="" /></a>',
					'trailing' => new DropBox([
						'children' => [
							$isEdit ? '<a class="sg-action" href="'.url('imed/api/visit/'.$psnId.'/delete/'.$seqId).'" title="ลบรายการบันทึกนี้ทิ้ง" data-rel="notify" data-title="ลบรายการบันทึก" data-confirm="ลบรายการบันทึกนี้ทิ้ง กรุณายืนยัน?" data-before="remove:#imed-visit-'.$seqId.'"><i class="icon -material">delete</i><span>ลบบันทึกเยี่ยมบ้าน</span></a>' : '',
						], // children
					]), // DropBox
				]),

				// Detail
				new Container([
					'class' => 'detail',
					'child' => $isEdit ?
						view::inlineedit(
							[
								'group' => 'service',
								'fld' => 'rx',
								'tr' => $seqId,
								'psnId' => $psnId,
								'button' => 'yes',
								'ret' => 'text',
								'value' => $visitInfo->rx,
							],
							str_replace("\n",'<br />',$visitInfo->rx),
							$isEdit
							,'textarea'
						) : nl2br($visitInfo->rx),
				]), // Container

				$photoAlbum->build(false),

				$visitInfo->needItems ? $needWidget : NULL,

				$infoBar ? '<div id="vitalsign-'.$seqId.'"class="-vitalsign">'._NL.$infoBar.'</div><!-- vitalsign-detail -->'._NL : NULL,

				// Bottom Navigator
				$psnId && $seqId && $isEdit ? new Row([
					'tagName' => 'nav',
					'class' => 'nav -card',
					'mainAxisAlignment' => 'spacearound',
					'crossAxisAlignment' => 'center',
					'children' => [
						'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('imed/api/visit/'.$psnId.'/photo.upload/'.$seqId).'" data-rel="#imed-visit-'.$seqId.' .ui-album" data-append="li" data-class="ui-item sg-action -hover-parent"><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>'.$cameraStr.'</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>',
						'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/form.need/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full" data-options=\'{"class": "-expand"}\'><i class="icon -material">how_to_reg</i><span>ความต้องการ</span></a>',
						'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/form.vitalsign/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full"><i class="icon -material '.($hasVitalsign?'-has':'-not').'">monitor_heart</i><span>{tr:Vital Signs}</span></a>',
						'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/form.barthel/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full"><i class="icon -local -barthel-'.$barthel->level.'"></i><span>{tr:Barthel ADL Index}</span></a>',
						'<a class="sg-action btn -link" href="'.url('imed/visit/'.$psnId.'/qt/'.$seqId, ['ref' => $options->refApp]).'" data-rel="box" data-width="full"><i class="icon -material">fact_check</i><span>แบบบันทึก</span></a>',
					], // children
				]) : NULL,

				// $cardUi->count() ? '<nav class="nav -card">'.$cardUi->build().'</nav>' : NULL,
				// print_o($visitInfo, '$visitInfo'),
			],
		]);
	}
}
?>