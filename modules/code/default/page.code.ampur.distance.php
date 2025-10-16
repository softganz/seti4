<?php
/**
 * Code    :: Ampur Distance 
 * Created :: 2019-05-15
 * Modify  :: 2025-08-01
 * Version :: 3
 *
 * @param Object $self
 * @param Int $var
 * @return String
 */

use Softganz\DB;

$debug = true;

function code_ampur_distance($self, $ampurId = NULL) {
	$fromSelect = post('from');

	$ampurInfo = R::Model('code.ampur.get', $ampurId);

	$isEdit = user_access('access administrator pages, administer contents') || (i()->ok && in_array(i()->uid, (Array) cfg('system')->createDistance));

	$ret = '<div id="ampur-distance">';
	$ret .= '<h3>'.$ampurInfo->id.' อำเภอ'.$ampurInfo->name.' จังหวัด'.$ampurInfo->changwatName.'</h3>';

	$form = new Form([
		'action' => url('code/ampur/distance/'.$ampurId),
		'class' => 'sg-form -inlineitem',
		'method' => 'GET',
		'rel' => 'replace:#ampur-distance',
		'children' => [
			'from' => [
				'type' => 'select',
				'label' => 'จากจังหวัด',
				'choice' => ChangwatModel::items(NULL, ['selectText' => '=== เลือกจังหวัด ===']),
				'value' => $fromSelect,
				'attr' => ['onChange' => '$(this).closest(\'form\').submit()'],
			],
		],
	]);

	$ret .= $form->build();

	if ($fromSelect) {
		$dbs = DB::select([
			'SELECT
			d.`distid` `fromareacode`
			, d.`distname` `ampurName`
			, dt.`distance`
			, dt.`fixprice`
			, p.`provname` `changwatName`
			FROM %co_district% d
				LEFT JOIN %distance% dt ON LEFT(dt.`toareacode`,4) = :toareacode AND dt.`fromareacode` = d.`distid`
				LEFT JOIN %co_province% p ON p.`provid` = LEFT(d.`distid`,2)
			%WHERE%
			ORDER BY `distid` ASC',
			'where' => [
				'%WHERE%' => [
					['LEFT(`distid`,2) = :ampurId AND RIGHT(`distName`, 1) != "*"', ':ampurId' => $fromSelect],
					[NULL, ':toareacode' => $ampurInfo->id]
				]
			]
		]);
	} else {
		$dbs = DB::select([
			'SELECT
			  dt.*
			, d.`distname` `ampurName`
			, p.`provname` `changwatName`
			FROM %distance% dt
				LEFT JOIN %co_district% d ON d.`distid` = dt.`fromareacode`
				LEFT JOIN %co_province% p ON p.`provid` = LEFT(dt.`fromareacode`,2)
			%WHERE%
			ORDER BY CONVERT(`changwatName` USING tis620) ASC, `fromareacode` ASC',
			'where' => [
				'%WHERE%' => [
					['dt.`toareacode` = :ampurId', ':ampurId' => $ampurInfo->id],
				]
			]
		]);
	}

	$inlineAttr = [];
	if ($isEdit) {
		$inlineAttr['class'] = 'sg-inline-edit';
		$inlineAttr['data-update-url'] = url('code/ampur/distance/update');
		$inlineAttr['data-id'] = $ampurInfo->id;
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}
	$ret .= '<div id="code-info" '.sg_implode_attr($inlineAttr).'>'._NL;

	$tables = new Table([
		'thead' => ['code -center -nowrap'=>'รหัสอำเภอ', 'name'=>'ชื่ออำเภอ', 'จังหวัด', 'tambon -amt -nowrap'=>'ระยะทาง (ก.ม.)', 'village -amt -nowrap'=>'เหมาจ่าย(บาท)'],
		'children' => array_map(
			function($rs) use($ampurInfo, $isEdit) {
				return [
					$rs->fromareacode,
					$rs->ampurName,
					$rs->changwatName,
					view::inlineedit(
						[
							'from' => $rs->fromareacode,
							'to' => $ampurInfo->id,
							'fld' => 'distance',
							'value' => $rs->distance,
							'ret' => 'numeric',
						],
						$rs->distance,
						$isEdit,
						'numeric'
					),
					view::inlineedit(
						[
							'from' => $rs->fromareacode,
							'to' => $ampurInfo->id,
							'fld' => 'fixprice',
							'value' => $rs->fixprice,
							'ret' => 'money',
						],
						$rs->fixprice,
						$isEdit,
						'money'
					),
				];
			},
			$dbs->items
		),
	]);

	$ret .= $tables->build();

	$ret .= '</div>';

	$ret .= '</div>';

	//$ret .= print_o($dbs);
	return $ret;
}
?>