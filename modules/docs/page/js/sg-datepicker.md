<code>
$(document).on('focus', '.sg-datepicker', function(e) {
	var defaults={
		clickInput: true,
		dateFormat: "dd/mm/yy",
		altFormat: "yy-mm-dd",
		altField: "",
		disabled: false,
		monthNames: thaiMonthName,
		onSelect: function(dateText,inst) {
			// Process call back
			var callback=$(this).data('callback');
			if (callback) {
				if (callback=='submit') {
					$(this).closest('form').submit()
				} else if (typeof window[callback]==='function') {
					 window[callback](dateText,inst);
				} else {
					var url=callback+'/'
					window.location=url;
				}
			}
		},
	}
	var options = $.extend(defaults, $(this).data());
	//if (options.onSelect) 

	$(this).datepicker(options)
});
</code>