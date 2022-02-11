<?php
/**
* Module :: Description
* Created 2021-09-24
* Modify  2021-09-24
*
* @param Int $projectSetId
* @return Widget
*
* @usage project/proposal/new/{projectSetId}
*/

$debug = true;

import('model:code.php');

class ProjectProposalNew extends Page {
	var $projectSetId;

	function __construct($projectSetId = NULL) {
		$this->projectSetId = $projectSetId;
	}

	function build() {
		$maxDevProjectAllow = cfg('PROJECT.DEVELOP.MAX_PER_USER');

		if (!user_access('create project proposal')) return message('error','access denied');

		if (!user_access('administer contents')) {
			$dbs = mydb::select('SELECT `tpid` FROM %topic% WHERE type = "project-develop" AND uid = :uid AND `status` IN (1,2,3)',':uid',i()->uid);
			if ($maxDevProjectAllow == 1 && $dbs->_num_rows == 1) {
				location('project/proposal/'.$dbs->items[0]->tpid);
			} else if ($maxDevProjectAllow == 0 || $dbs->_num_rows < $maxDevProjectAllow) {
				// Allow to add new develop project
			} else {
				location('project/my');
			}
		}

		// Check start date in config
		$curDate = date('Y-m-d H:i');
		$cfgStartDate = cfg('project.develop.startdate');

		if ($cfgStartdate) {
			if ( ($curDate >= cfg('project.develop.startdate') && $curDate<=cfg('project.develop.enddate')) ) {
				; // do nothing
			} else {
				$msg = 'ปิดรับการพัฒนาโครงการ : ขออภัย : ช่วงนี้งดรับพัฒนาโครงการใหม่<br />';

				$cfgEndDate=cfg('project.develop.enddate');
				if (empty($cfgStartDate) || $cfgStartDate<$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไปยังไม่ได้กำหนด';
				else if ($cfgStartDate>$curDate) $msg.='ช่วงเวลาในการเปิดรับพัฒนาโครงการครั้งต่อไป คือ <strong>'.sg_date($cfgStartDate,'ว ดดด ปปปป H:i').' น. - '.sg_date($cfgEndDate,'ว ดดด ปปปป H:i').' น.</strong>';

				$ret.=message('error',$msg);

				//.(cfg('project.develop.startdate')?'<br />ช่วงเวลาเปิดรับพัฒนาโครงการคือ '.sg_date(cfg('project.develop.startdate'),'ว ดดด ปปปป H:i').' น. ถึง '.sg_date(cfg('project.develop.enddate'),'ว ดดด ปปปป H:i').' น.':''));
				return $ret;
			}
		}


		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เริ่มพัฒนาโครงการใหม่',
				'boxHeader' => true,
			]),
			'body' => new Container([
				'children' => [
					new Form([
						'variable' => 'topic',
						'action' => url('project/proposal/create'),
						'id' => 'edit-topic',
						'class' => 'sg-form',
						'checkValid' => true,
						'children' => [
							'previd' => ['type' => 'hidden', 'value' => post('previd')],
							$this->projectSetId ? (function() {
								$projectParentTitle = mydb::select('SELECT `title` FROM %topic% WHERE `tpid` = :tpid LIMIT 1',':tpid',$this->projectSetId)->title;
								return [
									'children' => [
										'<b>ภายใต้ชุดโครงการ : '.$projectParentTitle.'</b>',
										'parent' => [
											'type'=>'hidden',
											'label'=>'ชุดโครงการ:',
											'value' => $this->projectSetId,
										],
									],
								];
								})()
								:
								(function() {
								$children = [];
								$stmt = 'SELECT
									*
									FROM %project% p
										LEFT JOIN %topic% USING(`tpid`)
									WHERE (`prtype` = "ชุดโครงการ" AND `project_status` = "กำลังดำเนินโครงการ") OR (`tpid` = :projectset)
									ORDER BY `title` ASC';
								$prSets = mydb::select($stmt, ':projectset',$this->projectSetId);
								if ($prSets->_num_rows) {
									$selectOptions  = array();
									foreach ($prSets->items as $item) $selectOptions[$item->tpid]=$item->title;
									$children['parent'] = [
										'type'=>$prSets->_num_rows<=5?'radio':'select',
										'label'=>'ชุดโครงการ:',
										'require'=>true,
										'options'=>$selectOptions,
										'value' => $this->projectSetId,
									];
								}
								return ['children' => $children];
							})(),

							'title' => [
								'type' => 'text',
								'label' => 'ชื่อโครงการที่จะเริ่มพัฒนาใหม่',
								'require' => true,
								'class' => '-fill',
								'value' => htmlspecialchars($post->title),
								'placeholder' => 'ระบุชื่อโครงการที่ต้องการเสนอ',
							],
							'pryear' => [
								'type' => 'radio',
								'label' => 'ประจำปี :',
								'require' => true,
								'options' => (function() {
									$options = [];
									for ($year = date('Y') - 1; $year <= date('Y') + 2; $year++) {
										$options[$year] = $year + 543;
									}
									return $options;
								})(),
								'value' => SG\getFirst($post->pryear,date('Y')),
							],
							'areacode' => ['type' => 'hidden', 'value' => $rs->areacode],
							'changwat' => [
								'type' => 'select',
								'label' => 'พื้นที่ดำเนินการ:',
								'class' => 'sg-changwat -fill',
								'require' => true,
								'options' => [
										-1 => '=== เลือกพื้นที่ ===',
										'TH' => '++ ทั้งประเทศ',
										'ระดับภาค' => [
											1 => '++ ภาคกลาง',
											3 => '++ ภาคตะวันออกเฉียงเหนือ',
											5 => '++ ภาคเหนือ',
											8 => '++ ภาคใต้',
										],
										'ระดับจังหวัด' => ChangwatModel::items(),
									],
								'attr' => 'data-altfld="#edit-topic-areacode"',
							],
							'ampur' => [
								'type' => 'select',
								'class' => 'sg-ampur -fill -hidden',
								'options' => array('' => '== เลือกอำเภอ =='),
								'attr' => 'data-altfld="#edit-topic-areacode"',
							],
							'tambon' => [
								'type' => 'select',
								'class' => 'sg-tambon -fill -hidden',
								'options' => array('' => '== เลือกตำบล =='),
								'attr' => 'data-altfld="#edit-topic-areacode"',
							],
							'save' => [
								'type'=>'button',
								'value'=>'<i class="icon -save -white"></i><span>เริ่มพัฒนาโครงการ</span>',
								'container' => array('class'=>'-sg-text-right'),
							],
						], // children
					]), // Form
				], // children
			]), // Container
		]);
	}
}
?>