<?php
/**
* Module Method
* Created 2017-04-08
* Modify  2019-05-24
*
* @param Object $self
* @param Int $gltype
* @return String
*/

$debug = true;

function saveup_gl_openbalance($self, $gltype) {
	$para=para(func_get_args(),1);

	$self->theme->title='กลุ่มออมทรัพย์ - ยอดยกมา';

	$isEdit = true;
	$inlineAttr = array();
	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('saveup/gl/openbalance/update');
		if (post('debug')) $inlineAttr['data-debug'] = 'inline';
	}
	$ret .= '<div id="saveup-balance" '.sg_implode_attr($inlineAttr).'>'._NL;

	switch ($gltype) {
		case 'saving' : $cardcond='gltype="DEBT"';break;
		case 'loan' : $cardcond='gltype="ASSEST"';break;
	}
	$stmt='SELECT `card`,`desc` FROM %saveup_glcode% WHERE '.$cardcond.' AND `card` IS NOT NULL';
	$cardlist=mydb::select($stmt);

	foreach ($cardlist->items as $ckey=>$citem) {
		$tbname='m'.$ckey;
		$cardsql.='(SELECT `amt` FROM %saveup_memcard% '.$tbname.' WHERE '.$tbname.'.`mid`=m.`mid` AND '.$tbname.'.`card`="'.$citem->card.'" AND '.$tbname.'.`trno` IS NULL) `'.$citem->card.'`, ';
	}
	$cardsql=trim($cardsql,', ');

	$items=SG\getFirst($para->items,50);

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS m.*, '.
			$cardsql.'
		FROM %saveup_member% AS m
			LEFT JOIN %saveup_balance% b USING(mid)
		WHERE status="active" AND firstname IS NOT NULL
		ORDER BY m.mid ASC;
		';
	$users= mydb::select($stmt);

	$total_items = $dbs->_found_rows;
	$pagenv = new PageNavigator($items,$para->page,$total_items,q());

	//$no=$pagenv->FirstItem();

	if ($users->_empty) return $ret.message('error','ไม่มีสมาชิกตามเงื่อนไขที่กำหนด');

	$ret .= $pagenv->show._NL;

	$tables = new Table();
	$tables->addClass('saveup-member-list');
	$tables->id='saveup-openbalance';
	$tables->thead=array('ID', 'date'=>'สมาชิกเมื่อ','ชื่อ - สกุล');

	foreach ($cardlist->items as $citem) $tables->thead['money '.$citem->card]=$citem->desc;

	foreach ($users->items as $rs) {
		unset($row);
		$row = array(
			$rs->mid,
			$rs->date_approve?sg_date($rs->date_approve,'ว ดด ปป'):'',
			$rs->prename.$rs->firstname.' '.$rs->lastname
		);

		foreach ($cardlist->items as $ckey=>$citem) {
			$row[] = view::inlineedit(
				array(
					'mid'=>$rs->mid,
					'fld'=>$citem->card,
					'value'=>$rs->{$citem->card},
					'ret'=>'numeric',
				),
				number_format($rs->{$citem->card},2),
				$isEdit,
				'numeric'
			);
			//'<span class="editable" mid="'.$rs->mid.'" fld="'.$citem->card.'">'.number_format($rs->{$citem->card},2).'</span>';
			/*
			$row[]=array(
							'class'=>'col-money editable',
							'mid'=>$rs->mid,
							'fld'=>$citem->card,
							number_format($rs->{$citem->card},2)
							);
			*/
			$total[$citem->card] += $rs->{$citem->card};
		}
		$row['config']=array('id'=>$rs->mid);
		$tables->rows[]=$row;
	}

	$tables->tfoot[] = array('','','',number_format($total['SAVING-DEP'],2),number_format($total['SAVING-SPECIAL'],2));
	$ret .= $tables->build();

	$ret .= $pagenv->show._NL;

	$ret .= '</div>';

	/*
	$ret.='
<script type="text/javascript">
$(document).ready(function() {
var waiting=false;
$("#saveup-openbalance").click(function(event) {
	if (waiting) return false;
	notify();
	if ($(event.target).is(".editable")) {
		$container=$(event.target);
		var mid=$container.attr("mid");
		var fld=$container.attr("fld");
		oldValue=$container.text();
		$input=$("<input />").attr({"type":"text","value":$container.text(),
					"size":"5","style":"text-align:right;width:100%;"});
		$container.html($input).
			children("input").
			focus()
			.select()
			.blur(function() {
				if ($(this).val()==oldValue) {
					$container.html(oldValue);
					return false;
				}
				waiting=true;
				notify("กำลังบันทึก");
				value=$(this).val();
				$.get("'.url('saveup/gl/openbalance/update','mid="+mid+"&fld="+fld+"&v="+value').',function(data) {
					notify("บันทึกเรียบร้อย",10000);
					$container.text(data);
					waiting=false;
				});
			})
			.keypress(function(event) {
				if (event.keyCode==27) {
					notify("ยกเลิกการแก้ไข",2000);
					$container.html(oldValue);
					return false;
				} else if (event.target.nodeName=="TEXTAREA") {
					return true;
				} else if (event.keyCode==13) {
					$(this).blur();
					return false;
				}
			});
;
		event.stopPropagation();
	}
});
});
</script>';
*/
	return $ret;
}
?>