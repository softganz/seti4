<?php
/**
* Garage :: Repair Recieve VAT
* Created 2020-04-08
* Modify  2020-04-08
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_admin_repair_recieve_vat($self) {
	$isConfirm = SG\confirm();

	new Toolbar($self, 'Repair Recieve VAT');

	$ret = '';

	if (!$isConfirm) {
		$ret .= '<nav class="nav -page -sg-text-center"><a class="sg-action btn -primary" href="'.url('garage/admin/repair/recieve/vat').'" data-rel="#main" data-title="Repair Recieve VAT" data-confirm="Confirm?"><i class="icon -material">done</i><span>Repair Recieve VAT</span></a></nav>';

		$stmt = 'SELECT * FROM %garage_rcv% ORDER BY `rcvid` DESC';

		$dbs = mydb::select($stmt);


		$tables = new Table();
		$tables->thead = array('Rcv No.', 'Insu Name', 'subtotal -money' => 'Sub Total', 'vattotal -money' => 'VAT Total', 'grandtotal -money' => 'Grand Total');

		foreach ($dbs->items as $rs) {
			$tables->rows[] = array(
				'<a class="sg-action" href="'.url('garage/recieve/'.$rs->rcvid).'" data-rel="box">'.$rs->rcvno.'</a>',
				$rs->rcvcustname,
				$rs->subtotal,
				$rs->vattotal,
				$rs->total,
			);
		}
		$ret .= $tables->build();

		return $ret;
	}

	$ret .= '<h3>Repair Recieve VAT</h3>';

	$stmt = 'SELECT `rcvid`, `rcvno`, `total` FROM %garage_rcv%
		WHERE `subtotal` = 0 AND `vattotal` = 0 AND `total` = 0
		ORDER BY `rcvid` DESC';

	$dbs = mydb::select($stmt);

	foreach ($dbs->items as $rs) {
		$ret .= '<p>Repair :: '.$rs->rcvno.' Total '.$rs->total.'</p>';
		R::Model('garage.recieve.vat.update', $rs->rcvid);
	}

	return $ret;
}
?>