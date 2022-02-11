$(document).ready(function() {

	$(document).on('click','#add-costcode',function() {
		$(this)
		.closest('form')
		.find('#edit-icarcost-costcode')
		.replaceWith('<input type="text" name="cost[costname]" class="form-text -require -fill" placeholder="ชื่อรายการใหม่" />');
		$('[name="cost[costname]"]').focus();
		return false;
	})

})