$(document).ready(function () {
	$(".button--expand").click(function() {
		$("#comment").toggle()
		var show=$("#comment").css("display")
		if (show=="none") {
			$("body#project").css("margin-right","0px")
			$(this).css({'right':4,'opacity':1})
			$(this).text("<")
		} else {
			$("body#project").css("margin-right","320px")
			$(this).css({'right':"270px",'opacity':0.5})
			$(this).text(">")
		}
	});

	$(document).on("click",".project-toogle-display",function(){
		var $parent=$(this).parent();
		$parent.next().toggle();
		if ($(this).children().hasClass('-up')) {
			$(this).children().removeClass('-up').addClass('-down');
		} else {
			$(this).children().removeClass('-down').addClass('-up');
		}
	});

	$(document).on("click",".project-develop-plan-add .form-text",function(){
		$(this).parent("form").find('.btn').css({display:"block",margin:"16px auto"});
	})

	$(document).on("click",'.__plan_showmore',function() {
		var $this=$(this);
		var $icon=$this.children('.icon');
		if ($icon.hasClass('-down')) {
			$icon.removeClass('-down').addClass('-up');
			$this.next().show();
		} else {
			$icon.removeClass('-up').addClass('-down');
			$this.next().hide();
			//$this.
		}
		//console.log($this.next().html());
		if ($this.next().html()==undefined) {
			console.log("Loading"+$this.attr("href"));
			$this.after("<div>Loading...</div>");
			$.get($this.attr("href"),function(html){
				$this.next().replaceWith(html);
			});
		}
		return false;
	});

	$("input.datainput-disable[type=radio]").attr('disabled', true);
	$("input.datainput-disable[type=radio][checked]").attr('disabled', false);
	$('.checkbox:has(.datainput-disable[type="radio"])').css({'color':'#bbb'});

	$('.checkbox:has(.datainput-disable[type="checkbox"])').css({'color':'#bbb'});
	$('.checkbox:has(.datainput-disable[checked="checked"])').css({'color':'#333'});

	$("input.datainput-disable[type=checkbox]").attr('disabled', true);
	$("input.datainput-disable[type=checkbox][checked]").attr('disabled', false);


})


function projectDevelopUpdatePlanTitle($this,data,$parent) {
	console.log($this.closest('.project-develop-plan-item').html());
	$this.closest('.project-develop-plan-item').children('a').children('.-title').text(data.value);
}

function projectDevelopCategoryLinkSelect($this,data,$parent) {
	//console.log($this, data, $parent)
	//console.log('VALUE '+$this.val()+' PLAN '+$this.data('planning'))
	// value = 2 => ref = 7
	// value = 4 => ref = 5
	var refid = $this.data('planning')
	var $ele = $('#project-develop-issue .inline-edit-field[value=' + refid +']')

	if (data.value) {
		//console.log("ADD Plan "+data.value)
		//console.log("PLAN ",$ele)
		$ele.prop('checked', false).trigger('click')
	} else {
		//console.log("REMOVE Plan "+data.value)
		//console.log("PLAN ",$ele)
		$ele.prop('checked', true).trigger('click')
	}
}
