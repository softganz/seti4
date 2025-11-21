/**
 * Tools   :: Tools Javascript Library
 * Created :: 2025-11-20
 * Modify  :: 2025-11-20
 * Version :: 1
 */

export function checkAll(setting = {}) {
	console.log(setting)
	let $mainCheckbox = $(setting.checkbox);
	let $checkItem = $($mainCheckbox.data("checkItem"));
	let checkContainer = $mainCheckbox.data("checkContainer");

	// console.log($mainCheckbox);
	// console.log("Check Item ", $mainCheckbox.data());
	// console.log(checkContainer);
	// console.log($($mainCheckbox.data("checkItem")));

	$($mainCheckbox).on("change", function() {
		if (this.checked) {
			$checkItem.each((index, element) => {
				// if (element)
				if ($(element).is(":visible")) $(element).prop("checked", true);
			});
			// $checkItem.find(":visible").prop("checked", true);
		} else {
			$checkItem.prop("checked", false);
		}
	});

	$checkItem.closest(checkContainer).on("click", function() {
		let $this = $(this);
		let $checkbox = $this.find($mainCheckbox.data("checkItem"));

		console.log($checkbox.is(":checked"));
		console.log($checkbox);

		if ($checkbox.is(":checked")) {
			$checkbox.prop("checked", false);
		} else {
			$checkbox.prop("checked", true);
		}

		// $checkbox.prop("checked", true);
	})

	// $(onChange=\'this.checked ? $(".select-box").prop("checked", true) : $(".select-box").prop("checked", false)\' />' : '',

}