<?php
function qt_group_course_summary($self) {
	R::View('toolbar',$self,'รายงานสรุปแบบประเมินผลหลักสูตร','qt.course');

	$form=new Form(NULL,url('qt/group/course/summary'),'form-report','sg-form form-report');
	$form->addData('rel',"#report-main");
	$form->addField(
		'network',
		array(
			'type'=>'select',
			'label'=>'เครือข่าย:',
			'options'=>array('ทุกเครือข่าย')+array(
				1=>'ชุมชนท้องถิ่นน่าอยู่',
				'ความมั่นทางอาหาร',
				'ท่องเที่ยวชุมชน',
				'ปัจจัยเสี่ยง เหล้า บุหรี่ ยาเสพติด',
				'การส่งเสริมกิจกรรมทางกาย',
				'การจัดการฐานทรัพยากรธรรมชาติและสิ่งแวดล้อม',
				'ความมั่นคงทางมนุษย์ เด็กและเยาวชน ผู้สูงอายุ ผู้ด้อยโอกาส ผู้พิการ',
				'กองทุนตำบล',
				99=>'อื่น ๆ'
			),
		)
	);

	$provDbs=mydb::select('SELECT p.`changwat`,cop.`provid`,cop.`provname` FROM %qtmast% q LEFT JOIN %db_person% p USING(`uid`) LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid` GROUP BY `changwat` HAVING `provid` IS NOT NULL ORDER BY CONVERT(`provname` USING tis620) ASC');
	$options['']='ทุกจังหวัด';
	foreach ($provDbs->items as $rs) {
		$options[$rs->changwat]=$rs->provname;
	}
	$form->addField(
						'prov',
						array(
							'type'=>'select',
							'label'=>'จังหวัด:',
							'options'=>$options,
						)
					);

	$form->addField(
						'age',
						array(
							'type'=>'select',
							'label'=>'ช่วงอายุ:',
							'options'=>array('ทุกช่วงอายุ'),//,'<5 ปี' , '6-10 ปี' , '11-15 ปี' , '16-20 ปี', '> 20 ปี'),
						)
					);

	$form->addField(
						'edu',
						array(
							'type'=>'select',
							'label'=>'การศึกษา:',
							'options'=>array('ทุกระดับการศึกษา')+array('x'=>'ต่ำกว่า ป.ตรี',6=>'ป.ตรี',7=>'ป.โท',8=>'ป.เอก'),
						)
					);

	$form->addField(
						'exp',
						array(
							'type'=>'select',
							'label'=>'ประสบการณ์:',
							'options'=>array('ทุกปีประสบการณ์'),
						)
					);

	$self->theme->navbar=$form->build();




	$ret.='<div id="report-main">';

	// FIXME: JOIN all network  

	//mydb::where('q.`qtform`=102');
	if (post('prov')) mydb::where('p.`changwat`=:changwat',':changwat',post('prov'));
	if (post('edu')) mydb::where('p.`educate`=:educate',':educate',post('edu'));
	if (post('network')) mydb::where('n.`tagname`="network" AND n.`num1`=:network',':network',post('network'));

	$stmt='SELECT `tpid`,t.`title`,COUNT(*) `amt`
				FROM %qtmast% q
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %person_group% g ON g.`groupname`="assessor" AND g.`uid`=q.`uid`
					LEFT JOIN %db_person% p ON p.`uid`=g.`uid`
					LEFT JOIN %person_tr% n ON n.`psnid`=p.`psnid` AND n.`tagname`="network"
				%WHERE%
				GROUP BY `tpid`;
				-- {sum:"amt",reset:false}';
	$dbs=mydb::select($stmt);
	//$ret.=print_o($dbs,'$dbs');

	$tables = new Table();
	$tables->caption='จำนวนแบบสอบถาม';
	$tables->thead=array('รายวิชา','amt'=>'จำนวนแบบสอบถาม');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->title,$rs->amt);
	}
	$tables->tfoot[]=array('รวม',$dbs->sum->amt);
	$ret.=$tables->build();

	//$ret.=print_o($dbs);

	$titleLists=array(
					1=>'เนื้อหา',
					'เอกสาร/คู่มือ',
					'วิทยากร',
					'การบรรยาย',
					'กิจกรรมกลุ่ม',
					'ความรู้/ทักษะ/ความสามารถ',
					'การนำความรู้ไปใช้ประโยชน์ในงานของเครือข่าย',
				);



	$stmt='SELECT
					`tpid`, t.`title`
					, AVG(`rate1`) `rate1`
					, AVG(`rate2`) `rate2`
					, AVG(`rate3`) `rate3`
					, AVG(`rate4`) `rate4`
					, AVG(`rate5`) `rate5`
					, AVG(`rate6`) `rate6`
					, AVG(`rate7`) `rate7`
					FROM 
						(SELECT
						  q.`qtref`
						, q.`tpid`
						, q.`uid`
						, SUM(IF(tr.`part`="RATE.1",tr.`rate`,NULL)) `rate1`
						, SUM(IF(tr.`part`="RATE.2",tr.`rate`,NULL)) `rate2`
						, SUM(IF(tr.`part`="RATE.3",tr.`rate`,NULL)) `rate3`
						, SUM(IF(tr.`part`="RATE.4",tr.`rate`,NULL)) `rate4`
						, SUM(IF(tr.`part`="RATE.5",tr.`rate`,NULL)) `rate5`
						, SUM(IF(tr.`part`="RATE.6",tr.`rate`,NULL)) `rate6`
						, SUM(IF(tr.`part`="RATE.7",tr.`rate`,NULL)) `rate7`
					FROM %qtmast% q
						LEFT JOIN %qttran% tr USING(`qtref`)
					WHERE q.`qtform`=102
					GROUP BY `qtref`
						) q
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %db_person% p ON p.`uid`=q.`uid`
					LEFT JOIN %person_tr% n ON n.`psnid`=p.`psnid` AND n.`tagname`="network"
				%WHERE%
				GROUP BY `tpid`
				ORDER BY `tpid`';
	$dbs=mydb::select($stmt);
	$totalSubject=$dbs->count();

	/*
	$ret.='<pre>';
$ret.='วิเคราะห์ตามรายวิชา ทีละรายการ
 - เครือข่าย
 - จังหวัด
 - ช่วงอายุ <20,21-40,41-60,>60
 - การศึกษา
 - ประสบการณ์ <5 , 6-10 , 11-15 , 16-20, > 20 ปี
หาค่าเฉลี่ยคะแนน 7 ข้อ (ตามแบบสอบถาม)
ผลรวมคะแนน

เครือข่ายชุมชนท้องถิ่นน่าอยู่ (เลือก)
รายวิชา						หัวข้อประเมิน 									คะแนนเฉลี่ย
						เนื้อหา	เอกสาร	วิทยากร	..	...
วิชาที่ ๑				?				?				?									SUM()/35
วิชาที่ ๒					?
วิชาที่ ๓					?
...							..
ค่าเฉลี่ย				SUM(COL)/(5*จำนวนวิชา)							SUM(ALL)/??';
$ret.='</pre>';
	*/




	$tables = new Table();
	$tables->caption='ผลการประเมิน';
	$tables->addClass('-summary');
	$tables->thead='<tr><th rowspan="2">รายวิชา</th><th colspan="7">หัวข้อประเมิน</th><th rowspan="2">คะแนนเฉลี่ย</th></tr><tr><th>เนื้อหา</th><th>เอกสาร/คู่มือ</th><th>วิทยากร</th><th>การบรรยาย</th><th>กิจกรรมกลุ่ม</th><th>ความรู้/ทักษะ/ความสามารถ</th><th>การนำความรู้ไปใช้ประโยชน์ในงานของเครือข่าย</th></tr>';
	foreach ($dbs->items as $rs) {
		$subjectAvg=($rs->rate1+$rs->rate2+$rs->rate3+$rs->rate4+$rs->rate5+$rs->rate6+$rs->rate7)/7;
		$subjectAvg1+=$rs->rate1;
		$subjectAvg2+=$rs->rate2;
		$subjectAvg3+=$rs->rate3;
		$subjectAvg4+=$rs->rate4;
		$subjectAvg5+=$rs->rate5;
		$subjectAvg6+=$rs->rate6;
		$subjectAvg7+=$rs->rate7;
		$subjectAvgTotal+=$subjectAvg;
		$tables->rows[]=array(
										$rs->title,
										number_format($rs->rate1,2),
										number_format($rs->rate2,2),
										number_format($rs->rate3,2),
										number_format($rs->rate4,2),
										number_format($rs->rate5,2),
										number_format($rs->rate6,2),
										number_format($rs->rate7,2),
										number_format($subjectAvg,2),
									);
	}
	$tables->tfoot[]=array(
									'',
									number_format($subjectAvg1/$totalSubject,2),
									number_format($subjectAvg2/$totalSubject,2),
									number_format($subjectAvg3/$totalSubject,2),
									number_format($subjectAvg4/$totalSubject,2),
									number_format($subjectAvg5/$totalSubject,2),
									number_format($subjectAvg6/$totalSubject,2),
									number_format($subjectAvg7/$totalSubject,2),
									number_format($subjectAvgTotal/$totalSubject,2),
								);
	$ret.=$tables->build();
	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($dbs,'$dbs');
	$ret.='</div>';

	head(
	'<style type="text/css">
	.item.-summary td:nth-child(n+2) {text-align:center;}
	.form-report .form-item {display:inline-block;}
	.form-report .form-item label {display:inline-block;}
	.form-report .form-select {width:100px;}
	</style>
	<script type="text/javascript">
	$(document).on("change","#form-report .form-select",function(){
		console.log("Change");
		$("#form-report").submit();
	})
	</script>'
	);
	return $ret;
}