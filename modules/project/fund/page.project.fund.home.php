<?php
/**
* Project :: Local Fund Home Page
* Created 2019-04-30
* Modify  2020-04-11
*
* @param Object $self
* @return String
*
* @usage project/fund
*/

import('package:project/fund/widgets/widget.fund.trailing');

Class ProjectFundHome extends Page {
	var $year;
	var $lastUCyear;

	function __construct() {
		$this->lastUCyear = mydb::select('SELECT MAX(`ucyear`) `ucyear` FROM %project_ucmoney% LIMIT 1')->ucyear;
		if (empty($this->lastUCyear)) $this->lastUCyear=date('Y');
		$this->year = SG\getFirst(post('year'), $this->lastUCyear);

		$this->area = post('area');
		$this->changwat = post('prov');
		$this->fundId = post('fid');
		$this->exportExcel = post('export') == 'excel';
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กองทุนสุขภาพตำบล',
				'trailing' => new FundTrailingWidget($this->fundInfo),
			]),
			'body' => new Widget([
				'children' => [
					$this->myFundBanner(),
					new Form([
						'class' => 'form-report',
						'method' => 'get',
						'action' => url('project/fund'),
						'children' => [
							'year' => [
								'type' => 'select',
								'onChange' => 'this.form.submit()',
								'value' => $this->year,
								'options' => (function() {
									$options = [];
									for ($dcYear = $this->lastUCyear; $dcYear > 2016; $dcYear--) $options[$dcYear] = 'พ.ศ.'.($dcYear+543);
									return $options;
								})(),
							],
							'area' => [
								'type' => 'select',
								'value' => $this->area,
								'onChange' => '$("#edit-prov").val("");this.form.submit()',
								'options' => ['' => '==ทุกเขต=='] + mydb::select('SELECT `areaid`,`areaname` FROM %project_area% ORDER BY areaid+0 ASC; -- {key: "areaid", value: "areaname"}')->items,
							],
							'prov' => [
								'type' => 'select',
								'value' => $this->changwat,
								'onChange' => 'this.form.submit()',
								'options' => ['' => '==ทุกจังหวัด=='] + (function() {
									if ($this->area) mydb::where('`areaid` = :areaid',':areaid',$this->area);
									$options = mydb::select(
										'SELECT DISTINCT `changwat`,`namechangwat`
										FROM %project_fund%
										%WHERE%
										ORDER BY CONVERT(`namechangwat` USING tis620) ASC;
										-- {key: "changwat", value: "namechangwat"}'
									)->items;
									return $options;
								})(),
							],
							$this->changwat ? '<a class="btn" href="'.url('project/fund', ['year' => $this->year, 'prov' => $this->changwat, 'export' => 'excel']).'"><i class="icon -download"></i><span>ดาวน์โหลด</span></a>' : '',
						], //children
					]), // Form

					$this->area || $this->changwat ? $this->estimateMoney() : $this->chart(),

					$ret,

					$this->script(),
				], // children
			]), // Widget
		]);
	}

	function myFundBanner() {
		// Check for owner fund
		if (i()->ok && $myFund = R::Model('project.fund.get.my',i()->uid)) {
			$widget = new Row([
				'mainAxisAlignment' => 'spacebetween',
				'class' => 'project-fund-owner',
				'children' => [
					'<a href="'.url('project/fund/'.$myFund->orgid).'"><img class="profile-photo" src="'.model::user_photo(i()->username,false).'" width="64" height="64" alt="'.htmlspecialchars(i()->name).'" title="'.htmlspecialchars(i()->name).'" /></a>',
					'<h3>'.$myFund->name.' ('.$myFund->fundid.')'.'</h3>'
					. '<p>อำเภอ'.$myFund->nameampur.' จังหวัด'.$myFund->namechangwat.' เขต '.$myFund->areaid.' '.$myFund->namearea.'</p>',
					'<a class="btn -primary" href="'.url('project/fund/'.$myFund->orgid).'" style="padding: 16px; border-radius: 4px;">จัดการกองทุน</a>',
				], // children
			]);
		}  else if (i()->ok && $myFund = R::Model('project.fund.get.my',i()->uid, 'TRAINER')) {
			$widget = new Row([
				'mainAxisAlignment' => 'spacebetween',
				'class' => 'project-fund-owner',
				'children' => [
					'<a href="'.url('project/fund/'.$myFund->orgid.'/trainer/'.i()->uid).'"><img class="profile-photo" src="'.model::user_photo(i()->username,false).'" width="64" height="64" alt="'.htmlspecialchars(i()->name).'" title="'.htmlspecialchars(i()->name).'" /></a>',
					'<h3>'.i()->name.'</h3>'
					. '<p>'.$myFund->name.' ('.$myFund->fundid.') อำเภอ'.$myFund->nameampur.' จังหวัด'.$myFund->namechangwat.' เขต '.$myFund->areaid.' '.$myFund->namearea.'</p>',
					'<a class="btn -primary" href="'.url('project/trainer/'.i()->uid).'" style="padding: 16px; border-radius: 4px;">บันทึกพี่เลี้ยง</a>',
				], // children
			]);
		}

		return $widget;
	}

	function estimateMoney() {
		$ucmoney = mydb::select('SELECT * FROM %project_ucmoney% WHERE `ucyear`=:year AND `provid`=:provid LIMIT 1',':provid',$this->changwat,':year',$this->year);

		mydb::where(NULL,':year',$this->year);
		if ($this->area) mydb::where('`areaid`=:areaid',':areaid',$this->area);
		if ($this->changwat) mydb::where('`changwat`=:prov',':prov',$this->changwat);
		if ($this->fundId) mydb::where('`shortname`=:fundid',':fundid',$this->fundId);

		$stmt = 'SELECT
			  b.*
				, lgl.`glcode` `localRcvGlCode`
				, ABS(lgl.`amount`) `localRcv`
				, ABS(SUM(lgl.`amount`)) `localRcv`
				, (SELECT COUNT(*) FROM %topic% t RIGHT JOIN %project% tp USING(`tpid`) WHERE t.`orgid`=b.`orgid` AND tp.`pryear`=:year AND tp.`prtype`="โครงการ") `totalProject`
			FROM
			(
				SELECT
				  a.*
				, gl.`glcode` `nhsoGlCode`
				, ABS(SUM(gl.`amount`)) `nhsoRcv`
				FROM
					(
						SELECT f.*
							, o.`name`
							, o.`shortname`
							, po.`num2` `yearPopulation`
							, po.`num4` `budgetlocal`
						FROM %project_fund% f
							LEFT JOIN %db_org% o ON o.`orgid`=f.`orgid`
							LEFT JOIN %project_tr% po ON po.`formid`="population" AND po.`part`=f.`fundid` AND po.`refid` = :year
						GROUP BY `orgid`
					) a
					LEFT JOIN %project_gl% gl
						ON gl.`orgid`=a.`orgid`
						AND gl.`glcode`="40100"
						AND YEAR(gl.`refdate`)+IF(MONTH(gl.`refdate`)>=10,1,0)=:year
					GROUP BY `fundid`
				) b
				LEFT JOIN %project_gl% lgl
					ON lgl.`orgid`=b.`orgid`
					AND lgl.`glcode`="40200"
					AND YEAR(lgl.`refdate`)+IF(MONTH(lgl.`refdate`)>=10,1,0)=:year
			%WHERE%
			GROUP BY `fundid`
			ORDER BY `changwat` ASC, `ampur` ASC, CONVERT(`fundname` USING tis620) ASC;
			-- {sum:"population,openbalance,totalProject,nhsoRcv,localRcv"}';

		$dbs = mydb::select($stmt);

		$totalPopulation = $dbs->sum->population;
		$budgetFromNHSO = $ucmoney->moneyallocate;
		$totalUCPopulation = $ucmoney->ucpop;
		$factor = $totalPopulation ? $budgetFromNHSO/$totalPopulation : 0;

		if ($this->year >= 2021) {
			// Year 2564 Org Size %
			$localAddPercentList = array(
				60=>60, //Not used
				50=>50, //มากกว่า 20 ล้านบาท(สมทบไม่น้อยกว่า ร้อยละ 50)
				40=>40, //6 -20 ล้านบาท (สมทบไม่น้อยกว่า ร้อยละ 40)
				30=>30, //น้อยกว่า 6 ล้านบาท(สมทบไม่น้อยกว่า ร้อยละ 30)
				20=>20, //Not used
				10=>10, //Not used
			);
		} else {
			$localAddPercentList=array(
				6=>60, //6:เทศบาลนคร=60%
				5=>60, //5:เทศบาลเมือง=60%
				4=>50, //4:เทศบาลตำบล=50%
				3=>40, //3:อบต.ขนาดใหญ่=50%
				2=>40, //2:อบต.ขนาดกลาง=40%
				1=>30, //1:อบต.ขนาดเล็ก=30%
			);
		}


		$totalFundAllocate=$totalFundLocal=$totalFundLocalAdd=0;


		// รายงานจำนวนประชากร
		$tables = new Table();
		if ($this->exportExcel) {
			$tables->thead = [
				'ชื่อกองทุน',
				'รหัสกองทุน',
				'อำเภอ',
				'จังหวัด',
				'money openbalance'=>'ยอดยกมา',
				'pop-year -amt' => 'จำนวนประชากร ปี '.($this->year + 543).'(คน)',
				'amt population'=>'จำนวนประชากร(คน)',
				'money -allocate'=>'ประมาณการณ์จำนวนเงินจัดสรรโดย สปสช.(บาท)',
				'money -local'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น(คำนวณ)(บาท)',
				'%',
				'money -locaninput'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น(บาท)',
				'%',
				'money -nshoinput'=>'จำนวนเงินรับจากสปสช.(บาท)',
				'money -localinput'=>'จำนวนเงินรับจากท้องถิ่น(บาท)',
				'amt'=>'โครงการ',
			];
		} else {
			$tables->thead = [
				'name -nowrap' => 'ชื่อกองทุน',
				'pop-year -amt' => 'จำนวนประชากร ปี '.($this->year + 543).'<br />(คน)',
				'amt population'=>'จำนวนประชากร<br />(คน)',
				'money -allocate'=>'ประมาณการณ์จำนวนเงินจัดสรรโดย สปสช.<br />(บาท)',
				'money -local'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น(คำนวณ)<br />(บาท)',
				'money -locaninput'=>'ประมาณการณ์เงินสมทบจากท้องถิ่น<br />(บาท)',
				'money -nshoinput'=>'จำนวนเงินรับจากสปสช.<br />(บาท)',
				'money -localinput'=>'จำนวนเงินรับจากท้องถิ่น<br />(บาท)',
				'amt'=>'โครงการ',
			];
		}
		// debugMsg('$factor = '.$factor);
		// debugMsg($dbs, '$dbs');
		foreach ($dbs->items as $rs) {
			$localPercentIndex = $this->year >= 2021 ? $rs->orgincomepcnt : $rs->orgsize;
			$localAddPercentAmt = $localAddPercentList[$localPercentIndex];
			$moneyAllocate = round($rs->population*$factor);
			$moneyLocal = round($moneyAllocate*$localAddPercentAmt/100);
			$localBudgetPercent = $moneyAllocate ? $rs->budgetlocal*100/$moneyAllocate : 0;

			if ($rs->budgetlocal > $moneyLocal) {
				$fontColor = 'green';
			} else if ($localAddPercentAmt-$localBudgetPercent <= 3) {
				$fontColor = 'yellow';
			} else {
				$fontColor = 'red';
			}

			if ($this->exportExcel) {
				$tables->rows[] = [
					$rs->name,
					$rs->shortname,
					$rs->nameampur,
					$rs->namechangwat,
					number_format($rs->openbalance,2),
					$rs->yearPopulation ? number_format($rs->yearPopulation) : '-',
					number_format($rs->population),
					number_format($moneyAllocate,2),
					number_format($moneyLocal,2),
					$localAddPercentAmt.'%',
					number_format($rs->budgetlocal,2),
					number_format($rs->budgetlocal*100/$moneyAllocate),
					number_format($rs->nhsoRcv,2),
					number_format($rs->localRcv,2),
					number_format($rs->totalProject),
				];
			} else {
				$tables->rows[] = [
					'<a href="'.url('project/fund/'.$rs->orgid).'"><b>'.$rs->name.'</b></a><br /><i>'.$rs->shortname
					. ' อำเภอ'.$rs->nameampur.'</i>',
					$rs->yearPopulation ? number_format($rs->yearPopulation) : '-',
					number_format($rs->population),
					number_format($moneyAllocate,2),
					number_format($moneyLocal,2)
					. '<br /><span style="color:#ccc;">('.$localAddPercentAmt.'%)</span>',
					'-'.$fontColor=>number_format($rs->budgetlocal,2)
					.'<br /><span style="color:#ccc;">('.number_format($localBudgetPercent).'%)</span>',
					$rs->nhsoRcv ? number_format($rs->nhsoRcv,2) : '-',
					$rs->localRcv ? number_format($rs->localRcv,2) : '-',
					$rs->totalProject ? '<a href="'.url('project/fund/'.$rs->orgid.'/follow').'">'.number_format($rs->totalProject).'</a>' : '-',
				];
			}
			$totalFundAllocate+=$moneyAllocate;
			$totalFundLocal+=$moneyLocal;
			$totalFundLocalAdd+=$rs->budgetlocal;
		}
		$tables->tfoot[] = [
			'รวม '.$dbs->_num_rows.' กองทุน',
			//number_format($dbs->sum->openbalance,2),
			number_format($dbs->sum->population),
			number_format($totalFundAllocate,2),
			number_format($totalFundLocal,2),
			number_format($totalFundLocalAdd,2),
			number_format($dbs->sum->nhsoRcv,2),
			number_format($dbs->sum->localRcv,2),
			number_format($dbs->sum->totalProject),
		];

		if ($this->exportExcel) die(R::Model('excel.export',$tables,'ประชากร ปี '.($this->year + 543).' จังหวัด '.$this->changwat.' @'.date('Y-m-d H:i:s').'.xls','{debug:false}'));

		return new Widget([
			'children' => [
				new ScrollView([
					'child' => $tables,
				]), // ScrollView
				'<p class="remark">*** หมายเหตุ : ตัวเลข ประมาณการณ์จำนวนเงินจัดสรรโดย สปสช.	และ ประมาณการณ์เงินสมทบจากท้องถิ่น จะยังไม่ถูกต้องจนกว่าจะมีการบันทึกข้อมูลประชากรครบทุกกองทุน</p>'
			], // children
		]);
	}

	function chart() {
		$colors = ['#3366CC','#DC3912','#FF9900','#109618','#990099','#0099C6','#DD4477'];

		// Graph
		$graph = [
			'prov-project' => [],
			'prov-budget' => [],
			'prov-pop' => [],
			'prov-fund' => [],
			'type-project' => [],
			'type-budget' => [],
		];

		$stmt="SELECT
			  `changwat`
			, `namechangwat`
			, COUNT(IF(`population`>0,1,NULL)) `inputFund`
			, COUNT(*) `totalFund`
			, SUM(`population`) `totalPopulation`
			, u.`ucpop`
			, u.`moneyallocate`
			FROM %project_fund% f
				LEFT JOIN %project_ucmoney% u ON u.`provid`=f.`changwat` AND u.`ucyear`=$this->year
			GROUP BY f.`changwat`;
			-- {key:'changwat',sum:'totalFund,inputFund,totalPopulation,ucpop,moneyallocate'}
		";
		$dbs=mydb::select($stmt);

		$stmt='SELECT
				t.`title`
			, o.`shortname`
			, f.`changwat`
			, COUNT(*) `totalProject`
			, SUM(p.`budget`) `totalBudget`
			FROM %topic% t
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project% p USING(`tpid`)
				LEFT JOIN %project_fund% f ON f.`fundid`=o.`shortname`
			WHERE t.`type`="project" AND p.`prtype`="โครงการ" AND `pryear`=:year AND f.`fundid` IS NOT NULL
			GROUP BY `changwat`;
			-- {key:"changwat",sum:"totalProject,totalBudget"}';
		$projects=mydb::select($stmt,':year',$this->year);

		$totalProject=0;
		$tables = new Table();
		$tables->caption='ประจำปี '.($this->year+543);
		$tables->thead=array('','จังหวัด','amt fund'=>'จำนวนกองทุน','amt join'=>'กองทุนเข้าร่วมระบบออนไลน์','amt pop'=>'จำนวนประชากรกลางปีทุกสิทธิที่ท้องถิ่นให้ข้อมูล','amt -ucpop'=>'จำนวนประชากรกลางปีทุกสิทธิจาก สปสช.(Total Thai Population)','money -allocate'=>'จำนวนเงินจัดสรรโดย สปสช.','amt -project'=>'จำนวนโครงการ','money -budget'=>'งบประมาณโครงการ');
		$no=0;
		foreach ($dbs->items as $rs) {
			$changwatProject=$projects->items[$rs->changwat]->totalProject;
			$tables->rows[]=array(
				'<span class="color" style="background-color:'.$colors[$no++].'"></span>',
				'<a href="'.url('project/fund',array('prov'=>$rs->changwat)).'">'.$rs->namechangwat.'</a>',
				$rs->totalFund,
				$rs->inputFund.' <span class="percent">('.number_format(100*$rs->inputFund/$rs->totalFund).'%)</span>',
				number_format($rs->totalPopulation).' <span class="percent">('.number_format(100*$rs->totalPopulation/$dbs->sum->totalPopulation).'%)</span>',
				number_format($rs->ucpop),
				number_format($rs->moneyallocate,2),
				$changwatProject?$changwatProject.' <span class="percent">('.number_format(100*$changwatProject/$projects->sum->totalProject).'%)</span>':'',
				number_format($projects->items[$rs->changwat]->totalBudget,2),
			);

			$totalProject+=$changwatProject;
			$graph['prov-project'][]=array($rs->namechangwat,round($changwatProject));
			$graph['prov-pop'][]=array($rs->namechangwat,round($rs->totalPopulation));
			$graph['prov-fund'][]=array($rs->namechangwat,round($rs->totalFund));
			$graph['prov-budget'][]=array($rs->namechangwat,round($projects->items[$rs->changwat]->totalBudget));
		}
		$tables->tfoot[]=array(
			'',
			'รวม',
			number_format($dbs->sum->totalFund),
			number_format($dbs->sum->inputFund).' <span class="percent">('.number_format(100*$dbs->sum->inputFund/$dbs->sum->totalFund).'%)</span>',
			number_format($dbs->sum->totalPopulation),
			number_format($dbs->sum->ucpop),
			number_format($dbs->sum->moneyallocate,2),
			number_format($totalProject),
			number_format($projects->sum->totalBudget,2),
		);

		$ret .= '<div class="">'
			. '<div id="prov-project" class="graph">กราฟแสดงจำนวนโครงการแต่ละจังหวัด ประจำปี '.($this->year+543).'</div>'
			. '<div id="prov-budget" class="graph">กราฟแสดงงบประมาณแต่ละจังหวัด ประจำปี '.($this->year+543).'</div>'
			. '<div id="prov-pop" class="graph">กราฟแสดงจำนวนประชากรแต่ละจังหวัด ประจำปี '.($this->year+543).'</div>'
			. '<div id="prov-fund" class="graph">กราฟแสดงจำนวนกองทุนแต่ละจังหวัด ประจำปี '.($this->year+543).'</div>';


		if (user_access('access administrator pages')) $ret.='<p align="right"><a class="btn -primary" href="'.url('project/admin/fund/money').'"><i class="icon -addbig -white"></i> บันทึกเงินจัดสรรโดย สปสช.</a></p>';

		$ret .= $tables->build();
		$ret .= '</div>';


		$stmt='SELECT p.`supporttype`, IFNULL(st.`name`,"ไม่ระบุ") `supporttypeName`, COUNT(*) `totalProject`, SUM(`budget`) `totalBudget`
			FROM %project% p
				LEFT JOIN %tag% st ON st.`taggroup`="project:supporttype" AND st.`catid`=p.`supporttype`
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_fund% f ON f.`fundid`=o.`shortname`
			WHERE p.`prtype`="โครงการ" AND p.`pryear`=:year AND f.`fundid` IS NOT NULL
			GROUP BY `supporttypeName`;
			-- {sum:"totalProject,totalBudget"}';
		$dbs=mydb::select($stmt,':year',$this->year);
		$tables = new Table();
		$tables->caption='ประเภทการสนับสนุนของโครงการประจำปี '.($this->year+543);
		$tables->thead=array('','ประเภท','amt -project'=>'โครงการ','money -budget'=>'งบประมาณ');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				'<span class="color" style="background-color:'.$colors[$no++].'"></span>',
				$rs->supporttypeName,
				number_format($rs->totalProject)
				.' <span class="percent">('.number_format(100*$rs->totalProject/$dbs->sum->totalProject).'%)</span>',
				number_format($rs->totalBudget,2)
				.' <span class="percent">('.number_format(100*$rs->totalBudget/$dbs->sum->totalBudget).'%)</span>',
			);
			$supporttypeName=$rs->supporttype?'ประเภท '.$rs->supporttype:'ไม่ระบุ';
			$graph['type-project'][]=array($supporttypeName,round($rs->totalProject));
			$graph['type-budget'][]=array($supporttypeName,round($rs->totalBudget));
		}
		$tables->tfoot[]=array('','รวม',number_format($dbs->sum->totalProject),number_format($dbs->sum->totalBudget,2));

		$ret.='<div class=""><div id="type-project" class="graph">กราฟแสดงจำนวนโครงการแต่ละประเภท ประจำปี '.($this->year+543).'</div><div id="type-budget" class="graph">กราฟแสดงงบประมาณแต่ละประเภท ประจำปี '.($this->year+543).'</div>';
		$ret.=$tables->build();
		$ret.='</div>';


		// สิทธิประโยชน์
		$stmt='SELECT p.`supporttype`, IFNULL(pt.`title`,"ไม่ระบุ") `parentName`, COUNT(*) `totalProject`, SUM(p.`budget`) `totalBudget`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)
				LEFT JOIN %project_fund% f ON f.`fundid`=o.`shortname`
				LEFT JOIN %topic_parent% m ON m.`tpid`=t.`tpid`
				LEFT JOIN %topic% pt ON pt.`tpid`=m.`parent`
			WHERE p.`prtype`="โครงการ" AND p.`pryear`=:year AND f.`fundid` IS NOT NULL
			GROUP BY `parentName`
			ORDER BY `totalBudget` DESC;
			-- {sum:"totalProject,totalBudget"}';
		$dbs=mydb::select($stmt,':year',$this->year);

		$tables = new Table();
		$tables->caption='กิจกรรมหลักของโครงการ ประจำปี '.($this->year+543);
		$tables->thead=array('','กิจกรรมหลัก','amt -project'=>'โครงการ','money -budget'=>'งบประมาณ');
		$no=0;
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				'<span class="color" style="background-color:'.$colors[$no++].'"></span>',
				$rs->parentName,
				number_format($rs->totalProject)
				.' <span class="percent">('.number_format(100*$rs->totalProject/$dbs->sum->totalProject).'%)</span>',
				number_format($rs->totalBudget,2)
				.' <span class="percent">('.number_format(100*$rs->totalBudget/$dbs->sum->totalBudget).'%)</span>',
			);
			$graph['mainact-project'][]=array($rs->parentName,round($rs->totalProject));
			$graph['mainact-budget'][]=array($rs->parentName,round($rs->totalBudget));
		}
		$tables->tfoot[]=array('','รวม',number_format($dbs->sum->totalProject),number_format($dbs->sum->totalBudget,2));

		$ret.='<div class=""><div id="mainact-project" class="graph">กราฟแสดงจำนวนโครงการแต่ละกิจกรรมหลัก ประจำปี '.($this->year+543).'</div><div id="mainact-budget" class="graph">กราฟแสดงงบประมาณแต่ละกิจกรรมหลัก ประจำปี '.($this->year+543).'</div>';
		$ret.=$tables->build();
		$ret.='<p>*** จำนวนโครงการ/งบประมาณรวมอาจจะไม่เท่ากับจำนวนโครงการทั้งหมดในระบบ เนื่องจากแต่ละโครงการสามารถอยู่ภายกิจกรรมหลักได้หลายกิจกรรม</p>';
		$ret.='</div>';
		return new ScrollView([
			'children' => [
				$ret,
				$this->_drawChart($graph),
			], // children
		]);
	}

	function _drawChart($graph) {
		head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
		return '
			<script type="text/javascript">
			var chartData='.json_encode($graph).'
			var chartType="pie";
			google.charts.load("current", {"packages":["corechart"]});
			google.charts.setOnLoadCallback(drawChart);

			function drawChart() {
				var options = {
					vAxis: {
						viewWindowMode: "explicit",
					},
				};

				if (chartType=="pie") {
					var options = {
						legend: {position: "labeled"},
						chartArea: {width:"100%",height:"80%"},
						pieSliceText: "label",
						pieHole:0.4,
					};
				}
				$.each(chartData,function(i,eachChartData) {
					options.title=$("#"+i).text();
					var data = new google.visualization.DataTable();
					data.addColumn("string", "จังหวัด");
					data.addColumn("number", "จำนวน");
					data.addRows(eachChartData);

					var chart = new google.visualization.PieChart(document.getElementById(i));
					if (chartType=="line") {
						chart = new google.visualization.LineChart(document.getElementById(i));
					} else if (chartType=="bar") {
						chart = new google.visualization.BarChart(document.getElementById(i));
					} else if (chartType=="col") {
						chart = new google.visualization.ColumnChart(document.getElementById(i));
					} else if (chartType=="pie") {
						chart = new google.visualization.PieChart(document.getElementById(i));
					}
					chart.draw(data, options);
				});
			}
			$(document).on("click", ".toolbar.-graphtype a", function() {
				var $this=$(this);
				chartType=$this.attr("href").substring(1);
				//notify("chartType="+chartType);
				$(".toolbar.-graphtype a").removeClass("active");
				$this.addClass("active");
				drawChart();
				return false;
			});
			</script>
			';
	}

	function script() {
		return '
		<style type="text/css">
		.percent {font-size:0.9em;color:#999;}
		.graph {width:49%; height:400px; margin:10px 0.5%; background-color:#eee; float:left;}
		.color {display:inline-block;width:16px;height:16px;}
		.col-money.-yellow {color:#ffbd00;}
		.col-money.-red {color:red;}
		.col-money.-green {color:green;}
		</style>';
	}
}
?>