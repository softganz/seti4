<?php
/**
* Project :: Print Planning
* Created 2018-10-25
* Modify  2021-06-16
*
* @param Int $orgId
* @param Int $year
* @return Widget
*
* @usage project/planning/print/{orgId}/{year}
*/

$debug = true;

class ProjectPlanningPrint extends Page {
	var $orgId;
	var $year;

	function __construct($orgId = NULL, $year = NULL) {
		$this->orgId = $orgId;
		$this->year = $year;
	}

	function build() {
		$fundInfo = R::Model('project.fund.get', $this->orgId);
		if (!$fundInfo) return message('error','ไม่มีข้อมูลตามที่ระบุ');

		$orgId = $fundInfo->orgid;

		$isEdit = $fundInfo->right->edit || $fundInfo->right->trainer;

		$bigdata=new bigData();
		$bigKey='project.yearplanning.'.$this->year;
		$yearPlanning=array();
		foreach ($bigdata->getField('*',$bigKey,$orgId) as $item) {
			$yearPlanning[$item->fldname]=$item;
		}

		$inlineAttr = array();
		if ($isEdit) {
			$inlineAttr['data-tpid']=$orgId;
			$inlineAttr['class'] = 'sg-inline-edit';
			$inlineAttr['data-update-url']=url('project/edit/tr');
			if (debug('inline')) $inlineAttr['data-debug']='inline';
		}
		$inlineAttr['class'] .= ' project-planning';

		$ret.='<div id="project-info" '.sg_implode_attr($inlineAttr).'>'._NL;

		$ret.='<div class="box"><h3>1. บริบทพื้นที่/สถานการณ์ชุมชน</h3>'
			.view::inlineedit(array('group'=>'bigdata:'.$bigKey.':areasummary','fld'=>'areasummary', 'tr'=>$yearPlanning['areasummary']->bigid, 'ret'=>'html','class'=>'-fill', 'orgid'=>$orgId),$yearPlanning['areasummary']->flddata,$isEdit,'textarea')
			.'</div>';
	/*
	'bigdata' :
		//$group='bigdata';
		$values['fldname']=$fld;
		$values['flddata']=$value;
		$values['keyid']=$tpid;
		$values['keyname']=$part;

	<abbr class="checkbox"><input type="checkbox" data-type="checkbox" class="{$datainput}" name="act-target-1-1" data-group="bigdata:project.develop:act-target-1-1" data-fld="act-target-1-1" value="1" data-removeempty="yes" /> 7.4.1.1 การสำรวจข้อมูลสุขภาพ การจัดทำทะเบียนและฐานข้อมูลสุขภาพ<br /></abbr>
	*/
		$ret.='<div class="box"><h3>2. ข้อทุนกองทุน</h3>'
			.view::inlineedit(array('group'=>'bigdata:'.$bigKey.':fundinfo','fld'=>'fundinfo', 'tr'=>$yearPlanning['fundinfo']->bigid, 'ret'=>'html','class'=>'-fill', 'orgid'=>$orgId),$yearPlanning['fundinfo']->flddata,$isEdit,'textarea')
			.'</div>';

		$ret.='</div>';

		$ret.='<div class="box"><h3>3. แผนการดำเนินงานตามประเด็น</h3></div>';


		$ret.='<div class="project-yearplan">';
		mydb::where('p.`prtype`="แผนงาน"');
		if ($orgId) mydb::where('t.`orgid`=:orgid',':orgid',$orgId);
		mydb::where('p.`pryear`=:year',':year',$this->year);
		$stmt='SELECT
			p.*
			, t.`title`, t.`orgid`
			, o.`shortname`, o.`name` `orgName`
			, pt.`refid` `plangroup`
			, t.`created`
			, (SELECT COUNT(*) FROM %project% cp WHERE cp.`projectset`=p.`tpid`) `childcount`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_tr% pt ON pt.`tpid`=p.`tpid` AND pt.`formid`="info" AND pt.`part`="title"
			%WHERE%
			HAVING `plangroup` IS NOT NULL
			ORDER BY `pryear` DESC, `tpid` DESC';
		$dbs=mydb::select($stmt);

		foreach ($dbs->items as $rs) {
			$ret.=R::Page('project.planning.view',NULL,$rs->tpid,'print');
			$ret.='<hr class="pagebreak" />';
		}

		$ret.='</div><!-- project-yearplan -->';

		$ret .= '<style type="text/css">
		@media print {
			.module-project .box {margin: 0;}
			table.item {display: table; width: 100%; margin: 0;}
		}
		</style>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนงานกองทุน - '.$fundInfo->name,
				'navigator' => [
					R::View('project.nav.org', $fundInfo),
				], // navigator
			]), // AppBar
			// R::View('project.toolbar',$self,'แผนงานกองทุน - '.$fundInfo->name, 'org', $fundInfo);
			'children' => [
				'<h2 class="title">แผนการดำเนินงาน '.$fundInfo->name.'<br />ประจำปีงบประมาณ '.($this->year+543).'</h2>',
				$ret,
			],
		]);
	}
}
?>