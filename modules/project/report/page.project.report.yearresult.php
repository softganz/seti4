<?php
/**
 * รายงานผลลัพธ์โครงการ รายปี
 *
 */
function project_report_yearresult($self) {
	R::View('project.toolbar', $self, 'รายงานผลลัพธ์โครงการ ณ วันที่ '.sg_date('ว ดด ปปปป'), 'report');

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
	if ($year) $where=sg::add_condition($where,'p.`pryear`=:year','year',$year);
	if ($province) $where=sg::add_condition($where,'p.`changwat`=:changwat','changwat',$province);
	if ($prset) $where=sg::add_condition($where,'p.`projectset`=:prset','prset',$prset);

	$stmt='SELECT SUM(`num1`) targetDirect, SUM(`num2`) targetOther
				FROM %project_tr% tr
				LEFT JOIN %project% p USING(`tpid`)
				WHERE (tr.`formid`="ส.3" AND tr.`part`="title") '
					.($where?' AND '.implode(' AND ',$where['cond']):'')
				.' LIMIT 1';

	$stmt='SELECT SUM(`num1`) targetDirect, SUM(`num2`) targetOther
				FROM %project_tr% tr
				LEFT JOIN %project% p USING(`tpid`)
				WHERE (tr.`formid`="ส.3" AND tr.`part`="title") '
					.($where?' AND '.implode(' AND ',$where['cond']):'')
				.' LIMIT 1';
	$rs=mydb::select($stmt,$where['value']);
	$targetDirect=$rs->targetDirect;
	$targetOther=$rs->targetOther;

	$stmt='SELECT p.`tpid`, t.`title`, tr.`text6`, tr.`text7`
				FROM %project_tr% tr
					LEFT JOIN %project% p USING(`tpid`)
					LEFT JOIN %topic% t USING(`tpid`)
				WHERE (tr.`formid`="ส.3" AND tr.`part`="title" AND tr.`text6`!="" ) '
					.($where?' AND '.implode(' AND ',$where['cond']):'')
				.' ';
	$dbs=mydb::select($stmt,$where['value']);
	$desc='<ol>';
	foreach ($dbs->items as $rs) {
		$desc.='<li><h3>โครงการ : '.$rs->title.'</h3><ul>'
						.($rs->text6?'<li><strong>ทางตรง : </strong>'.$rs->text6.'</li>':'')
						.($rs->text7?'<li><strong>ทางอ้อม : </strong>'.$rs->text7.'</li>':'')
						.'</ul></li>';
	}
	$desc.='</ol>';

	$no=0;
	$tables = new Table();
	$tables->class='item report';
	$tables->thead=array('no'=>'','เป้าหมายตัวชี้วัดของแผนฯ','ผลงานเชิงปริมาณ','ผลงานเชิงคุณภาพ','คำนิยาม');

	$tables->rows[]=array('<td colspan="5"><h2>1. ประชากรกลุ่มเป้าหมายที่ได้รับประโยชน์จากการดำเนินโครงการทั้งทางตรงและทางอ้อม</h2></td>');
	$tables->rows[]=array(
		++$no,
		'ประชากรกลุ่มเป้าหมายที่ได้รับประโยชน์จากการดำเนินโครงการทั้งทางตรงและทางอ้อม',
		'ผู้รับประโยชน์ทางตรง <strong>'.number_format($targetDirect).' คน</strong><br />ผู้รับประโยชน์ทางอ้อม <strong>'.number_format($targetOther).' คน</strong>',
		'ระบุ<strong>พฤติกรรมสุขภาพ</strong>และจำนวนกลุ่มเป้าหมายที่มีพฤติกรรมนั้น ๆ เช่น การปลูกและบริโภคผักปลอดสารพิษ จำนวน 150 ครัวเรือน , จำนวนผู้ลดละบุหรี่ 72 คน เป็นต้น'
		.$desc,
		''
	);





	$stmt='SELECT p.`tpid`, t.`title`, tr.`text1`, tr.`text2`, tr.`text3`
				FROM %project_tr% tr
					LEFT JOIN %project% p USING(`tpid`)
					LEFT JOIN %topic% t USING(`tpid`)
				WHERE (tr.`formid`="ส.3" AND tr.`part`="outcome"
								AND (tr.`text1`!="" OR tr.`text2`!="" OR tr.`text3`!="" )) '
					.($where?' AND '.implode(' AND ',$where['cond']):'')
				.' ';
	$dbs=mydb::select($stmt,$where['value']);
	$outcomeAmt=$dbs->_num_rows;

	$total1=$total2=$total3=0;
	$desc1=$desc2=$desc3='<ol>';
	foreach ($dbs->items as $rs) {
		if (strlen($rs->text1)>10) {
			$desc1.='<li><h3>โครงการ : '.$rs->title.'</h3><p>'.$rs->text1.'</p></li>';
			$total1++;
		}
		if (strlen($rs->text2)>10) {
			$desc2.='<li><h3>โครงการ : '.$rs->title.'</h3><p>'.$rs->text2.'</p></li>';
			$total2++;
		}
		if (strlen($rs->text3)>10) {
			$desc3.='<li><h3>โครงการ : '.$rs->title.'</h3><p>'.$rs->text3.'</p></li>';
			$total3++;
		}
	}
	$desc1.='</ol>';
	$desc2.='</ol>';
	$desc3.='</ol>';

	$tablesTotal = new Table();
	$tablesTotal->rows[]=array('1) เกิดกฏ กติกา ระเบียบ หรือมาตรการชุมชน',$total1.' พื้นที่');
	$tablesTotal->rows[]=array('2) เกิดกลไก ระบบ หรือโครงสร้างชุมชนที่พัฒนาขึ้นใหม่',$total2.' พื้นที่');
	$tablesTotal->rows[]=array('3) เกิดต้นแบบ พื้นที่เรียนรู้ หรือแหล่งเรียนรู้ในระดับชุมชน',$total3.' พื้นที่');

	$tables->rows[]=array('<td colspan="5"><h2>2. การเกิดสภาพแวดล้อมหรือปัจจัยทางสังคมที่เอื้อต่อสุขภาวะของคน องค์กรและชุมชนในพื้นที่</h2></td>');
	$tables->rows[]=array(
		++$no,
		'การเกิดสภาพแวดล้อมหรือปัจจัยทางสังคมที่เอื้อต่อสุขภาวะของคน องค์กรและชุมชนในพื้นที่',
		'จำนวน <strong>'.$outcomeAmt.' แห่ง/พื้นที่</strong>',
		$tablesTotal->build()
		.'<h4>1) เกิดกฏ กติกา ระเบียบ หรือมาตรการชุมชน<br />จำนวน '.$total1.' พื้นที่</h4>'.$desc1
		.'<h4>2) เกิดกลไก ระบบ หรือโครงสร้างชุมชนที่พัฒนาขึ้นใหม่<br />จำนวน '.$total2.' พื้นที่</h4>'.$desc2
		.'<h4>3) เกิดต้นแบบ พื้นที่เรียนรู้ หรือแหล่งเรียนรู้ในระดับชุมชน<br />จำนวน '.$total3.' พื้นที่</h4>'.$desc3,
		''
	);





	$stmt='SELECT
				  tr.`gallery` catid
				, c.`name` leaderType
				, COUNT(DISTINCT `psnid`) amt
			FROM %project_tr% tr
				LEFT JOIN %project% p USING(`tpid`)
				LEFT JOIN %db_person% pn ON pn.`psnid`=tr.`parent`
				LEFT JOIN %tag% c ON c.`taggroup`="project:category" AND c.`catid`=tr.`gallery`
		WHERE (tr.`formid`="leader") '
			.($where?' AND '.implode(' AND ',$where['cond']):'')
		.' GROUP BY `catid`';
	$dbs=mydb::select($stmt,$where['value']);

	$total=0;
	$desc='<ol>';
	foreach ($dbs->items as $rs) {
		$total+=$rs->amt;
		$desc.='<li>'.$rs->leaderType.' จำนวน <strong>'.$rs->amt.' คน</strong></li>';
	}
	$desc.='</ol>';

	$tables->rows[]=array('<td colspan="5"><h2>3. กลุ่มเป้าหมายที่ได้รับการพัฒนาศักยภาพให้เป็นแกนนำในการทำกิจกรรมสร้างเสริมสุขภาพในพื้นที่ และแกนนำมีบทบาทในการทำให้โครงการประสบผลสำเร็จ</h2></td>');
	$tables->rows[]=array(
		++$no,
		'กลุ่มเป้าหมายที่ได้รับการพัฒนาศักยภาพให้เป็นแกนนำในการทำกิจกรรมสร้างเสริมสุขภาพในพื้นที่ และแกนนำมีบทบาทในการทำให้โครงการประสบผลสำเร็จ',
		'จำนวน <strong>'.$total.' คน</strong>',
		$desc,
		''
	);



	$stmt='SELECT COUNT(DISTINCT tr.`tpid`) bestpracticeCount
					FROM %project_tr% tr
						LEFT JOIN %topic% t USING(tpid)
						LEFT JOIN %project% p USING(tpid)
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` 
					WHERE (tr.`formid`="follow" AND tr.`part`="2.3.2" AND tr.`detail1` NOT IN("","-","ไม่มี") )'
					.($where?' AND '.implode(' AND ',$where['cond']):'')
					.' LIMIT 1';
	$rs=mydb::select($stmt,$where['value']);
	$bestpracticeCount=$rs->bestpracticeCount;

	$stmt='SELECT
						  tr.`trid`, tr.`tpid`, tr.`formid`, tr.`part`
						, p.`pryear`
						, p.`changwat`, cop.`provname`
						, t.`title`
						, tr.`detail1` `bestpractice`
						, tr.`text1` `howto`
						, tr.`text2` `result`
					FROM %project_tr% tr
						LEFT JOIN %topic% t USING(tpid)
						LEFT JOIN %project% p USING(tpid)
						LEFT JOIN %co_province% cop ON cop.provid=p.changwat 
					WHERE (tr.`formid`="follow" AND tr.`part`="2.3.2" AND tr.`detail1` NOT IN("","-","ไม่มี") )'
					.($where?' AND '.implode(' AND ',$where['cond']):'')
					.' ORDER BY CONVERT(t.`title` USING tis620) ASC';
	$dbs=mydb::select($stmt,$where['value']);
	$desc='<ol>';
	foreach ($dbs->items as $rs) {
		$desc.='<li><h3>ชื่อ Best Practice : '.$rs->bestpractice.'</h3>'
						.'<p><strong>โครงการ : '.$rs->title.'</strong><br />'
						.'<strong>วิธีการทำให้เกิด Best Practice : </strong>'.$rs->howto.'<br />'
						.'<strong>ผลของ Best Practice : </strong>'.$rs->result
						.'</p></li>';
	}
	$desc.'</ol>';

	$tables->rows[]=array('<td colspan="5"><h2>4. โครงการตัวอย่างที่ปฏิบัติการที่ดี (Best practice)</h2></td>');
	$tables->rows[]=array(
		++$no,
		'โครงการตัวอย่างที่ปฏิบัติการที่ดี (Best practice)',
		'จำนวน <strong>'.$bestpracticeCount.' โครงการ</strong>',
		$desc,
		''
	);





	$stmt='SELECT tr.trid, tr.tpid, tr.formid, tr.part,
						tr.`detail1` innovation,
						tr.`text1`, tr.`text2`,
						t.title, p.agrno, p.prid, p.pryear, tr.`period`,
						X(p.location) lat, Y(p.location) lng,
						p.project_status, p.project_status+0 project_statuscode,
						p.changwat, cop.provname
					FROM %project_tr% tr
						LEFT JOIN %topic% t USING(tpid)
						LEFT JOIN %project% p USING(tpid)
						LEFT JOIN %co_province% cop ON cop.provid=p.changwat 
					WHERE (tr.`formid`="follow" AND tr.`part`="2.3.1" AND tr.`detail1` NOT IN("","-","ไม่มี") )'
					.($where?' AND '.implode(' AND ',$where['cond']):'')
					.' GROUP BY tr.tpid'
					.' ORDER BY CONVERT(t.`title` USING tis620) ASC';
	$dbs=mydb::select($stmt,$where['value']);
	$desc='<ol>';
	foreach ($dbs->items as $rs) {
		$desc.='<li><h3>ชื่อนวัตกรรม : '.$rs->innovation.'</h3>'
						.'<p><strong>โครงการ : '.$rs->title.'</strong><br />'
						.'<strong>คุณลักษณะ/วิธีการทำให้เกิดนวัตกรรม : </strong>'.$rs->text1.'<br />'
						.'<strong>ผลของนวัตกรรม/การนำไปใช้ประโยชน์ : </strong>'.$rs->text2
						.'</p></li>';
	}
	$desc.='</ol>';

	$tables->rows[]=array('<td colspan="5"><h2>5. นวัตกรรมสร้างเสริมสุขภาพ</h2></td>');
	$tables->rows[]=array(
										++$no,
										'นวัตกรรมสร้างเสริมสุขภาพ',
										'จำนวน <strong>'.$dbs->_num_rows.' กรณี</strong>',
										$desc,
										''
										);

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
	.form-item {display: inline-block;}
	.report td:nth-child(2) {width:15%}
	.report td:nth-child(3) {width:20%}
	.report td:nth-child(4) {width:45%}
	.report td:nth-child(5) {width:20%}
	.report h2 {padding:10px;background:green; color:#fff;}
	.report h3 {margin:0; font-family:tahoma; font-size:1.2em;}
	</style>';
	return $ret;
}
?>