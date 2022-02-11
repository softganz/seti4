<?php
/**
* Brand Management
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function icar_brand($serlf) {
	//		if (!(user_access('administer projects') || project_model::is_trainer_of($tpid))) return 'Access denied';
	if ($_REQUEST['q']) {
		$stmt='SELECT uid, username, name
						FROM %users% u
						WHERE u.`username` LIKE :q OR u.`name` LIKE :q  OR u.email LIKE :q
						ORDER BY u.name ASC';
		$dbs=mydb::select($stmt,':q','%'.$_REQUEST['q'].'%');
		
		$result=array();
		foreach ($dbs->items as $rs) {
			$result[] = array('value'=>$rs->uid, 'label'=>htmlspecialchars($rs->name),'detail'=>'<img src="'.model::user_photo($rs->username).'" width="32" height="32" />');
		}
		if (debug('api')) {
			$result[]=array('value'=>'query','label'=>$dbs->_query);
			$result[]=array('value'=>'num_rows','label'=>'Result is '.$dbs->_num_rows.' row(s).');
		}
		return $result;
	} else if ($_REQUEST['uid']) {
		$stmt='INSERT INTO %topic_user% (`tpid`, `uid`, `membership`) VALUES (:tpid, :uid, :membership)
						ON DUPLICATE KEY UPDATE `membership`=:membership;';
		mydb::query($stmt,':tpid',$tpid, ':uid', $_REQUEST['uid'], ':membership', 'Owner');
		$user=mydb::select('SELECT uid,username,name FROM %users% WHERE uid=:uid LIMIT 1',':uid',$_REQUEST['uid']);
		$ret='<img src="'.model::user_photo($user->username).'" width="32" height="32" alt="'.htmlspecialchars($user->name).'" title="'.htmlspecialchars($user->name).'" />'.$user->name.'(Owner)';
		model::watch_log('project','add owner',$user->name.'('.$user->uid.') was added to be an owner of project '.$tpid.' by '.i()->name.'('.i()->uid.')');
	} else {
		$ret.='<input type="text" name="brand" class="form-text" placeholder="ยี่ห้อรถ" />';
		$ret .= '
		<script type="text/javascript">
		$(document).ready(function() {
			var $form=$("form#add-owner");
			$form.find(".button,label").hide();
			$form.find("#edit-name")
				.focus()
				.autocomplete({
					source: function(request, response){
						$.get($("#add-owner").attr("action")+"?q="+encodeURIComponent(request.term), function(data){
							response($.map(data, function(item){
							return {
								label: item.label,
								value: item.value,
								detail: item.detail,
							}
							}))
						}, "json");
					},
					minLength: 2,
					dataType: "json",
					cache: false,
					select: function(event, ui) {
						this.value = ui.item.label;
						// Do something with id
						$.post($form.attr("action"),{uid: ui.item.value}, function(data) {
							$form.closest("td").find("ul").append("<li>"+data+"</li>");
							$form.parent().html("");
						});
						return false;
					}
				})
				.data( "autocomplete" )._renderItem = function( ul, item ) {
					return $( "<li></li>" )
						.data( "item.autocomplete", item )
						.append( "<a>"+item.detail+item.label+"</a>" )
						.appendTo( ul );
				};
		});
		</script>';
	}
	return $ret;
}
?>