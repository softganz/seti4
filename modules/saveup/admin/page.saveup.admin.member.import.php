<?php
/**
 * Import member from text file
 *
 * @para Array $_FILES['text_import']
 * @return String
 */
function saveup_admin_member_import($self) {
	if ($_POST['cancel']) location('saveup/admin');

	$self->theme->title='Import Member From Text File';

	$error=null;
	if ($_POST && is_uploaded_file($_FILES['text_import']['tmp_name'])) {
		$text_import=(object)$_FILES['text_import'];

		$lines = file($text_import->tmp_name);
		$msg_data.= '<style>.src { visibility:hidden;display:none; } </style>'._NL;
		$msg_data .= '<table border=1 width=100% cellspacing=0 cellpadding=1>'._NL;
		$msg_data .= '<tr>';
		$msg_data .= '<th></th><th>mid</th><th>prename</th><th>firstname</th><th>lastname</th><th>nickname</th>';
		$msg_data .= '<th>address</th><th>amphure</th><th>province</th><th>zip</th>';
		$msg_data .= '<th>caddress</th><th>camphure</th><th>cprovince</th><th>czip</th><th>phone</th>';
		$msg_data .= '</tr>'._NL;
		$import_item=$no=0;
		foreach ($lines as $l) {
			$no++;
			$l=trim($l);

			$rs=explode(',',$l);
			foreach ($rs as $key=>$value) $rs[$key]=trim($value);

			$msg_data .= '<tr>'._NL;
			$msg_data .= '<td><img src="'._img.'arrow.8.png" ';
			$msg_data .= 'onmouseover="dom.toggle(\'_'.$no.'\');" onmouseout="dom.toggle(\'_'.$no.'\');"></td>'._NL;
			foreach ($rs as $key=>$value) $msg_data.='<td>'.$value.'</td>';
			$msg_data .= '</tr>'._NL;

			$data_sql = 'INSERT INTO %saveup_member% VALUES (';
			for ($i=0;$i<=13;$i++) $data_sql.=($rs[$i]?'"'.$rs[$i].'"':'NULL').',';
			$data_sql = substr($data_sql,0,-1);
			$data_sql .=');';

			$ret_data_sql .= $data_sql.'<br />';
			if ( $_POST['save'] ) {
				db_querytable($data_sql);
				if ( $err=mysql_error() ) $ret_data_error .= '<font color=red>'.$id.':'.$err.'</font><br />'.$data_sql.'<br />';
				else $import_item++;
			}

			$msg_data.='<tr><td colspan="15"><div id="_'.$no.'" class="src">'.$l.'</div></td></tr>'._NL;
		}
		$msg_data .= '</table>';

		$ret .= '<b>Import data from file "'.$text_import->name.'"</b><br/>';
		$ret .= '<b>Total import '.$import_item.' record(s). from '.$before_record.' to '.$after_record.' recors(s).</b><br/>';
		$ret .= $msg_clear_data;
		$ret .= $msg_data;
		$ret .= $ret_data_sql;
		// $ret .= $_POST['show_data'] ? $msg_data : '';
		// $ret .= $_POST['show_sql'] ? $ret_data_sql : '';
		$ret .= $ret_data_error;
		if (!$error) {
			// start save new item
			$simulate=debug('simulate');

			if ($simulate) $ret.= '<p><strong>mobile sql :</strong> '.db_query_cmd().'</p>';
		}
	}

	if ($error) $ret.=message('error',$error);

	return new Scaffold([
		'appBar' => new AppBar([
			'title' => 'Import Member From Text File',
		]),
		'body' => new Widget([
			'children' => [
				$ret,
				new Form([
					'action' => url(q()),
					'enctype' => 'multipart/form-data',
					'children' => [
						'text_import' => [
							'type' => 'file',
							'name' => 'text_import',
							'label' => 'Import Member From Text File',
							'require' => true,
							'description' => 'Please select text file that contain member information seperate each field by , .',
						],
						'save' => [
							'type' => 'button',
							'save' => 'Import',
						],
					], // children
				]), // Form
			],
		]),
	]);
}
?>