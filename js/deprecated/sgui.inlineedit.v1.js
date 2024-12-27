// @deprecated
/*
* jQuery Extension :: sg-inline-edit
* Softganz inline edit field
* Written by Panumas Nontapan
* https://softganz.com
* Using <div class="sg-inline-edit"><span class="inline-edit-field" data-type="text"></span></div>
* DOWNLOAD : https://github.com/NicolasCARPi/jquery_jeditable
*/
(function($) { // sg-inline-edit
	let version = '1.01'
	let sgInlineEditAction = 'click'
	let updatePending = 0
	let updateQueue = 0
	let database;
	let ref
	let debug
	let value

	$.fn.sgInlineEdit = function(target, options = {}) {
		// default configuration properties
		if (typeof $.fn.editable === 'undefined') {
			console.log('ERROR :: $.editable is not load')
			return
		}

		if ('disable' === target) {
			//$(this).data('disabled.editable', true);
			return;
		}
		if ('enable' === target) {
			//$(this).data('disabled.editable', false);
			return;
		}
		if ('destroy' === target) {
			//$(this)
			//.unbind($(this).data('event.editable'))
			//.removeData('disabled.editable')
			//.removeData('event.editable');
			return;
		}

		let $this = $(this)
		let $parent = $this.closest('.sg-inline-edit')
		let postUrl = $this.data('updateUrl')
		let inputType = $this.data('type');
		let callback = $this.data('callback');
		// console.log($parent.data('updateUrl'))
		// console.log($this)
		// console.log($parent.data());
		// console.log($this.data())
		// console.log(options)

		if (postUrl === undefined) postUrl = $parent.data('updateUrl');

		// console.log('POST URL = ',postUrl)

		debug = $parent.data('debug') ? true : false

		if (inputType == 'money' || inputType == 'numeric' || inputType == 'text-block') {
			inputType = 'text'
		} else if (inputType == 'radio' || inputType == 'checkbox') {
			// console.log('RADIO or CHECKBOX Click:',$this)
			// console.log('$this.attr(value) = ',$this.attr('value'))
			value = $this.is(':checked') ? $this.attr('value') : ''
			// console.log('value = ', value)
			//self.save($this, value, callback)
			//return
		} else if (inputType == 'link') {
			return
		} else if (inputType == '' || inputType == undefined) {
			inputType = 'text'
			$this.data('type','text')
		}

		let defaults = {
			type: inputType,
			result: 'json',
			container : $(this),
			/*
			onblur : function(value) {
					$(this).closest('.inline-edit-field').removeClass('-active');
					notify(value)
					$(this).closest('form').submit();
				},
				*/
			// onblur: function() {'submit'},
			onblur: $this.data('onblur') ? $this.data('onblur') : 'submit',
			data: function(value, settings) {
					if ($this.data('data'))
						return $this.data('data');
					else if ($this.data('value') != undefined)
						return $this.data('value');
					else if (value == '...')
						return '';
					return value;
				},
			loadurl: $this.data('loadurl'),
			/*loaddata : function(value, settings) {
					console.log($this.data('loaddata'))
					if ($this.data('loaddata')) {
					}
					return {foo: 'bar'};
				},
				*/
				/*
			callback: function(result, settings, submitdata) {
					console.log('CALLBACK')
					console.log(result)
					console.log(settings)
					console.log(submitdata)
					//$this.html('<span>'+result+'</soan>')
				},
				*/
			before : function() {
					//let height = $this.height()
					//console.log('BEFORE EDIT '+$this.attr('class')+' height = '+$this.height())
					//$this.height('500px')
					//$this.find('.form-textarea').height($this.prop('scrollHeight')+'px');
					//$this.find('.form-textarea').height('100%')

					let options = $this.data('options')
					let callbackFunction = options != undefined && options.hasOwnProperty('onBefore') ? options.onBefore : null
					//console.log("BEFORE CALLBACK ",callbackFunction)
					if (callbackFunction && typeof window[callbackFunction] === 'function') {
						window[callbackFunction]($this,$parent);
					}
				},
			cancel		: $(this).data('button')=='yes' ? '<button class="btn -link -cancel"><i class="icon -material -gray">cancel</i><span>ยกเลิก</span></button>':null,
			submit		: $(this).data('button')=='yes' ? '<button class="btn -primary"><i class="icon -material -white">done_all</i><span>บันทึก</span></button>':null,
			placeholder: $(this).data('placeholder') ? $(this).data('placeholder') : '...',
		}

		let dataOptions = $this.data('options')
		// console.log('typeof container',typeof dataOptions.container)
		// if (typeof dataOptions.container === "object") delete dataOptions.container
		let settings = $.extend({}, $.fn.sgInlineEdit.defaults, defaults, options, dataOptions)
		// console.log(typeof settings.container)
		// if (typeof settings.container === 'object') delete settings.container
		// console.log('dataOptions',dataOptions)
		//console.log($this.data('options'))
		// console.log('SG-INLINE-EDIT SETTING:',settings)

		if (dataOptions && 'debug' in dataOptions && dataOptions.debug) debug = true

		if ($this.data('type') == 'textarea') settings.inputcssclass = 'form-textarea'
		else if ($this.data('type') == 'text') settings.inputcssclass = 'form-text'
		else if ($this.data('type') == 'numeric') settings.inputcssclass = 'form-text -numeric'
		else if ($this.data('type') == 'money') settings.inputcssclass = 'form-text -money'
		else if ($this.data('type') == 'email') settings.inputcssclass = 'form-text -email'
		else if ($this.data('type') == 'url') settings.inputcssclass = 'form-text -url'
		else if ($this.data('type') == 'autocomplete') settings.inputcssclass = 'form-text -autocomplete'
		else if ($this.data('type') == 'select') settings.inputcssclass = 'form-select'

		self.validValue = function($this, newValue) {
			if ($this.data('ret') != 'numeric') return true

			newValue = newValue.replace(/[^0-9.\-]+|\.(?!\d)/g, '')// = parseFloat(newValue)
			// console.log('minValue = ',$this.data("minValue"),' newValue = ',newValue,' IS ',newValue*1 < $this.data('minValue')*1)
			if ($this.data('minValue') != undefined && newValue*1 < $this.data('minValue')*1) {
				// console.log('less than minValue')
				return false
			} else if ($this.data('maxValue') != undefined && newValue*1 > $this.data('maxValue')*1) {
				// console.log('more than maxValue')
				// console.log('Reverse value to ',$this.data('value'))
				// console.log($this.html())
				// console.log($this)
				//$this.html($('<span />').html($this.data('value')))
				return false
			} else {
				// console.log('not check or valid')
				return true
			}
		}

		self.save = function($this, value, callback) {
			// console.log('Update Value = '+value)
			// console.log($parent.data('updateUrl'))
			// console.log('postUrl = ', postUrl)
			// console.log($parent.data());
			// console.log($this.data());

			if (postUrl === undefined) {
				// console.log('ERROR :: POSTURL UNDEFINED')
				notify('ข้อมูลปลายทางสำหรับบันทึกข้อมูลผิดพลาด')
				return
			}
			// console.log("POST")

			// if (!validValue($this, value)) {
			// 	notify('ข้อมูลไม่อยู่ในช่วงที่กำหนด')
			// 	return
			// }

			let para = $.extend({},$parent.data(), $this.data())

			delete para['options']
			delete para['data']
			delete para['event.editable']
			delete para['uiAutocomplete']
			para.action = 'save';
			para.value = value.replace(/\"/g, "\"")
			if (settings.var) para[settings.var] = para.value
			$this.data('value', para.value)

			//if (settings.blank === null && para.value === "") para.value = null
			//console.log(settings.blank)

			if (debug) console.log('UPDATE PARA:', para)

			updatePending++
			updateQueue++

			notify('กำลังบันทึก กรุณารอสักครู่....' + (debug ? '<br />Updating : pending = '+updatePending+' To = '+postUrl+'<br />' : ''))

			// Lock all inline-edit-field until post complete
			$parent.find('.inline-edit-field').addClass('-disabled')

			// console.log(postUrl)
			//console.log('length='+$('[data-group="'+para.group+'"]').length)
			//console.log(para)

			$.post(postUrl,para, function(data) {
				updatePending--
				$parent.find('.inline-edit-field').removeClass('-disabled')

				if (typeof data == 'string') {
					let tempData = data
					data = {}
					data.value = para.value
					if (debug) data.msg = tempData
				}

				//if (data == '' || data == '<p>&nbsp;</p>')
				//	data = '...';

				// console.log('RETURN DATA:', data)

				if (para.ret == 'refresh') {
					window.location = window.location
				} else if ($this.data('type') == 'autocomplete') {
					$this.data('value',para.value)
					$this.html('<span class="-for-input">'+data.value+'</span>');
				} else if ($this.data('type') == 'radio') {
				} else if ($this.data('type') == 'checkbox') {
				} else if ($this.data('type') == 'select') {
					let selectValue
					if ($this.data('data')) {
						selectValue = $this.data('data')[data.value]
					} else {
						selectValue = data.value
					}
					$this.html('<span class="-for-input">'+selectValue+'</span>')
				} else {
					// console.log('VALUE = ',data.value)
					$this.html('<span class="-for-input">'+(data.value == null ? '<span class="placeholder -no-print">'+settings.placeholder+'</span>' : data.value)+'</span>')
				}


				let replaceTrMsg = '';
				//console.log('para.tr='+para.tr+' data.tr='+data.tr)
				if (para.tr != data.tr) {
					if (data.tr == 0)
						data.tr = '';
					//console.log(para.group+' : '+para.tr+' : '+data.tr)
					$('[data-group="'+para.group+'"]').data('tr', data.tr)
					replaceTrMsg = 'Replace tr of group '+para.group+' with '+data.tr
					//console.log(replaceTrMsg);
				}

				if (debug) console.log('data', data)

				notify(
					(data.error ? data.error : (data.msg ? data.msg : ''))
					+ (debug && data.debug ? '<div class="-sg-text-left" style="white-space: normal;">Update queue = '+updateQueue+', Update pending = '+updatePending+'<br />PARAMETER : group = '+para.group+', FIELD = '+para.fld+', TRAN = '+para.tr+', VALUE = '+data.value+'<br />DEBUG : '+data.debug+'<br />Return : TRAN = '+data.tr+'<br />'+replaceTrMsg+'</div>' : ''),
					debug ? 300000 : 5000);

			}, settings.result)
			.fail(function(response) {
				notify('ERROR ON POSTING. Please Contact Admin.');
				// console.log(response)
			}).done(function(response) {
				// console.log('response', response)
				// Process callback function
				let callbackFunction = settings.callback ? settings.callback : $this.data('callback')

				if (debugSG) console.log("CALLBACK ON COMPLETE -> " + callbackFunction + (callbackFunction ? '()' : ''))
				if (callbackFunction) {
					if (typeof window[callbackFunction] === 'function') {
						window[callbackFunction]($this,response,$parent);
					} else if (settings.callbackType == 'silent') {
						$.get(callbackFunction, function() {})
					} else {
						window.location = callbackFunction;
					}
				}

				// Process action done
				if (settings.done) sgActionDone(settings.done, $this, response);
				console.log('$.sgInlineEdit DONE!!!')
			});
		}


		// SAVE value immediately when radio or checkbox click
		if (inputType == 'radio' || inputType == 'checkbox') {
			self.save($this, value, callback)
		} else {
			$this.editable(
				function(value, settings) {
					if (validValue($this, value)) {
						self.save($this, value, callback)
						return value
					} else {
						notify('ข้อมูลไม่อยู่ในช่วงที่กำหนด')
						return $this.data('value')
					}
				} ,
				settings
			).trigger('edit')
		}

		// $this.editable(function(value, settings) {
		// 	self.save($this, value, callback)
		// 	return value
		// } ,
		// settings
		// ).trigger('edit')


		// RETURN that can call from outside
		return {
			// GET VERSION
			getVersion: function() {
				return version
			},

			// SAVE DATA IN FORM TO TARGET
			update: function($this, value, callback) {
				self.save($this, value, callback)
			}
		}
	}

	/* Publicly accessible defaults. */
	$.fn.sgInlineEdit.defaults = {
		indicator				: '<div class="loader -rotate"></div>',
		tooltip 				: 'คลิกเพื่อแก้ไข',
		cssclass				: 'inlineedit',
		width						: 'none',
		height 					: 'none',
		var							: null,
		cancelcssclass	: 'btn -link -cancel',
		submitcssclass	: 'btn -primary',
		showButtonPanel	: true,
		indicator 			: 'SAVING',
		event 					: 'edit',
		inputcssclass		: '',
		autocomplete 		: {},
		datepicker 			: {},
	}


	$(document).on(sgInlineEditAction, '.sg-inline-edit .inline-edit-field:not(.-readonly)', function() {
		console.log('$.sgInlineEdit version ' + version + ' start')
		$(this).sgInlineEdit()
	})

	$(document).on('keydown', ".sg-inline-edit .inline-edit-field", function(evt) {
		// TAB Key
		if(evt.keyCode == 9) {
			let $this = $(this);
			let $allBox = $this.closest(".sg-inline-edit");
			let nextBox = '';
			let currentBoxIndex = $(".inline-edit-field").index(this);
			if (currentBoxIndex == ($(".inline-edit-field").length-1)) {
				nextBox = $(".inline-edit-field:first");
			} else {
				nextBox = $(".inline-edit-field").eq(currentBoxIndex+1);
			}
			$(this).find("input").blur();
			$(nextBox).trigger('click')
			//		notify('Index='+currentBoxIndex+$this' Length='+$allBox.children(".inline-edit-field").length+' Next='+nextBox.data('fld'))
			return false;
		};
	});
})(jQuery);