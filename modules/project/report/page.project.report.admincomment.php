<?php

	/**
	 * Send Document Report
	 *
	 */
	function project_report_admincomment($self) {
		R::View('project.toolbar', $self, 'บันทึกเจ้าหน้าที่', 'report');

		$year=SG\getFirst(post('y'));
		$province=post('p');
		$prset=post('s');

		$form = new Form('report', url(q()), 'project-report');

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
		$where=sg::add_condition($where,'tr.`formid`="admin" AND tr.`part`="comment"');
		if ($year) $where=sg::add_condition($where,'p.`pryear`=:year','year',$year);
		if ($province) $where=sg::add_condition($where,'p.`changwat`=:changwat','changwat',$province);
		if ($prset) $where=sg::add_condition($where,'p.`projectset`=:prset','prset',$prset);

		$stmt='SELECT p.`tpid`, tr.`trid`, tr.`formid`, tr.`part`,
						tr.`text1` `comment`, tr.`date1` `date`,
						t.`title`,
						p.`agrno`, p.`prid`, p.`pryear`,
						p.`project_status`, p.`project_status`+0 project_statuscode,
						p.`changwat`, cop.`provname`
					FROM %project_tr% tr
						LEFT JOIN %project% p USING(`tpid`)
						LEFT JOIN %topic% t USING(tpid)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` '
					.($where?'WHERE '.implode(' AND ',$where['cond']):'')
					.' ORDER BY `date` ASC';

		$dbs=mydb::select($stmt,$where['value']);

		$tables = new Table();
		$tables->thead=array('no'=>'','ปี','จังหวัด','ชื่อโครงการ','สถานะโครงการ');
		foreach ($dbs->items as $rs) {
 			$tables->rows[]=array(++$no,
 													$rs->pryear+543,
													$rs->provname,
													'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>'.'<br />'.sg_text2html($rs->comment),
													$rs->project_status,
													);
		}

		$ret .= $tables->build();
		//$ret.=print_o($dbs,'$dbs');

		$ret.='<script type="text/javascript"><!--
$(document).ready(function() {
	$("#project-report select").change(function() {
		notify("Loading...");
		$("#project-report").submit();
	});
});
--></script>';
		return $ret;
	}
?>