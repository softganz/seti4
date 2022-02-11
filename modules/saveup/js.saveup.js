/**
* Saveup :: Genernal JavaScript
* Created 2019-05-19
* Modify  2020-12-10
*
* @usage <script type="text/javascript" src="saveup/js.saveup.js"></script>
*/

function saveupRcvMemberSelect($this, ui) {
	var memberId = ui.item.value
	console.log("Member ID="+memberId)
	//$this.val(ui.item.label);
	$this.prev().val(ui.item.value)

	// Create empty row of transaction
	var $tr    = $this.closest("tr")
	var $clone = $tr.clone()
	$clone.find("input").val("")
	$clone.find("select").empty()

	var $tableBody = $this.closest("tbody")
	var $trLast = $tableBody.find("tr:last")
	$trLast.after($clone)


	// Get GL code for selected member
	var codeurl = $this.closest("form").data("codeurl")
	var para = {mid:memberId}
	var $select = $this.closest("tr").find(".saveup-rcvtr-glcode")
	$.getJSON(codeurl,para,function(data){
		$select.empty()
		$.each(data, function(i, obj){
			var attr = {}
			attr.value = obj.value
			attr["data-loanno"] = obj.loanno
			if (obj['pay-amt']) attr['data-pay-amt'] = obj['pay-amt']
			if (obj['pay-fee']) attr['data-pay-fee'] = obj['pay-fee']
			//console.log(attr)
			$select.append($("<option>").text(obj.label).attr(attr))
		})
		$select.focus()
	})
}