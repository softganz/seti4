<?php

/**
 * Send Document Report
 *
 */
function project_report_leader($self) {
	R::View('project.toolbar', $self, 'รายชื่อแกนนำ/ผู้นำการเปลี่ยนแปลงด้านการสร้างเสริมสุขภาพ', 'report');

	$year=SG\getFirst(post('y'));
	$province=post('p');
	$prset=post('s');

	$form = new Form('report', url(q()), 'project-report');
	$form->config->method='get';

	$form->year->type='select';
	$form->year->name='y';
	$form->year->options[NULL]='--- ทุกปี ---';
	foreach (mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->items as $item) {
		$form->year->options[$item->pryear]='พ.ศ. '.($item->pryear+543);
	}
	$form->year->value=$year;

	$form->province->type='select';
	$form->province->name='p';
	$form->province->options[NULL]='--- ทุกจังหวัด ---';
	if ($property['region']=='all') {
		foreach ($dbs=mydb::select('SELECT `provid`, `provname` FROM %co_province% ORDER BY `provname` ASC')->items as $prov) {
			$form->province->options[$prov->provid]=$prov->provname;
		}
	} else {
		$form->province->options[80]='นครศรีธรรมราช';
		$form->province->options[81]='กระบี่';
		$form->province->options[82]='พังงา';
		$form->province->options[83]='ภูเก็ต';
		$form->province->options[84]='สุราษฎร์ธานี';
		$form->province->options[85]='ระนอง';
		$form->province->options[86]='ชุมพร';
		$form->province->options[90]='สงขลา';
		$form->province->options[91]='สตูล';
		$form->province->options[92]='ตรัง';
		$form->province->options[93]='พัทลุง';
		$form->province->options[94]='ปัตตานี';
		$form->province->options[95]='ยะลา';
		$form->province->options[96]='นราธิวาส';

		// For sbpac
		// $form->province->options[90]='สงขลา';
		// $form->province->options[94]='ปัตตานี';
		// $form->province->options[95]='ยะลา';
		// $form->province->options[96]='นราธิวาส';
	}
	$form->province->value=$province;

	$form->prset->type='select';
	$form->prset->name='s';
	$form->prset->options[NULL]='--- ทุกชุดโครงการ ---';
	foreach (project_model::get_project_set() as $item) {
		$form->prset->options[$item->tid]=$item->name;
	}
	$form->prset->value=$prset;


	$form->submit->type='submit';
	$form->submit->items->go='ดูรายงาน';

	$ret .= $form->build();

	$where=array();
	$where=sg::add_condition($where,'tr.`formid`="follow" AND tr.`part`="2.3.3" AND tr.`detail1`!="" ');
	if ($year) $where=sg::add_condition($where,'p.`pryear`=:year','year',$year);
	if ($province) $where=sg::add_condition($where,'p.`changwat`=:changwat','changwat',$province);
	if ($prset) $where=sg::add_condition($where,'p.`projectset`=:prset','prset',$prset);

	$stmt='SELECT p.`tpid`, tr.`trid`, tr.`formid`, tr.`part`,
						tr.`detail1` `name`, tr.`detail2` `address`, tr.`text1` `description`, tr.`period`,
						t.`title`,
						p.`agrno`, p.`prid`, p.`pryear`,
						p.`project_status`, p.`project_status`+0 project_statuscode,
						p.`changwat`, cop.`provname`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(tpid)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` '
					.($where?'WHERE '.implode(' AND ',$where['cond']):'')
					.' GROUP BY `name`,tr.`tpid` ORDER BY `name` ASC';

	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead=array('no'=>'','ชื่อ-สกุล','ที่อยู่','จังหวัด','เลขที่ข้อตกลง-ชื่อโครงการ','สถานะโครงการ');
	foreach ($dbs->items as $rs) {
			$tables->rows[]=array(++$no,
					'<a href="'.url('paper/'.$rs->tpid.'/member/trainer/post/follow/period/'.$rs->period).'">'.$rs->name.'</a>',
					$rs->address,
				$rs->provname,
				'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->agrno.'<br />'.$rs->title.'</a>',
				$rs->project_status,
			);
			$tables->rows[]=array('','<td colspan="5">'.$rs->description.'</td>');
	}

	$ret .= $tables->build();
//		$ret.=print_o($dbs,'$dbs');

	$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
$("#project-report select").change(function() {
	notify("Loading...");
	$("#project-report").submit();
});
});
--></script>
<style type="text/css">
.item tr:nth-child(2n+1)>td {border-bottom:none;}
.form-item {display: inline-block;}
</style>';
	return $ret;
}
?>