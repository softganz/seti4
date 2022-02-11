$(document).on('click','.form.-addtocart .btn.-primary', function() {
	console.log("Add to cart")
	let form = $(this).closest('form')
	let url = form.attr('action')
	let para = {}
	para.amt = form.find('.form-select').val()
	console.log(url,para)
	$.post(url, form.serialize(), function(data) {
		console.log(data)
		notify(data.msg)
		$('.cart-items').text(data.cartamt)
	},'json')
	return false
});

function ibuyMsgLikeDone($this, data) {
	console.log(data)
	if (data.liked) {
		$this.closest('.btn').removeClass('-inactive').addClass('-active')
	} else {
		$this.closest('.btn').removeClass('-active').addClass('-inactive')
	}

	if (data.liketimes) {
		$this.closest('.ui-item.-ibuy-activity').find('.liketimes').text(data.liketimes)
		$this.closest('.ui-item.-ibuy-activity').find('.-like-status').removeClass('-hidden')
	} else {
		$this.closest('.ui-item.-ibuy-activity').find('.-like-status').addClass('-hidden')
	}
}