<?php
function project_admin_distance($self, $action = NULL) {
	R::View('project.toolbar',$self,'Project Administrator','admin');
	$self->theme->sidebar=R::View('project.admin.menu','value');

	$ret .= '<a href="'.url('project/admin/distance/import').'">Import Distance</a>';

	switch ($action) {
		case 'import':
			$ret .= __project_admin_distance_import();
			break;

		default:
			# code...
			break;
	}
	return $ret;
}

function __project_admin_distance_import() {
		$simulate = post('simulate');
		$ret.='<h3>นำเข้าข้อมูลใหม่</h3>';

		if ($simulate) $ret.='<p class="notify">SIMULATE</p>';
		if (post('data')) {
			$lines=explode("\n",post('data'));
			$sep="\t";
			//$sep="\t";
			foreach ($lines as $key=>$line) {
				$line=trim($line);
				if (empty($line)) continue;
				$line=preg_replace('/  /',' ',$line);
				$row=explode($sep,$line);
				//$row=str_getcsv($line);
				foreach ($row as $k=>$v) $row[$k]=trim($v);
				if ($row[0]=='sid') {
					unset($lines[$key]);
					continue;
				}

				// sid(auto) scode prename firstname lastname  serno office ozip home hzip phone email idcard remark
				// sid(auto) รหัสนักศึกษา คำนำหน้า ชื่อ นามสกุล รุ่น ที่ทำงาน รหัสไปรษณีย์ บ้าน รหัสไปรษณีย์ โทรศัพท์ อีเมล์ 13หลัก หมายเหตุ

				$values['fromareacode'] = SG\getFirst($row[0], '');
				$values['toareacode'] = SG\getFirst($row[1], '');
				$values['distance'] = SG\getFirst($row[2], 0);
				$values['fixprice'] = SG\getFirst($row[3], NULL);

				if ($simulate) {
					$ret.=print_o($row,'$row').print_o($values, '$values');
				} else {
					$stmt='INSERT INTO %distance%
								(
									`fromareacode`, `toareacode`, `distance`, `fixprice`
								)
								VALUES
								(
									:fromareacode, :toareacode, :distance, :fixprice
								)
								ON DUPLICATE KEY UPDATE
								`distance` = :distance
								, `fixprice` = :fixprice
								';
					mydb::query($stmt,$values);

					if (!mydb()->_error) {
						//$ret.=print_o($row,'$row').print_o($values, '$values');
						$complete[]=$line;
						unset($lines[$key]);
					} else {
						$ret.='<p class="notify">*** ERROR***</p>';
						$ret.=print_o($row,'$row').print_o($values, '$values');
						$ret.='<p>'.mydb()->_query.'<br /><font color="red">'.mydb()->_error.'</font></p>';
					}
				}
			}
			$post->data=implode("\n",$lines);
		}

		$form = new Form(NULL, url(q()));

		$form->addField('data',
						array(
							'type' => 'textarea',
							'label' => '1 บรรทัดต่อ 1 พื้นที่ แยกฟิลด์ด้วยเครื่องหมาย tab )',
							'class' => '-fill',
							'rows' => 20,
							'value' => htmlspecialchars($post->data),
						)
					);

		$form->addField('simulate',
						array(
							'type' => 'checkbox',
							'options' => array('1' => 'Simulate'),
							'value' => $simulate,
						)
					);

		$form->addField('save',
						array(
							'type' => 'button',
							'value' => '<i class="icon -save -white"></i>นำเข้าข้อมูล',
							'pretext' => '<a href="'.url('project/admin/distance').'">{tr:Cancel}</a>',
							'containerclass' => '-sg-text-right',
						)
					);

		$ret .= $form->build();

		$ret.='<style type="text/css">
		</style>';
		return $ret;
}
?>