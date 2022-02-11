<?php
/**
* Project :: Follow Problem Information
* Created 2022-02-01
* Modify  2022-02-01
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/{id}/info.problem
*/

class ProjectinfoProblem extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$projectInfo = $this->projectInfo;

		//$isEdit=$action=='edit';
		$isEdit = $projectInfo->info->isEdit;

		$basicInfo = reset(SG\getFirst(project_model::get_tr($this->projectId, 'info:basic')->items['basic'], []));

		$ret = '';

		$ret .= '<div id="project-info-problem-'.$this->projectId.'">';

		$tables = new Table();
		$tables->thead = ['no' => '', 'สถานการณ์ปัญหา', 'icons -c1 -center' => '', 'amt -size' => 'ขนาด', 'icons -c1' => ''];

		foreach ($projectInfo->problem as $rs) {
			$tables->rows[] = array(
				++$no,
				($rs->refid
				?
					view::inlineedit(array('class'=>'-title -fill'), $rs->problem, false)
				:
					view::inlineedit(
						array(
							'group'=>'tr:info:problem',
							'fld'=>'detail1',
							'tr'=>$rs->trid,
							'class'=>'-title -fill',
							'placeholder'=>'ระบุสถานการณ์ปัญหา'
						),
						$rs->problem,
						$isEdit
					)
				)
				//.print_o(json_decode('{"rel":"parent:.item"}'),'$a')
				//.print_o($rs,'$rs')
				.'<div id="project-info-problem-detail-'.$rs->trid.'" class="project-info-problem-detail-item">'.($rs->detailproblem?sg_text2html($rs->detailproblem):'').'</div>'
				,
				$isEdit ? '<a class="sg-action" href="'.url('project/'.$this->projectId.'/info/problem.detail/'.$rs->trid,array('ref'=>$rs->refid)).'" data-rel="#project-info-problem-detail-'.$rs->trid.'"><i class="icon -material -gray">edit_note</i></a>' : '',
				view::inlineedit(
					array(
						'group'=>'tr:info:problem',
						'fld'=>'num1',
						'tr'=>$rs->trid,
						'class'=>'-fill',
						'ret'=>'numeric',
						'options'=>'{placeholder: "?", done: "load->replace:#project-info-objective-'.$this->projectId.':'.url('project/'.$this->projectId.'/info.objective').'"}',
					),
					$rs->trid ? number_format($rs->problemsize,2) : '',
					$isEdit
				),
				$isEdit && $rs->trid ? '<span class="hover-icon -tr"><a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info/problem.remove/'.$rs->trid).'" data-rel="none" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" title="ลบรายการ"><i class="icon -cancel -gray"></i></a>' : '',
			);
		}


		if ($isEdit) {
			$stmt = 'SELECT p.*,pn.`name` `planName`
						FROM %tag% p
							LEFT JOIN %tag% pn ON pn.`taggroup` = "project:planning" AND CONCAT("project:problem:",pn.`catid`) = p.`taggroup`
						WHERE p.`process` IS NOT NULL AND p.`taggroup` IN
							(SELECT CONCAT("project:problem:",`refid`) FROM %project_tr% WHERE `tpid` = :projectId AND `formid` = "info" AND `part` = "supportplan")';
			$problemDbs = mydb::select($stmt,':projectId',$this->projectId);


			$ret .= '<form class="sg-form" method="post" action="'.url('project/'.$this->projectId.'/info/problem.save').'" data-checkvalid="yes" data-rel="refresh">';
			$form = new Form(NULL,url('project/'.$this->projectId.'/info.problem/edit'),NULL,'sg-form project-info-problem-form');

			if ($problemDbs->_num_rows) {
				$optionsObjective[''] = '==เลือกตัวอย่างสถานการณ์==';
				foreach ($problemDbs->items as $rs) {
					if ($this->__is_problem_exists($rs->taggroup,$rs->catid,$projectInfo->problem)) continue;
					$detail = json_decode($rs->description);
					$optionsObjective[$rs->planName][$rs->taggroup.':'.$rs->catid]=$rs->name;
				}
				$form->addField(
					'problemref',
					[
						'type' => 'select',
						'name' => 'problemref',
						'class' => '-fill',
						'require' => true,
						'options' => $optionsObjective,
					]
				);

				$tables->rows[] = array(
					++$no,
					$form->get('edit-problemref'),
					'',
					'<input class="form-text -numeric -require" type="text" name="problemsize" size="5" placeholder="0.00" />',
					'<button class="btn -link" type="submit"><i class="icon -add"></i></button>',
				);
			}
		}

		$ret .= $tables->build();


		if ($isEdit) {
			$ret .= '</form>';
			$ret .= '<nav class="nav -page -sg-text-right"><a class="btn -primary" href="javascript:void(0)" onclick=\'$("#project-info-problem-form").show();return false;\'><i class="icon -addbig -white"></i><span>เพิ่มสถานการณ์อื่น ๆ</span></a></nav>';
			$ret .= $this->__project_info_problem_form($projectInfo);
			$ret .= '<p><em>เลือกตัวอย่างสถานการณ์จากความสอดคล้องกับแผนงานที่ระบุไว้แล้ว หรือ ระบุสถานการณ์เพิ่มเติม ป้อนขนาดปัญหา แล้วบันทึก</em></p>';
		}

		$ret .= '<p><b>ความสำคัญของโครงการ สถานการณ์ หลักการและเหตุผล'.($isEdit ? ' (บรรยายเพิ่มเติม)':'').'</b></p>'
				. view::inlineedit(
						[
							'group' => 'info:basic',
							'fld' => 'text1',
							'tr' => $basicInfo->trid,
							'ret' => 'html',
							'options' => ['placeholder' => 'บรรยายสถานการณ์/หลักการและเหตุเพิ่มเติมได้ในช่องนี้'],
						],
						$basicInfo->text1,
						$isEdit,
						'textarea'
					)
				. _NL;

		//$ret .= print_o($problemDbs,'$problemDbs');
		//$ret .= print_o($projectInfo->problem,'$problem');
		$ret .= '</div><!-- project-info-problem-'.$this->projectId.' -->';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$ret,
				], // children
			]), // Widget
		]);
	}

	function __is_problem_exists($taggroup, $catid, $problem = NULL) {
		$found = false;
		//debugMsg('Check '.$taggroup.' Catid '.$catid);
		foreach ($problem as $rs) {
			if ($taggroup == $rs->tagname && $catid == $rs->refid) {
				$found = true;
				break;
			}
		}
		return $found;
	}

	function __project_info_problem_form($projectInfo) {
		$stmt='SELECT p.*,pn.`name` `planName`
						FROM %tag% p
							LEFT JOIN %tag% pn ON pn.`taggroup`="project:planning" AND CONCAT("project:problem:",pn.`catid`)=p.`taggroup`
						WHERE p.`taggroup` IN
							(SELECT CONCAT("project:problem:",`refid`) FROM %project_tr% WHERE `tpid`=:projectId AND `formid`="info" AND `part`="supportplan")';
		$problemDbs=mydb::select($stmt,':projectId',$this->projectId);


		$form = new Form(NULL, url('project/'.$this->projectId.'/info/problem.save'), 'project-info-problem-form', 'sg-form project-info-problem-form -hidden');
		$form->addData('rel', 'replace:#project-info-problem-'.$this->projectId);
		$form->addData('ret', url('project/'.$this->projectId.'/info.problem'));
		$form->addData('checkValid', true);
		$form->addConfig('title', 'เพิ่มสถานการณ์');

		$form->addField(
			'problemother',
			array(
				'type'=>'text',
				'name'=>'problemother',
				'label'=>'ระบุสถานการณ์อื่นๆ',
				'class'=>'-fill',
				'require'=>true,
				'placeholder'=>'ระบุสถานการณ์เพิ่มเติม',
			)
		);

		$form->addField(
			'problemsize',
			array(
				'type'=>'text',
				'name'=>'problemsize',
				'label'=>'ขนาด',
				'class'=>'-fill',
				'require'=>true,
				'placeholder'=>'0.00',
			)
		);

		$form->addField(
			'save',
			array(
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
				'pretext'=>'<a class="btn -link -cancel" href="javascript:void(0)" onclick=\'$("#project-info-problem-form").hide();return false;\'><i class="icon -cancel -gray"></i>{tr:CANCEL}</a>',
				'container' => '{class: "-sg-text-right"}'
				)
		);

		$ret.=$form->build();
		$ret.='<style type="text/css">
		.project-info-problem-form {margin:32px 16px; text-align:left; box-shadow:0 0 0 1px #eee inset;border-radius:4px;}
		</style>';

		//$ret.=print_o($problemDbs);

		return $ret;
	}

}
?>