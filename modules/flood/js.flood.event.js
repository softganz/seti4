var floodEventCommentBoxHeight='30px'
var isFirstTimeOfEvent=true
var isFloodCommentWriting=false
var floodEventRefreshTime=1200000

$(document).on('click','#form-item-flood-event-submit .btn.-primary',function() {
	var $form=$(this).closest('form');
	if ($("#edit-msg").val()=='' && $("#flood-event-station").val()=='') {
		alert('กรุณาป้อน "รายละเอียดสถานการณ์ฝนหรือน้ำท่วม" หรือ "เลือกสถานี"')
		return false
	}
	/*
	var para={};
	para.msg=$("#flood-event-msg").val();
	para.where=$("#flood-event-where").val();
	para.when=$("#flood-event-when").val();
	$.post($form.attr('action'),para,function(data) {
		notify(data.msg,10000);
		if (!data.error) {
			$("#flood-event-show").prepend(data.html);
			$("#flood-event-msg").val('');
			$("#flood-event-where").val('');
		}
	}, 'json');
	*/
	notify('กำลังบันทึก')
	$form.ajaxForm({
		dataType: "json",
		/*			target: "#imed-app", */
		success: function(data) {
			notify(data.msg,10000);
			console.log("Saved & Clear")
			if (!data.error) {
				//$("#flood-chat-show").prepend(data.html)
				$("#edit-msg").val('')
				$("#edit-where").val('')
				$("#edit-photoimg").clearInputs()
				$("#edit-station").val('')
				$('#edit-level').hide()
			}
		}
	}).submit();
	return false;
});

$(document).on('click','#flood-event-submit',function() {
	var $form=$(this).closest('form');
	if ($("#flood-event-msg").val()=='' && $("#flood-event-station").val()=='') {
		alert('กรุณาป้อน "รายละเอียดสถานการณ์ฝนหรือน้ำท่วม" หรือ "เลือกสถานี"')
		return false
	}
	/*
	var para={};
	para.msg=$("#flood-event-msg").val();
	para.where=$("#flood-event-where").val();
	para.when=$("#flood-event-when").val();
	$.post($form.attr('action'),para,function(data) {
		notify(data.msg,10000);
		if (!data.error) {
			$("#flood-event-show").prepend(data.html);
			$("#flood-event-msg").val('');
			$("#flood-event-where").val('');
		}
	}, 'json');
	*/
	notify('กำลังบันทึก')
	$form.ajaxForm({
		dataType: "json",
		/*			target: "#imed-app", */
		success: function(data) {
			notify(data.msg,10000);
			console.log("Saved & Clear")
			if (!data.error) {
				$("#flood-event-show").prepend(data.html)
				$("#flood-event-msg").val('')
				$("#flood-event-where").val('')
				$("#flood-event-photoimg").clearInputs()
				$("#flood-event-station").val('')
				$('#form-event-level').hide()
			}
		}
	}).submit();
	return false;
});

$(document).on('change','#flood-event-photoimg', function() {
	$("#flood-event-msg").focus();
});

$(document).on('click','#flood-event-refresh',function() {
	notify("รีเฟรช");
	$.get($(this).data('url'), function(html) {
		$("#flood-event-show").html(html);
		notify();
	})
});

$(document).on('click','.flood-event-comment-box', function(e) {
	if (floodEventCommentBoxHeight==undefined) floodEventCommentBoxHeight=$(this).height();
	$(this).height("100px");
})
.on('keypress','.flood-event-comment-box', function(e) {
	isFloodCommentWriting=true;
	if (e.which == 13) {
		var $this=$(this);
		var $form=$this.closest('form');
		$.post($form.attr('action'),$form.serialize(),function(data) {
			notify(data.msg,10000);
			if (!data.error) {
				$form.prepend(data.html);
				$this.val('');
				isFloodCommentWriting=false;
				$this.height(floodEventCommentBoxHeight);
			}
		}, 'json');

		e.stopPropagation();
		return false;
	}
});

$(document).on('click','.form-item--flag>div, .form-item--station>div', function(e) {
	$(this).parent().children().removeClass('active')
	$(this).addClass('active')
	$(this).children().attr('checked','checked')
});

$(document).ready(function() {
	$(function () {
		(function request() {
			if (isFirstTimeOfEvent) {
				isFirstTimeOfEvent=false;
				setTimeout(request, floodEventRefreshTime);  //second
				return;
			} else if (isFloodCommentWriting) {
				setTimeout(request, floodEventRefreshTime);  //second
				return;
			}
			$.get($('#flood-event-refresh').data('url'),function(html) {
				$("#flood-event-show").html(html);
			});
			setTimeout(request, floodEventRefreshTime);  //second
		})(); //self Executing anonymous function
	});

});