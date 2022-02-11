<?php
/**
 * Assessor main page
 *
 * @param Integer $userId
 * @param String $action
 * @param Integer $tranId
 * @return String
 */
function project_assessor_my($self) {
	R::View('project.toolbar',$self,'เครือข่ายนักติดตามประเมินผล','assessor');

	if (!i()->ok) return '<p class="notify">สำหรับผู้ที่เป็นสมาชิกเว็บอยู่แล้ว กรุณาเข้าสู่ระบบสมาชิกก่อนลงทะเบียน<br />หากยังไม่ได้เป็นสมาชิก กรุณา<a href="{url:user/register}">สมัครสมาชิกเว็บ</a>ให้เรียบร้อยก่อนลงทะเบียน</p>'.R::View('signform');

	$userId = i()->uid;

	$assessorInfo = mydb::select('SELECT * FROM %person_group% WHERE `groupname` = "assessor" AND `uid` = :uid LIMIT 1',':uid',$userId);

	if ($assessorInfo->_empty) {
		$ret .= '<p>ท่านยังไม่ได้ลงทะเบียนนักติดตามประเมินผล ต้องการลงทะเบียนคลิก <a class="btn -primary" href="'.url('project/assessor/register').'"><span>ลงทะเบียนนักติดตามประเมินผล</span></a>';
		return $ret;
	}

	mydb::clearprop($assessorInfo);
	$psnid = $assessorInfo->psnid;
	//$ret .= $userId.' '.$psnid;
	$assessorInfo->psnInfo = R::Model('person.get',$psnid);
	$psnInfo = $assessorInfo->psnInfo;
	//$psnInfo->assessorId = $
	//$ret .= print_o($assessorInfo,'$assessorInfo');

	$ret.=R::View('project.assessor.info',$assessorInfo);
	//$ret.=print_o($psnInfo,'$psnInfo');
	return $ret;
}
?>