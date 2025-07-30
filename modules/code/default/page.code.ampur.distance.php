<?php
/**
 * Code    :: Ampur Distance 
 * Created :: 2019-05-15
 * Modify  :: 2025-07-30
 * Version :: 2
 *
 * @param Object $self
 * @param Int $var
 * @return String
 */

$debug = true;

function code_ampur_distance($self, $ampurId = NULL) {
	$fromSelect = post('from');

	$ampurInfo = R::Model('code.ampur.get', $ampurId);

	$isEdit = user_access('access administrator pages, administer contents') || (i()->ok && in_array(i()->uid, (Array) cfg('system')->createDistance));

	$ret = '<div id="ampur-distance">';
	$ret .= '<h3>'.$ampurInfo->id.' อำเภอ'.$ampurInfo->name.' จังหวัด'.$ampurInfo->changwatName.'</h3>';

	$provList = mydb::select('SELECT `provid`, `provname` FROM %co_province% ORDER BY CONVERT(`provname` USING tis620) ASC; -- {key: "provid", value: "provname"}')->items;

	$form = new Form([
		'action' => url('code/ampur/distance/'.$ampurId),
		'class' => 'sg-form -inlineitem',
		'method' => 'GET',
		'rel' => 'replace:#ampur-distance',
		'children' => [
			'from' => [
				'type' => 'select',
				'label' => 'จากจังหวัด',
				'options' => [''=>'== เลือกจังหวัด =='] + $provList,
				'value' => $fromSelect,
				'attr' => ['onChange' => '$(this).closest(\'form\').submit()'],
			],
		],
	]);

	$ret .= $form->build();

	//$ret .= print_o($ampurInfo);

	//$ret .= print_o(post(),'post');

	if ($fromSelect) {
		mydb::where('LEFT(`distid`,2) = :ampurId', ':ampurId', $fromSelect);
		mydb::where(NULL, ':toareacode', $ampurInfo->id);

		$stmt = 'SELECT
			d.`distid` `fromareacode`
			, d.`distname` `ampurName`
			, dt.`distance`
			, dt.`fixprice`
			, p.`provname` `changwatName`
			FROM %co_district% d
				LEFT JOIN %distance% dt ON LEFT(dt.`toareacode`,4) = :toareacode AND dt.`fromareacode` = d.`distid`
				LEFT JOIN %co_province% p ON p.`provid` = LEFT(d.`distid`,2)
			%WHERE%
			ORDER BY `distid` ASC';
	} else {
		mydb::where('dt.`toareacode` = :ampurId', ':ampurId', $ampurInfo->id);
		$stmt = 'SELECT
			  dt.*
			, d.`distname` `ampurName`
			, p.`provname` `changwatName`
			FROM %distance% dt
				LEFT JOIN %co_district% d ON d.`distid` = dt.`fromareacode`
				LEFT JOIN %co_province% p ON p.`provid` = LEFT(dt.`fromareacode`,2)
			%WHERE%
			ORDER BY CONVERT(`changwatName` USING tis620) ASC, `fromareacode` ASC
			';
	}

	$dbs = mydb::select($stmt);

	//$ret .= mydb()->_query;

	$inlineAttr = array();
	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('code/ampur/distance/update');
		$inlineAttr['data-id'] = $ampurInfo->id;
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$ret .= '<div id="code-info" '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table();
	$tables->thead = array('code -center -nowrap'=>'รหัสอำเภอ', 'name'=>'ชื่ออำเภอ', 'จังหวัด', 'tambon -amt -nowrap'=>'ระยะทาง (ก.ม.)', 'village -amt -nowrap'=>'เหมาจ่าย(บาท)');

	foreach ($dbs->items as $rs) {
		$tables->rows[] = array(
			$rs->fromareacode,
			$rs->ampurName,
			$rs->changwatName,
			view::inlineedit(
				array(
					'from'=>$rs->fromareacode,
					'to'=>$ampurInfo->id,
					'fld'=>'distance',
					'value'=>$rs->distance,
					'ret'=>'numeric',
				),
				$rs->distance,
				$isEdit,
				'numeric'
			),
			view::inlineedit(
				array(
					'from'=>$rs->fromareacode,
					'to'=>$ampurInfo->id,
					'fld'=>'fixprice',
					'value'=>$rs->fixprice,
					'ret'=>'money',
				),
				$rs->fixprice,
				$isEdit,
				'money'
			),
		);
	}

	$ret .= $tables->build();

	$ret .= '</div>';

	$ret .= '</div>';

	//$ret .= print_o($dbs);
	return $ret;
}
?>