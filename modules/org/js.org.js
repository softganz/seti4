$(document).on('submit', '#org-add-person', function() {
	$('input[name="addname"]').val('').focus()
});


function orgMeetingSelect(element,ui) {
	$('#edit-meeting-atdate').val(ui.item.atDate)
	$('#edit-meeting-fromtime').val(ui.item.atTime)
	$('#edit-meeting-place').val(ui.item.location)
}

function orgMeetingAddMember(element,ui) {
	var action = element.closest('form').attr('action')
	var rel = element.closest('form').data('rel')
	notify('กำลังเพิ่มชื่อ')
	$.post(action+'?id='+ui.item.value,function(data) {
		$(rel).html(data)
		element.val('')
		notify('เพิ่มชื่อเรียบร้อย',5000)
	})
}