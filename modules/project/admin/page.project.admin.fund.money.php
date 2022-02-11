<?php
function project_admin_fund_money($self) {
	R::View('project.toolbar',$self,'Local Fund Management','admin');
	$self->theme->sidebar=R::View('project.admin.menu');

	$provList=R::Model('fund.prov.get',NULL,'{getAllRecord:true}');

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead=array('ปี','จังหวัด','ประชากร UC','งบจัดสรร');

	if (post('ucpop') && post('moneyallocate')) {
		$data = (Object) post();
		$data->ucpop = sg_strip_money($data->ucpop);
		$data->moneyallocate = sg_strip_money($data->moneyallocate);
		
		$stmt = 'INSERT INTO %project_ucmoney%
			(`ucyear`,`provid`,`ucpop`,`moneyallocate`)
			VALUES
			(:ucyear, :provid, :ucpop, :moneyallocate)
			ON DUPLICATE KEY UPDATE
			`ucpop` = :ucpop
			, `moneyallocate` = :moneyallocate';

		mydb::query($stmt, $data);
		//$ret.=mydb()->_query;
	}

	$ret.='<form id="project-add" class="sg-form" method="post" action="'.url('project/admin/fund/money',array('act'=>'add')).' "data-checkvalid="true">'._NL;


	foreach ($provList as $item) $provOption.='<option value="'.$item->provid.'">'.$item->name.'</option>';
	$tables->rows['input']=array(
		'<label class="-hidden" for="project-edit-ucyear">ปีงบประมาณ</label><select class="form-select" name="ucyear"><option value="'.(date('Y')+1).'">ปีงบประมาณ '.(date('Y')+543+1).'</option><option value="'.(date('Y')+2).'">ปีงบประมาณ '.(date('Y')+543+2).'</option></select>',
		'<label class="-hidden" for="project-edit-provid">จังหวัด</label><select class="form-select" name="provid">'.$provOption.'</select>',
		'<label class="-hidden" for="project-edit-ucpop">ประชากร UC</label><input id="project-edit-ucpop" class="form-text require -money -fill" type="text" name="ucpop" placeholder="0.00" />',
		'<label class="-hidden" for="project-edit-moneyallocate">เงินจัดสรรโดย สปสช.</label><input id="project-edit-moneyallocate" class="form-text require -money -fill" type="text" name="moneyallocate" placeholder="0.00" />'
		.'<p align="right"><button class="btn -primary" type="submit" value="บันทึก"><i class="icon -save -white"></i> บันทึก</button></p>',
		'config'=>array('class'=>'-datainput')
	);


	$tables->rows[]='<header>';

	$stmt='SELECT * FROM %project_ucmoney% u LEFT JOIN %co_province% p USING(`provid`) ORDER BY `ucyear` DESC, CONVERT(`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array($rs->ucyear+543,$rs->provname,number_format($rs->ucpop),number_format($rs->moneyallocate,2));
	}
	$ret.=$tables->build();
	$ret.='</form>';
	//$ret.=print_o(post(),'post()');
	return $ret;
}
?>