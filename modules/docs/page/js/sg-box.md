<code>
$(document).on('click','.sg-box', function() {
	var defaults={
		fixed: true,
		opacity: 0.5,
		width: "90%",
		maxHeight: "90%",
		maxWidth: "90%",
	}
	var $this=$(this)
	var group=$this.data("group");
	var options = $.extend(defaults, $this.data());
	if (options.group) options.rel=options.group

	if ($this.data('confirm')!=undefined && !confirm($this.data('confirm'))) {
		return false
	}

	if ($this.attr('href')=='#close') {
		$.colorbox.close()
		return false
	}

	$('.sg-box[data-group="'+group+'"]').each(function(i){
		var $elem=$(this);
		$elem.colorbox(options);
	});
	options.open=true
	$this.colorbox(options);

	// Process callback function
	var callbackFunction=$this.data("callback");
	if (callbackFunction && typeof window[callbackFunction] === 'function') {
		window[callbackFunction]($this,'');
	}

	return false
});
</code>