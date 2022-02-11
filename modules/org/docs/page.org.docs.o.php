<?php
/**
* Org Create New Docs
*
* @param Object $self
* @param Int $
* @return String
*/

import('model:org.php');

function org_docs_o($self, $orgId, $action = NULL, $trid = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('org.get',$orgId);
	$orgId = $orgInfo->orgid;

	$officerType = OrgModel::officerType($orgId, i()->uid);

	$isAdmin = user_access('administrator orgs');
	$isEdit = $isAdmin || $officerType;


	if (!$isEdit) return message('error', 'access denied');

	$orgInfo = R::Model('org.get', $orgId, '{debug:false}');

	if ($action == 'in') $typeTitle = 'หนังสือเข้า ';
	else if ($action == 'out') $typeTitle = 'หนังสือออก ';

	R::View('org.toolbar',$self,$typeTitle, 'docs', $orgInfo, '{modulenav: true}');



	if ($isEdit && $action == 'in') {
		$ret.='<div class="btn-floating -right-bottom"><a class="?-sg-action btn -floating -circle48" href="'.url('org/docs/o/'.$orgId.'/new/in').'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
	} else if ($isEdit && $action == 'out') {
		$ret.='<div class="btn-floating -right-bottom"><a class="?-sg-action btn -floating -circle48" href="'.url('org/docs/o/'.$orgId.'/new/out').'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
	}


	switch ($action) {
		case 'new':
			$data->orgid = $orgId;
			if ($trid == 'out') $ret .= R::View('org.docs.form.out', $data);
			else if ($trid == 'in') $ret .= R::View('org.docs.form.in', $data);
			break;
		
		case 'save':
			$data = (object) post('docs');
			if ($data->title) {
				$result = R::Model('org.docs.save', $data);
				//$ret .= mydb()->_query;
				//$ret .= print_o($result, $result);
			}
			if ($data->doctype == 'IN') $returl = 'org/docs/o/'.$orgId.'/in';
			else if ($data->doctype == 'OUT') $returl = 'org/docs/o/'.$orgId.'/out';
			else $returl = 'org/docs/o/'.$orgId;
			location($returl);
			//$ret .= print_o($data, '$data');
			break;

		case 'view':
			$data = R::Model('org.docs.get', $trid);
			//$ret .= print_o($data,'$data');
			if ($data->doctype == 'IN') $ret .= R::View('org.docs.form.in', $data, '{mode: "view"}');
			else if ($data->doctype == 'OUT') $ret .= R::View('org.docs.form.out', $data, '{mode: "view"}');
			break;

		case 'edit':
			$data = R::Model('org.docs.get', $trid);
			//$ret .= print_o($data,'$data');
			if ($data->doctype == 'IN') $ret .= R::View('org.docs.form.in', $data);
			else if ($data->doctype == 'OUT') $ret .= R::View('org.docs.form.out', $data);
			break;

		case 'delete':
			if ($orgId && $trid && SG\confirm()) {
				$stmt = 'DELETE FROM %org_doc% WHERE `docid` = :docid AND `orgid` = :orgid LIMIT 1';
				mydb::query($stmt, ':docid', $trid, ':orgid', $orgId);
				//$ret .= mydb()->_query;
				$ret .= 'ลบหนังสือเรียบร้อย';
			}
			break;

		default:
			mydb::where('`orgid` = :orgid', ':orgid', $orgId);
			if ($action == 'in') mydb::where('`doctype` = "IN"');
			if ($action == 'out') mydb::where('`doctype` = "OUT"');

			$orderBy = SG\getFirst(post('o'), 'id');
			$orderList = array(
										'id' => '`docid`',
										'date' => '`docdate`',
										'to' => 'CONVERT(`attnorg` USING tis620)',
										'no' => 'CONVERT(`docno` USING tis620)',
										'title' => 'CONVERT(`title` USING tis620)',
									);
			mydb::value('$order', $orderList[$orderBy]);

			$stmt = 'SELECT * FROM %org_doc% %WHERE% ORDER BY $order DESC';
			$dbs = mydb::select($stmt);
			//$ret .= mydb()->_query;

			$tables = new Table();
			$tables->addClass('-header-nowrap');
			$tables->thead = array(
												'เลขที่หนังสือ <a href="'.url('org/docs/o/'.$orgId.($action ? '/'.$action : ''), array('o'=>'no')).'"><i class="icon -sort'.($orderBy == 'no' ? '' : ' -gray').'"></i></a>',
												'date -out' => 'วันที่ <a href="'.url('org/docs/o/'.$orgId.($action ? '/'.$action : ''), array('o'=>'date')).'"><i class="icon -sort'.($orderBy == 'date' ? '' : ' -gray').'"></i></a>',
												'ถึง <a href="'.url('org/docs/o/'.$orgId.($action ? '/'.$action : ''), array('o'=>'to')).'"><i class="icon -sort'.($orderBy == 'to' ? '' : ' -gray').'"></i></a>',
												'title -hover-parent' => 'เรื่อง <a href="'.url('org/docs/o/'.$orgId.($action ? '/'.$action : ''), array('o'=>'title')).'"><i class="icon -sort'.($orderBy == 'title' ? '' : ' -gray').'"></i></a>'
											);
			foreach ($dbs->items as $rs) {
				$menu = '';
				if ($isEdit) {
					$menu = '<nav class="nav iconset -hover"><a  class="sg-action" href="'.url('org/docs/o/'.$rs->orgid.'/view/'.$rs->docid).'" data-rel="box"><i class="icon -viewdoc"></i></a> ';
					$menu .= '<a href="'.url('org/docs/o/'.$rs->orgid.'/edit/'.$rs->docid).'"><i class="icon -edit"></i></a>';
					$menu .= '<a class="sg-action" href="'.url('org/docs/o/'.$orgId.'/delete/'.$rs->docid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="notify" data-removeparent="tr"><i class="icon -delete"></i><span class="-hidden">ลบหนังสือ</span></a>';
					$menu .= '</nav>';
				}
				$tables->rows[] = array(
														$rs->docno,
														sg_date($rs->docdate, 'ว ดด ปปปป'),
														$rs->attnorg,
														$rs->title
														. $menu,
													);
			}
			$ret .= $tables->build();
			//$ret .= print_o($orgInfo, '$orgInfo');

			break;
	}


	return $ret;
}
?>