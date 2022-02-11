<?php
/**
* Flood Camera water level
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

/**
 * Camera water level
 *
 * @param String $camid
 * @return String
 */
function flood_status_level($self,$camid=NULL) {
	$para=para(func_get_args(),1);
	$cam=R::Model('flood.camera.get',$camid);
	$self->theme->title=$cam->title.' - Camera Record';
	R::View('flood.toolbar',$self,$rs->title,NULL,$cam);
	if ($cam->_empty) return message('error','ไม่มีข้อมูล');

	$is_edit=user_access('administrator floods,operator floods','edit own flood content',$cam->uid);

	$ret .= R::Page('flood.status.levelshort',NULL,$camid,'items=20');
	return $ret;





	if ($is_edit) {
		if ($para->delete) {
			$stmt='DELETE FROM %flood_level% WHERE `aid`=:aid LIMIT 1';
			mydb::query($stmt,':aid',$para->delete);
			location('flood/camera/level/'.$camid);
		} else if ($_POST) {
			$post=(object)post();
			$post->waterlevel=SG\getFirst($post->waterlevel,0);
			$post->gatelevel=SG\getFirst($post->gatelevel,0);
			$post->camid=$camid;
			$post->uid=SG\getFirst(i()->uid,'func.NULL');
			$post->created=date('U');
			list($dd,$mm,$yy)=explode('/',$post->recdate);
			$post->rectime=sg_date($yy.'-'.$mm.'-'.$dd.' '.$post->rectime.':00','U');
			$stmt='INSERT INTO %flood_level% (`uid`, `rectime`, `camid`, `waterlevel`, `gatelevel`, `created`) VALUES (:uid, :rectime, :camid, :waterlevel, :gatelevel, :created)';
			mydb::query($stmt,$post);
		}
	}

	$stmt='SELECT l.*, w.bankheightleft, w.bankheightright, w.depth, w.gateheight FROM %flood_level% l LEFT JOIN %flood_water% w USING(`camid`) WHERE l.camid=:camid ORDER BY rectime DESC LIMIT 200';
	$dbs=mydb::select($stmt,':camid',$camid);
	$bankheightleft=$dbs->items[0]->bankheightleft;
	$bankheightright=$dbs->items[0]->bankheightright;
	$gateheight=$dbs->items[0]->gateheight;
	$depth=$dbs->items[0]->depth;

	$tables = new Table();
	$tables->addClass('water-level');
	$tables->caption=($bankheightleft?($gateheight?'ระดับคันบึง ':' ระดับตลิ่ง ').sg::sealevel($bankheightleft,2).($bankheightright?'/'.sg::sealevel($bankheightright,2):'').' ม.รทก.':'').($depth?' ระดับท้องคลอง '.sg::sealevel($depth,2).' ม.รทก.':'').($gateheight?' ระดับช่องระบายน้ำฉุกเฉิน '.sg::sealevel($gateheight,2).' ม.รทก.':'');
	$thead[]='วันที่';
	$thead[]='เวลา';
	$thead[]='ระดับน้ำ (ม.รทก.)';
	if ($bankheightleft) $thead[]='ต่ำกว่า'.($gateheight?'คันบึง':'ตลิ่ง').' (ม.) <a href="#" onclick="return false;" title="ค่าเป็นบวกหมายถึงระดับน้ำต่ำกว่าตลิ่ง(ยังไม่ล้นตลิ่ง) ค่าเป็นลบหมายถึงระดับน้ำสูงกว่าตลิ่ง(ล้นตลิ่ง) หน่วยเป็นเมตร">?</a>';
	if ($gateheight) {
		$thead[]='ต่ำว่าช่องระบายน้ำฉุกเฉิน (ม.)';
		$thead[]='ระยะเปิดประตูน้ำ (ม.)';
	}
	$thead[]='ระดับความสำคัญ';
	if ($is_edit) $thead[]='';
	$tables->thead=$thead;
	if ($is_edit) {
		$row[]='<form method="post"><input type="text" name="recdate" value="'.SG\getFirst($post->recdate,date('d/m/Y')).'" size="10" class="form-text require" />';
		for ($i=0;$i<24;$i++) {
			$hr=sprintf('%02d',$i);
			for ($m=0;$m<4;$m++) {
				$min=sprintf('%02d',$m*15);
				$r=round(date('i')/15);
				if ($r>3) $r=0;
				$option.='<option value="'.$hr.':'.($min).'"'.($hr==date('H') && $m==$r?' selected="selected"':'').'>'.$hr.'.'.$min.' น.</option>';
			}
		}
		$row[]='<select name="rectime" class="form-select require">'.$option.'</select';
		$row[]='<input type="text" name="waterlevel" size="5" class="form-text require" />';
		if ($bankheightleft) $row[]='';
		if ($gateheight) {
			$row[]='';
			$row[]='<input type="text" name="gatelevel" size="5" class="form-text" />';
		}
		$row[]='<td colspan="2"><input type="submit" class="button button-save" value="บันทึก" /></form></td>';
		$tables->rows[]=$row;
	}
	foreach ($dbs->items as $rs) {
		unset($row);
		$auto=$rs->uid?false:true;
		$row[]=sg_date($rs->rectime,'ว ดด ปป');
		$row[]=sg_date($rs->rectime,'H:i');
		$row[]=array(sg::sealevel($rs->waterlevel,2).($auto?'<a href="#" title="ค่าจากระบบโทรมาตร" onclick="return false;"><sup>?</sup></a>':''));
		if ($bankheightleft && !is_null($rs->waterlevel)) $row[]=number_format($bankheightleft-$rs->waterlevel,2).($bankheightright?'/'.number_format($bankheightright-$rs->waterlevel,2):'');
		else $row[]='-';
		if ($gateheight) {
			$row[]=number_format($gateheight-$rs->waterlevel,2);
			$row[]=number_format($rs->gatelevel,2);
		}
		$row[]=$rs->priority;
		$row[]=$is_edit?'<a href="'.url('flood/camera/level/'.$camid.'/delete/'.$rs->aid).'" title="ลบรายการ">X</a>':'';
		$tables->rows[]=$row;
	}

	$ret .= $tables->build();

	$ret.='<script type="text/javascript">
$(document).ready(function() {
$("input[name=recdate]").datepicker({
	clickInput:true,
	dateFormat: "dd/mm/yy",
	disabled: false,
	monthNames: thaiMonthName
});
});
</script>';
	return $ret;
}
?>