<h4>Data Attribute</h4>
<code>
data-rel = none | notify | this | parent | replace[#id] | box | element id
data-done = notify | javascript | callback | back | close | moveto | remove:parent .class | reload:url | load[->replace,before,after,append,prepend,prev,next,clear]:id,class: url
data-checkvalid = true | false
data-data-type = NULL , json
data-complete = remove | closebox
data-callback = function name | url
class = .-require
</code>

<h4>Script</h4>
<code>
$(document).on('submit', 'form.sg-form', function(e) {
	var $this=$(this)
	var rel=$this.data('rel');
	var checkValid=$this.data('checkvalid');
	var errorField='';
	var errorMsg='';
	console.log('sg-form submit of '+$this.attr('id'));
	if (checkValid) {
		console.log('Form Check input valid start.');
		$this.find('.require, .-require').each(function(i) {
			var $inputTag=$(this);
			console.log('Form check valid input tag '+$inputTag.prop("tagName")+' type '+$inputTag.attr('type')+' id='+$inputTag.attr('id'))
			if (($inputTag.attr('type')=='text' || $inputTag.attr('type')=='password' || $inputTag.attr('type')=='hidden' || $inputTag.prop("tagName")=='TEXTAREA') && $inputTag.val().trim()=="") {
				errorField=$inputTag;
				errorMsg='กรุณาป้อนข้อมูลในช่อง " '+$('label[for='+errorField.attr('id')).text()+' "';
				$inputTag.focus();
			} else if ($inputTag.prop("tagName")=='SELECT' && ($inputTag.val()==0 || $inputTag.val()==-1 || $inputTag.val()=='')) {
				errorField=$inputTag;
				errorMsg='กรุณาเลือกข้อมูลในช่อง " '+$('label[for='+errorField.attr('id')).text()+' "';
			} else if (($inputTag.attr('type')=='radio' || $inputTag.attr('type')=='checkbox')
									&& !$("input[name=\'"+$inputTag.attr('name')+"\']:checked").val()) {
				errorField=$inputTag;
				errorMsg=errorField.closest('div').children('label').first().text();
			}
			if (errorField) {
				console.log('Invalid input '+errorField.attr('id'));
				var invalidId=errorField.attr('id');
				$('#'+invalidId).focus();
				$('html,body').animate({ scrollTop: errorField.offset().top-100 }, 'slow');
				notify(errorMsg);
				return false;
			}
		});
		if (errorField) return false;
	}

	console.log("data-type="+$this.data('dataType'));

	if (rel!=undefined) {
		notify('กำลังดำเนินการ');
		console.log('Send form to '+$this.attr('action'));
		console.log('Result to '+rel);
		$.post($this.attr('action'),$this.serialize(), function(html) {
			console.log('Form submit completed.');
			if ($this.data('complete')=='remove') {
				$this.remove()
			} else if ($this.data('complete')=='closebox') {
				if ($(e.rel).closest('.sg-dropbox.box').length!=0) {
					$('.sg-dropbox.box').children('div').hide()
					$('.sg-dropbox.box.active').removeClass('active')
					//alert($(e.rel).closest('.sg-dropbox.box').attr('class'))
				} else {
					$.colorbox.close()
				}
			}
			console.log('Form output to '+rel);

			if (rel=='none') {
				;//do nothing
			} else if (rel=='notify') {
				notify(html,5000);
			} else if (rel=='this') {
				$this.html(html);
			} else if (rel=='parent') {
				$this.parent().html(html);
			} else if (rel.substr(0,7)=='replace') {
				var $ele;
				if (rel=='replace') $ele=$this;
				else {
					var target=rel.substr(8);
					$ele=$(target);
				}
				console.log(target)
				$ele.replaceWith(html);
			} else if (rel=='box') {
				$.colorbox({html:html,width:$('#colorbox').width(),opacity:0.5});
			} else if (rel.substring(0,1)=='.') {
				$this.closest(rel).replaceWith(html);
				console.log(rel)
			} else {
				$('#'+rel).html(html);
			}
			if (rel!='notify') notify()

			// Process callback function
			var callback=$this.data('callback');
			if (callback && typeof window[callback] === 'function') {
				window[callback]($this,html);
			} else if (callback) {
				window.location=callback;
			}
		},$this.data('dataType')==undefined?null:$this.data('dataType'));
		return false
	}
})
.on('keydown', 'form.sg-form input:text', function(event) {
	var n = $("input:text").length
	if(event.keyCode == 13) {
		event.preventDefault()
		var nextIndex = $('input:text').index(this) + 1
		if(nextIndex < n)
			$('input:text')[nextIndex].focus()
		return false
	}
});
</code>