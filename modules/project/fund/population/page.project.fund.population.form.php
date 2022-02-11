<?php
/**
* Project : Local Fund Population Form
* Created 2020-04-10
* Modify  2020-04-10
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage project/fund/$orgId/population.form
*/

$debug = true;

function project_fund_population_form($self, $fundInfo) {
	if (!($orgId = $fundInfo->orgid)) return message('error', 'PROCESS ERROR:NO FUND');

	$isAddPopulation = $fundInfo->right->addPopulation;

	if (!$isAddPopulation) return 'ERROR: ACCESS DENIED';

	$getYear = SG\getFirst(post('year'),date('Y')+1);

	// Get population of year
	$stmt = 'SELECT
		  `trid`
		, `orgid`
		, `refid`
		, `refcode`
		,`date1` `recordyear`
		, `num1` `balance`
		, `num2` `population`
		, `num3` `budgetnhso`
		, `num4` `budgetlocal`
		, `detail1` `haveplan`
		, `detail2` `byname`
		, `detail3` `byposition`
		FROM %project_tr%
		WHERE `orgid` = :orgid AND `formid` = "population" AND YEAR(`date1`) = :year
		LIMIT 1';

	$data = mydb::select($stmt, ':orgid', $fundInfo->orgid, ':year', $getYear);

	if (!$data->orgincomepcnt) $data->orgincomepcnt = $fundInfo->info->orgincomepcnt;
	if (!$data->orgemail) $data->orgemail = $fundInfo->info->orgemail;
	if (!$data->orgphone) $data->orgphone = $fundInfo->info->orgphone;

	return new Scaffold([
		'appBar' => new AppBar([
			'title' => 'ข้อมูลประชากร'.($data->trid ? ' [แก้ไขข้อมูลประจำปีงบประมาณ '.($getYear+543).']' : ''),
			'leading' => _HEADER_BACK,
			'boxHeader' => 'true',
		]),
		'body' => 	$form = new Form([
			'variable' => 'data',
			'action' => url('project/fund/'.$orgId.'/info/population.save'),
			'id' => 'project-population-edit',
			'class' => 'sg-form',
			'rel' => 'notify',
			'done' => 'back | load:#main',
			'checkValid' => true,
			'children' => [
				'<h3 class="-sg-text-center">แบบฟอร์มกรอกข้อมูลเพื่อจัดทำข้อมูลประชากรการจัดสรรงบประมาณกองทุนฯ<br />ประจำปีงบประมาณ '.($getYear+543).'</h3>',
				'year' => ['type'=>'hidden','value'=>$getYear],
				'<h4>ส่วนที่ 1 : ข้อมูลกองทุน</h4>',
				//$form->addField('fundname','<strong><big>1 ชื่อกองทุนหลักประกันสุขภาพระดับท้องถิ่น อบต./เทศบาล <em>'.$fundInfo->name.'</em> อำเภอ <em>'.$fundInfo->info->nameampur.'</em> จังหวัด <em>'.$fundInfo->info->namechangwat.'</em></big></strong>');
				'population' => [
					'type'=>'text',
					'label'=>'1. ข้อมูลประชากรตามทะเบียนราษฎร์ ณ วันที่ 1 เมษายน '.($getYear-1+543).' (ข้อมูล ณ วันที่ 31 มีนาคม) จำนวนทั้งสิ้น',
					'posttext'=>' คน',
					'require'=>true,
					'class' => '-numeric',
					'description'=>'(เอกสารแนบ....ที่มาของยอดประชากร)',
					'value' => $data->population ? number_format($data->population) : '',
				],
				'orgincomepcnt' => [
					'type'=>'radio',
					'label'=>'2. ระดับรายได้ขององค์กรปกครองส่วนท้องถิ่นไม่ร่วมเงินอุดหนุน (ปีที่ผ่านมา) ตามประกาศฯข้อ 8:',
					'require'=>true,
					'class' => 'org-budget-size',
					'options'=>array(
						'30' => 'น้อยกว่า 6 ล้านบาท(สมทบไม่น้อยกว่า ร้อยละ 30)',
						'40' => '6 -20 ล้านบาท (สมทบไม่น้อยกว่า ร้อยละ 40)',
						'50' => 'มากกว่า 20 ล้านบาท(สมทบไม่น้อยกว่า ร้อยละ 50)',
					),
					'value' => $data->orgincomepcnt,
				],
				'budgetnhso' => [
					'type'=>'text',
					'label'=>'3. ประมาณการการได้รับเงินจาก สปสช.ปี '.($getYear+543).' จำนวน',
					'posttext'=>' บาท',
					'require'=>true,
					'class' => '-money',
					'description'=>'(ข้อมูลประชากรตามข้อ 1. X 45 บาท)',
					'value' => $data->budgetnhso ? number_format($data->budgetnhso,2) : '',
				],
				'budgetlocal' => [
					'type'=>'text',
					'label'=>'4. จำนวนเงินสมทบที่ตั้งไว้ในข้อบัญญัติงบประมาณของ อบต./เทศบาล ประจำปี '.($getYear+543).' จำนวน',
					'posttext'=>' บาท',
					'require'=>true,
					'class' => '-money',
					'value' => $data->budgetlocal ? number_format($data->budgetlocal,2) : '',
					'description' => 'จำนวนเงินข้อ 3. x ระดับการสมทบข้อ 2',
				],
				'<h4>ส่วนที่ 2 : รายละเอียดผู้ป้อนข้อมูล</h4>',
				'byname' => [
					'type'=>'text',
					'label'=>'ผู้กรอกข้อมูล',
					'class'=>'-fill',
					'require'=>true,
					'value'=>$data->byname,
				],
				'byposition' => [
					'type'=>'text',
					'label'=>'ตำแหน่ง',
					'class'=>'-fill',
					'require'=>true,
					'value'=>$data->byposition,
				],
				'orgemail' => [
					'type'=>'text',
					'label'=>'อีเมล์ที่ติดต่อได้',
					'class'=>'-fill',
					'value'=>$data->orgemail,
					'description'=>'อีเมล์ที่ติดต่อได้จะเปิดเผยต่อสาธารณะ กรุณาใช้อีเมล์สำนักงาน',
				],
				'orgphone' => [
					'type'=>'text',
					'label'=>'เบอร์โทรศัพท์ที่ติดต่อได้',
					'class'=>'-fill',
					'value'=>$data->orgphone,
					'description'=>'เบอร์โทรศัพท์ที่ติดต่อได้จะเปิดเผยต่อสาธารณะ กรุณาใช้เบอร์โทรสำนักงาน',
				],
				'save' => [
					'type'=>'button',
					'name'=>'save',
					'value'=>'<i class="icon -material">done_all</i><span>บันทึกข้อมูลประชากร</span>',
					'pretext'=>'<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
					'container' => '{class: "-sg-text-right"}',
				],
				'<p>หมายเหตุ  : โปรดกรอกข้อมูลให้ครบทุกข้อ</p>',
				'<style type="text/css">
					.form h3 {padding:16px; margin:0 0 32px 0; background:#ddd; text-align:center; line-height:1.6em;}
					#project-population-edit h4 {padding:8px; margin:0 0 10px 0; background:#ccc;}
					#project-population-edit .form-item {margin: 0 0 40px 0;}
					#project-population-edit .form-select {width:210px;}
					#project-population-edit #form-item-edit-data-byname {margin-bottom:0;}
				</style>',
				'<script type="text/javascript">
					$("#edit-data-population").keyup(function() {projectUpdatePop()});
					$("input[name=\'data[orgincomepcnt]\'").change(function() {projectUpdatePop()});

					function projectUpdatePop() {
						var budgetPerPerson = 45
						var population = $("#edit-data-population").val().sgMoney()
						var radioValue = $("input[name=\'data[orgincomepcnt]\']:checked").val();
						if(population && radioValue){
							var budgetNHSO = population * budgetPerPerson
							var budgetLocal = budgetNHSO * radioValue / 100
							$("#edit-data-budgetnhso").val(budgetNHSO.toString().sgMoney(2))
							$("#edit-data-budgetlocal").val(budgetLocal.toString().sgMoney(2))
						}
					}
				</script>',
			]
		]),
	]);
}
?>