<?php
/**
* iMed :: Create Patient Visit Item
* Created 2021-08-17
* Modify  2021-08-17
*
* @param String $arg1
* @return Widget
*
* @usage imed/patient/{id}/visit.create
*/

$debug = true;

import('page:imed.api.visit.create');

class ImedPsycApiVisitCreate extends ImedApiVisitCreate {
	function build() {
		$result = parent::build();

		if (!$result->seqId) return $result;

		$post = post();
		// $data = (Object) post('data');

		$post = array_slice($post, 5);

		$post['problem-detail'] = $this->_clearNl($post['problem-detail']);
		$post['guide-detail-eatdrug'] = $this->_clearNl($post['guide-detail-eatdrug']);
		$post['guide-detail-exacerbation'] = $this->_clearNl($post['guide-detail-exacerbation']);
		$post['guide-detail-nocaretaker'] = $this->_clearNl($post['guide-detail-nocaretaker']);
		$post['guide-detail-usedrug'] = $this->_clearNl($post['guide-detail-usedrug']);
		$post['guide-detail-economy'] = $this->_clearNl($post['guide-detail-economy']);
		$post['follow'] = $this->_clearNl($post['follow']);
		$post['nextvisit'] = $this->_clearNl($post['nextvisit']);

		$mastData->uid = i()->uid;
		$mastData->qtdate = date('Y-m-d');
		$mastData->qtgroup = 3;
		$mastData->qtform = 'PSYCVISIT';
		$mastData->psnId = post('psnId');
		$mastData->seqId = $result->seqId;
		$mastData->data = (Object) $post;
		$mastData->collectname = i()->name;
		$mastData->created = date('U');

		$resultQt = R::Model('qt.save', $mastData);

		// debugMsg(post(),'post()');
		// debugMsg('<pre>'.$resultQt->msg.'</pre>');
		// debugMsg($resultQt, '$resultQt');
		return $result;
	}

	function _clearNl($str) {
		// debugMsg('<pre>'.$str.'</pre>');
		$str = str_replace(array("\r\n", "\r", "\n"), "<br />", $str);
		$str = preg_replace('/["\'\\\\]+/', '', $str);
		// $str = preg_replace('/[\r\n]+/', '', trim($str));
		// $str = trim(preg_replace("/[\r\n/]+/", '', trim($str)));
		// Remove " ' \ /
		// $str = stripslashes($str);
		// debugMsg('<pre>=>'.$str.'</pre>');
		return $str;
	}
}
?>