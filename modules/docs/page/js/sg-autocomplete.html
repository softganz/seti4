<h2>SG-AUTOCOMPLETE</h2>

<h3>Data Attribute</h3>
<code>
data-query = url
data-item = n
data-minlength = n
data-class = string
data-width = string
data-altfld = string
data-select = object | string
data-callback = submit | url
</code>

<code>
$(document).on('focus', '.sg-autocomplete', function(e) {
	var $this=$(this)
	var minLength=1
	if ($this.data('minlength')) minLength=$this.data('minlength')
	$this
	.autocomplete({
		source: function(request, response){
			var para={}
			para.n=$this.data('item');
			para.q=request.term
			console.log("Query "+$this.data('query'))
			$.get($this.data('query'),para, function(data){
				response(data)
			}, "json");
		},
		minLength: minLength,
		dataType: "json",
		cache: false,
		open: function() {
			if ($this.data('class')) {
				$this.autocomplete("widget").addClass($this.data('class'));
			}
			if ($this.data('width')) {
				$this.autocomplete("widget").css({"width":$this.data('width')});
			}
		},
		focus: function(event, ui) {
			//this.value = ui.item.label;
			//event.preventDefault();
			return false
		},
		select: function(event, ui) {
			// Return in ui.item.value , ui.item.label
			// Do something with id
			console.log(ui.item.value);
			if ($this.data('altfld')) $("#"+$this.data('altfld')).val(ui.item.value);

			if ($this.data('select')!=undefined) {
				var selectValue=$this.data('select');
				if (typeof selectValue == 'object') {
					console.log(selectValue)
					var x;
					for (x in selectValue) {
						$('#'+x).val(ui.item[selectValue[x]]);
						console.log(x+" "+selectValue[x])
					}
				} else if (typeof selectValue == 'string') {
					$this.val(ui.item[selectValue]);
				}
			}
		//	if ($this.data('selectName')!=undefined) $('#'+$this.data('selectName')).val(ui.item.label);


			// Process call back
			var callback=$this.data('callback');
			if (callback) {
				if (callback=='submit') {
					$this.closest('form').submit()
				} else if (typeof window[callback]==='function') {
					 window[callback]($this,ui);
				} else {
					var url=callback+'/'+ui.item.value
					window.location=url;
				}
			}
			return false;
		}
	})
	.autocomplete( "instance" )._renderItem = function( ul, item ) {
		if (item.value=='...') {
			return $('<li class="ui-state-disabled -more"></li>')
			.append(item.label)
			.appendTo( ul );
		} else {
			return $( "<li></li>" )
			.append( "<a><span>"+item.label+"</span>"+(item.desc!=undefined ? "<p>"+item.desc+"</p>" : "")+"</a>" )
			.appendTo( ul )
		}
	}
});
</code>
