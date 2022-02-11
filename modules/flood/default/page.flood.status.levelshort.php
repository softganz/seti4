<?php
/**
* Flood :: Water Level
*
* @param Object $self
* @param Int $camId
* @return String
*/

$debug = true;

function flood_status_levelshort($self, $camId = NULL) {
	$para = para(func_get_args(),'items=200',1);
	$cam = R::Model('flood.camera.get',$camId);

	$self->theme->title = $cam->title;
	R::View('flood.toolbar',$self,$rs->title,NULL,$cam);

	$ret = '<header class="header">'._HEADER_BACK.'<h3>ระดับน้ำ</h3></header>';
	if ($cam->_empty) return message('error','ไม่มีข้อมูล');

	$stmt = 'SELECT
		l.*
		, w.`bankheightleft`, w.`bankheightright`, w.`depth`, w.`gateheight`
		FROM %flood_level% l
			LEFT JOIN %flood_water% w USING(`camid`)
		WHERE l.`camid` = :camid AND `priority` >= 0
		ORDER BY rectime DESC
		LIMIT '.addslashes($para->items);

	$dbs = mydb::select($stmt,':camid',$camId);

	$bankheightleft = $dbs->items[0]->bankheightleft;
	$bankheightright = $dbs->items[0]->bankheightright;
	$gateheight = $dbs->items[0]->gateheight;
	$depth = $dbs->items[0]->depth;

	$tables = new Table();
	$tables->addClass('water-level');
	$tables->caption = ($bankheightleft?($gateheight?'ระดับคันบึง ':' ระดับตลิ่ง ').sg::sealevel($bankheightleft,2).($bankheightright?'/'.sg::sealevel($bankheightright,2):'').' ม.รทก.':'').($depth?' ระดับท้องคลอง '.sg::sealevel($depth,2).' ม.รทก.':'').($gateheight?' ระดับช่องระบายน้ำฉุกเฉิน '.sg::sealevel($gateheight,2).' ม.รทก.':'');

	$thead[] = 'วันที่';
	$thead[] = 'เวลา';
	$thead[] = 'ระดับน้ำ (ม.รทก.)';
	if ($bankheightleft) $thead[] = 'ต่ำกว่า'.($gateheight?'คันบึง':'ตลิ่ง').' (ม.) <a href="#" onclick="return false;" title="ค่าเป็นบวกหมายถึงระดับน้ำต่ำกว่าตลิ่ง(ยังไม่ล้นตลิ่ง) ค่าเป็นลบหมายถึงระดับน้ำสูงกว่าตลิ่ง(ล้นตลิ่ง) หน่วยเป็นเมตร">?</a>';
	if ($gateheight) {
		$thead[] = 'ต่ำว่าช่องระบายน้ำฉุกเฉิน (ม.)';
		$thead[] = 'ระยะเปิดประตูน้ำ (ม.)';
	}
	$thead[] = '<a class="sg-action btn -primary -circle32" href="'.url('flood/camera/level/'.$camId).'" data-rel="box"><i class="icon -addbig -white"></i></a><!--ระดับความสำคัญ-->';
	if ($is_edit) $thead[] = '';
	$tables->thead = $thead;

	foreach ($dbs->items as $rs) {
		unset($row);
		$row[] = sg_date($rs->rectime,'ว ดด ปป');
		$row[] = sg_date($rs->rectime,'H:i');
		$row[] = sg::sealevel($rs->waterlevel,2);
		if ($bankheightleft && !is_null($rs->waterlevel)) {
			$row[] = number_format($bankheightleft-$rs->waterlevel,2).($bankheightright?'/'.number_format($bankheightright-$rs->waterlevel,2):'');
		} else {
			$row[] = '-';
		}

		if ($gateheight) {
			$row[] = number_format($gateheight-$rs->waterlevel,2);
			$row[] = number_format($rs->gatelevel,2);
		}
		$row[] = $rs->priority;
		$tables->rows[] = $row;
	}

	$ret .= $tables->build();

	return $ret;
}
?>