<?php
/**
* Project LTC Page Model
*
* @param Object $self
* @param Int $orgId
* @return String
*/
function project_ltc_info($self, $fundInfo = NULL, $action = NULL, $tranId = NULL) {
	$orgId = $fundInfo->orgid;

	if (!$fundInfo->right->edit) return message('error', 'Access Denied');

	$ret = '';

	switch ($action) {

		case 'save':
			$post = (object) post('data');

			if ($post->contactname) {
				$post->flddata = sg_json_encode($post);
				$post->bigid = $tranId; 
				$post->keyname = 'project.ltc';
				$post->keyid = $orgId;
				$post->fldname = 'info.contact';
				$post->ucreated = i()->uid;
				$post->created = date('U');
				$post->modified = date('U');
				$post->umodified = i()->uid;

				$stmt = 'INSERT INTO %bigdata%
					(`bigid`, `keyname`, `keyid`, `fldname`, `flddata`, `created`, `ucreated`)
					VALUES
					(:bigid, :keyname, :keyid, :fldname, :flddata, :created, :ucreated)
					ON DUPLICATE KEY UPDATE
						  `flddata` = :flddata
						, `modified` = :modified
						, `umodified` = :umodified
					';

				mydb::query($stmt, $post);
				//$ret .= mydb()->_query;
			}
			//$ret .= print_o($post,'$post');
			break;

		default:
			$ret = 'NO ACTION';
			break;
	}

	return $ret;
}
?>