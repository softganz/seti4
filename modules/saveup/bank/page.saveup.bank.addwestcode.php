<?php
/**
 * Add new westcode
 *
 * @param Array $_POST['data']
 * @return String and die / Location
 */
function saveup_bank_addwestcode() {
	if (!user_access('create saveup content')) return R::View('signform');
	R::View('saveup.toolbar',$self,'ธนาคารขยะ','bank');

	if (post('name')) {
		$post=(object)post(NULL,_TRIM);
		if ($post->name) {
			$post->cat_group=_WESTCODE;
			$stmt='INSERT INTO %co_category%
							(`cat_id`,`cat_name`, `cat_group`, `cat_num1`, `cat_detail1`)
							VALUES (:cat_id, :name, :cat_group, :unitprice, :unitname)
							ON DUPLICATE KEY UPDATE `cat_name`=:name, `cat_detail1`=:unitname, `cat_num1`=:unitprice ';
			mydb::query($stmt,$post);
		}
	}
	$ret.=$this->_setting();
	return $ret;
}
?>