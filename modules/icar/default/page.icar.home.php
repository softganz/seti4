<?php
/**
 * Car listing
 *
 * @param String $style
 * @return String
 */
function icar_home($self) {
	$style = post('s');

	$stmt = 'SELECT t.`title`, i.*, b.`name` brandname, p.`file` photo
		FROM %icar% i
			LEFT JOIN %topic% t USING(tpid)
			LEFT JOIN %tag% b ON b.`tid` = i.`brand`
			LEFT JOIN %topic_files% p ON p.`tpid` = i.`tpid` AND p.`type` = "photo" AND p.`cover` = "Yes"
		WHERE i.`pricetosale` > 0 AND (`sold` IS NULL OR `sold` != "Yes")
		ORDER BY i.`buydate` DESC';

	$dbs = mydb::select($stmt);

	$isEdit = user_access('administrator icars');

	R::View('icar.toolbar', $self);


	foreach ($dbs->items as $rs) {
		$ui = new Ui();
		$ui->add('<a href="'.url('icar/'.$rs->tpid).'" title="ดูรายละเอียด"><i class="icon -view"></i></a>');
		$rs->year = $rs->year>0?$rs->year:'';
		$photo = model::get_photo_property($rs->photo);
		$img = '<img class="thumbnail" src="'.($photo->_exists?$photo->_url:'/library/img/none.gif').'" />';
		$href = '<a href="'.url('icar/'.$rs->tpid).'" title="ดูรายละเอียด">';

		switch ($style) {
			case 'thumbnail' :
				$rows[] = '<div class="date"><span class="day">'.sg_date($rs->buydate,'ว').'</span> <span class="month">'.sg_date($rs->buydate,'ดดด').'</span> <span class="year">'.sg_date($rs->buydate,'ปปปป').'</div><!--date-->'._NL.$href.$img.'</a><h3>'.$href.$rs->title.'</a></h3><p>'.$rs->plate.'</p>';
				break;

			case 'icons' :
				$rows[] = '<div class="date"><span class="day">'.sg_date($rs->buydate,'ว').'</span> <span class="month">'.sg_date($rs->buydate,'ดดด').'</span> <span class="year">'.sg_date($rs->buydate,'ปปปป').'</span></div><!--date-->'._NL.$href.$img.'</a><h3>'.$href.$rs->title.'</a></h3><p>'.$rs->plate.'</p>';
				break;

			default :
				$rows[] = array(
					$href.$img.'</a>',
					$href.$rs->title.'</a>',
					$rs->plate,
					$rs->year,
					$rs->pricetosale>0 ? number_format($rs->pricetosale,2) : '',
					'<td>'.$ui->build('span').'</td>',
				);
				break;
		}
		//			if ($isEdit && $rs->partientname) $tables->rows[]=array('<td colspan="2">&nbsp;</td>','<td colspan="8"><span>ชื่อคนไข้ : '.$rs->partientname.' </span><span>ที่อยู่ : '.$rs->partientaddr.'</span></td>','config'=>array('class'=>'drugcontrol-partient'));
	}

	if ($style == 'thumbnail' || $style == 'icons') {
		$ret .= '<ul class="icar-list-'.$style.'"><li>'.implode('</li><li>',$rows).'</li></ul>';
	} else {
		$tables = new Table();
		$tables->addClass('icar-list-table');
		$tables->caption = $self->theme->title;
		$tables->thead = array('','ยี่ห้อ/แบบ','ทะเบียน','ปี','ราคาขาย','');
		$tables->rows = $rows;

		$ret .= $tables->build();
	}
	$ret .= '<br clear="all" />';
//		$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>