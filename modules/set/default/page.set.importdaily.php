<?php
/**
 * Import symbol daily data from text
 *
 * @param String $symbol
 * @param $_POST['data']
 * @return string
 */
function set_importdaily($self,$symbol) {
	if (!user_access('create set content')) return R::View('signform');
	$ret.='<h3>นำเข้าข้อมูลใหม่รายวัน</h3>';
	$symbol=SG\getFirst($symbol,strtoupper($_REQUEST['symbol']));

	if ($_POST['data'] && $symbol) {
		$lines=explode("\n",$_POST['data']);
		$sep="\t";
		foreach ($lines as $key=>$line) {
			$line=trim($line);
			if (empty($line)) continue;
			$row=str_getcsv($line,$sep);
			foreach ($row as $k=>$v) $row[$k]=trim($v);
			if ($row[0]=='title') {
				unset($lines[$key]);
				continue;
			}
			list($d,$m,$y)=explode('/',$row[0]);
			if ($y>2500) $y-=543;
			$values['pdate']=$y.'-'.$m.'-'.$d;
			$values['symbol']=$symbol;
			$values['popen']=$row[1];
			$values['maxprice']=$row[2];
			$values['minprice']=$row[3];
			$values['pclose']=$row[4];
			$values['volumes']=preg_replace('/[^0-9\.\-]/','',$row[7]);
			$values['values']=preg_replace('/[^0-9\.\-]/','',$row[8]);
			$values['created']=date('U');
			$stmt='INSERT INTO %setdaily% (`pdate`, `symbol`, `popen`, `maxprice`, `minprice`, `pclose`, `volumes`, `values`, `created`)
						VALUES
						(:pdate, :symbol, :popen, :maxprice, :minprice, :pclose, :volumes, :values, :created)';
			mydb::query($stmt,$values);
			if (!mydb()->_error) {
				$complete[]=$line;
				unset($lines[$key]);
			} else {
				$error.=print_o($row,'$row').print_o($values, '$values');
				$error.='<p>'.mydb()->_query.'<br />'.mydb()->_errno.'</p>';
			}
		}
		$post->data=implode("\n",$lines);
	}

	$form=new Form('import',url(q()),'edit-info');

	$form->symbol->type='text';
	$form->symbol->name='symbol';
	$form->symbol->label='ชื่อบริษัท';
	$form->symbol->cols=10;
	$form->symbol->value=htmlspecialchars($symbol);
	$lastDbs=mydb::select('SELECT * FROM %setdaily% WHERE `symbol`=:symbol ORDER BY `pdate` DESC LIMIT 1',':symbol',$symbol);
	$form->symbol->posttext=' ข้อมูลล่าสุดเมื่อ <strong>'.($lastDbs->pdate?sg_date($lastDbs->pdate,'d/m/ปปปป'):'').'</strong> ราคาตลาด <strong>'.number_format($lastDbs->pclose,2).'</strong> บาท';

	$form->data->type='textarea';
	$form->data->name='data';
	$form->data->label='CVS ( แยกฟิลด์ด้วยเครื่องหมาย | )';
	$form->data->rows=5;
	$form->data->value=htmlspecialchars($post->data);

	$form->button->type='submit';
	$form->button->items->save=tr('Save');
	$form->button->posttext='หรือ <a href="'.url('set').'">ยกเลิก</a>';

	$ret .= $form->build();
	$ret.=$error;
	$ret.='<iframe width="100%" height="1000" src="//www.set.or.th/set/historicaltrading.do?symbol='.$symbol.'&language=th&country=TH" style="border:none;"></iframe>';
	return $ret;
}
?>