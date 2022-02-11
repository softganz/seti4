<?php
/**
 * Add new alumni
 *
 * @param Array $_POST['set']
 * @return String and die / Location
 */
function set_add($self,$symbol) {
	$getGid = post('gid');

	$ret.='<h3>Add new transaction</h3>';
	if (!user_access('create set content')) return R::View('signform');

	if ($_POST['set']) {
		$post=(object)post('set',_TRIM);
		if ($post->symbol=='') $error='Please fill "symbol"';
		if ($error) {
			$ret.=notify($error);
		} else {
			if (i()->uid) $post->uid=i()->uid;
			$post->created=date('U');
			$post->balance=$post->bsd=='B'?$post->netamount:0;
			$stmt='INSERT INTO %setport%
							(`gid`, `uid`, `date`, `symbol`, `bsd`, `volumes`, `price`, `commission`, `tradingfee`, `chargingfee`, `vat`, `netamount`, `balance`, `created`)
							VALUES (:gid, :uid, :date, :symbol, :bsd, :volumes, :price, :commission, :tradingfee, :chargingfee, :vat, :netamount, :balance, :created)';
			mydb::query($stmt,$post);
			$sid=mydb()->insert_id;
			$ret.=$sid;
			$ret.=R::Model('set.balance.repair',$symbol);
			location('set');
		}
	}

	$form=new Form([
		'variable' => 'set',
		'action' => url(q()),
		'id' => 'edit-set',
		'class' => 'edit-set',
		'children' => [
			'date' => ['type' => 'hidden', 'value' => SG\getFirst($post->date,date('Y-m-d'))],
			'datein' => [
				'type' => 'text',
				'label' => 'วันที่',
				'size' => 10,
				'maxlength' => 10,
				'value' => sg_date(SG\getFirst($post->datein,date('Y-m-d')),'d/m/Y'),
			],
			'symbol' => [
				'type' => 'text',
				'label' => 'หลักทรัพย์',
				'size' => 10,
				'maxlength' => 10,
				'value' => SG\getFirst($post->symbol,$symbol),
			],
			'bsd' => [
				'type' => 'select',
				'label' => 'B/S/D',
				'options' => ['B'=>'Buy','S'=>'Sell','D'=>'Deposit'],
				'value' => $post->bsd,
			],
			'gid' => [
				'type' => 'select',
				'label' => 'Port',
				'options' => (function() {
					$options = [];
					foreach (mydb::select('SELECT * FROM %setgroup% WHERE `uid`=:uid AND `gtype`="Port" ORDER BY `name` ASC',':uid', i()->uid)->items as $port) {
						$options[$port->gid]=$port->name;
					}
					return $options;
				})(),
				'value' => $post->gid,
			],
			'volumes' => [
				'type' => 'text',
				'label' => 'จำนวนหุ้น',
				'size' => 10,
				'maxlength' => 10,
				'value' => $post->volumes,
			],
			'price' => [
				'type' => 'text',
				'label' => 'ราคา (บาท)',
				'size' => 10,
				'maxlength' => 10,
				'value' => $post->price,
			],
			'commission' => [
				'type' => 'text',
				'label' => 'Commission (บาท)',
				'size' => 10,
				'maxlength' => 8,
				'value' => $post->commission,
			],
			'tradingfee' => [
				'type' => 'text',
				'label' => 'Trading Fee (บาท)',
				'size' => 10,
				'maxlength' => 8,
				'value' => $post->tradingfee,
			],
			'chargingfee' => [
				'type' => 'text',
				'label' => 'Charging Fee (บาท)',
				'size' => 10,
				'maxlength' => 8,
				'value' => $post->chargingfee,
			],
			'vat' => [
				'type' => 'text',
				'label' => 'VAT (บาท)',
				'size' => 10,
				'maxlength' => 8,
				'value' => $post->vat,
			],
			'netamount' => [
				'type' => 'text',
				'label' => 'Net Amount (บาท)',
				'size' => 10,
				'maxlength' => 8,
				'value' => $post->netamount,
			],
			'submit' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>'.tr('Save').'</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('set').'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			],
		], // children
	]);

	$ret .= $form->build();



	if ($getGid) {
		mydb::where('p.`gid` IN ( :gid )',':gid', 'SET:'.$getGid);
	} else {
		mydb::where('p.`uid` = :uid',':uid',i()->uid);
	}
	if ($symbol && $symbol!='SET') mydb::where('`symbol`=:symbol', ':symbol',$symbol);
	$stmt='SELECT p.*, g.`name` `portname`
				FROM %setport% p
					LEFT JOIN %setgroup% g USING(gid)
					%WHERE%
					ORDER BY `date` DESC;
					-- {sum:"commission,tradingfee,chargingfee,vat,netamount"}';
	$dbs=mydb::select($stmt);
	//$ret .= print_o($dbs,'$dbs');

	$yearSum = 0;
	$currentYear = sg_date($dbs->items[0]->date,'Y');

	$tables = new Table();
	$tables->addClass('set-trade-list -center');
	$tables->thead=array('วันที่','พอร์ต','หลักทรัพย์','B/S/D','จำนวนหุ้น','Price','Commission','Trading Fee','Charging Fee','VAT','Net Amount');

	foreach ($dbs->items as $rs) {
		if ($currentYear != sg_date($rs->date, 'Y')) {
			$tables->rows[] = array('<td colspan="10" style="text-align: right;"><b>TOTAL OF YEAR '.($currentYear + 543).'</b></td>', '<b>'.number_format($yearSum,2).'</b>','config'=>'{class: "sub-header"}');
			$currentYear = sg_date($rs->date, 'Y');
			$yearSum = 0;
		}

		$yearSum += $rs->netamount;

		$price=$rs->volumes*$rs->price;
		$priceTotal+=$price;
		$vat=$price*$rs->vat/100;
		$commission=$price*$rs->commission/100;
		$tables->rows[]=array(sg_date($rs->date,'ว ดด ปปปป'),
														$rs->portname,
														$rs->symbol,
														$rs->bsd,
														number_format($rs->volumes),
														number_format($rs->price,3),
														number_format($rs->commission,2),
														number_format($rs->tradingfee,2),
														number_format($rs->chargingfee,2),
														number_format($rs->vat,2),
														number_format($rs->netamount,2),
														);
	}

	if ($yearSum) {
		$tables->rows[] = array('<td colspan="10" style="text-align: right;"><b>TOTAL OF YEAR '.($currentYear + 543).'</b></td>', '<b>'.number_format($yearSum,2).'</b>','config'=>'{class: "sub-header"}');
	}

	$tables->tfoot[]=array(
										'',
										'',
										'',
										'',
										'',
										number_format($priceTotal,2),
										number_format($dbs->sum->commission,2),
										number_format($dbs->sum->tradingfee,2),
										number_format($dbs->sum->chargingfee,2),
										number_format($dbs->sum->vat,2),
										number_format($dbs->sum->netamount,2),
										);
	$ret.=$tables->build();

	return $ret;
}
?>