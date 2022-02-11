<?php
/**
* Reorder port item by date
*
* @return String
*/
function set_admin_sort_date($self) {
	$stmt='set @i:=0;
			set @insId=0;

			ALTER IGNORE TABLE `sgz_setport` ADD `nid` INT NOT NULL AFTER `id` ;

			UPDATE `sgz_setport`
			SET nid=(SELECT @i := @i+1)
			ORDER BY date ASC, id ASC;

			UPDATE IGNORE `sgz_setport`
			SET id=nid
			WHERE id>=@insId
			ORDER BY nid ASC;

			ALTER TABLE `sgz_setport` DROP `nid`;';
	mydb::query($stmt);
	$ret.='Sort date';
	$ret.=mydb()->_query;
	return $ret;
}
?>