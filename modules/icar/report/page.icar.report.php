<?php
function icar_report($self) {
	$self->theme->title='รายงาน';

	$shop=icar_model::get_my_shop();

	R::View('icar.toolbar', $self, $shop->shopname);

	if ($shop->shopid) {
		$stmt = 'SELECT
			  COUNT(*) `totalCar`
			, COUNT(`saledate`) `totalSold`
			, SUM(IF(`saledate`,`costprice`,0)) `totalCostSold`
			, SUM(IF(`saledate`,`saleprice`,0)) `totalPriceSold`
			, SUM(IF(`saledate`,0,`costprice`)) `totalCostUnSold`
			, SUM(`costprice`) `totalCostPrice`
			, SUM(`saleprice`) `totalSalePrice`
			FROM %icar%
			WHERE `shopid`=:shopid LIMIT 1';

		$rs = mydb::select($stmt,':shopid',$shop->shopid);

		$ret.='<div class="cardsum">';
		$ret.='<div><h3>เดือนนี้</h3></div>';
		$ret.='<div><h3>เดือนที่แล้ว</h3></div>';
		$ret.='<div class="-c1"><h3>ทั้งหมด</h3>จำนวนรถ<br />ขายแล้ว '.number_format($rs->totalSold).' คัน<br />คงเหลือ '.number_format($rs->totalCar-$rs->totalSold).' คัน<br />ทั้งหมด '.number_format($rs->totalCar).' คัน<br />ต้นทุนขาย '.number_format($rs->totalCostSold,2).' บาท<br />ราคาขาย '.number_format($rs->totalPriceSold,2).' บาท<br />ต้นทุนคงเหลือ '.number_format($rs->totalCostUnSold,2).' บาท</div>';
		$ret.='</div>';
		//$ret.=print_o($rs,'$rs');
	}

	$ui=new Ui(NULL, 'ui-menu');
	$ui->add('<a href="'.url('icar/report/carbuy').'">รายงานการซื้อรถ</a>');
	$ui->add('<a href="'.url('icar/report/carsale').'">รายงานการขายรถ</a>');
	$ui->add('<a href="'.url('icar/report/instock').'">รายงานรถในคลังสินค้า</a>');
	$ui->add('<a href="'.url('icar/report/unpaiddown').'">รายงานเงินดาวน์ค้างชำระ</a>');
	//$ui->add('<a href="'.url('icar/report/instock').'">รายงานรถคงค้าง</a>');
	$ui->add('<sep>');
	//$ui->add('<a href="#">รายงานส่วนแบ่งกำไรผู้ร่วมทุน</a>');
	//$ui->add('<a href="#">รายงานการชำระ-ค้างจ่ายผู้ร่วมทุน</a>');
	$ret.=$ui->build();

	$ret.='<style type="text/css">
	.cardsum {padding:10px;background:#1565C0; color:#fff;}
	.cardsum p {margin:0; padding:0 0 0 16px;}
	.cardsum>div {width:25%; display:inline-block;vertical-align: top;}
	.cardsum>div>span {display:block;}
	.cardsum .itemvalue {font-size: 1.2em; line-height:1.2em;}
	</style>';

	return $ret;
}
?>