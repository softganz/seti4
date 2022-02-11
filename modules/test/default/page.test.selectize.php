<?php
function test_selectize($self) {
	$ret = '<h2>SELECTIZE Test</h2>';


	head('<script src="/library/js/test.autocomplete.js"></script>');
	head('<script src="/library/js/selectize.js"></script>');
	head('<link rel="stylesheet" type="text/css" href="/library/css/selectize.css" />');

	$ret .= '<input class="form-text -fill" type="text" name="country" id="autocomplete" value="ต.โคก" /><br /><br />';


	$ret .= '<script>
	$("#autocomplete").autocomplete({
    serviceUrl: "/happy/mehealthpromotion.com/api/address",
    onSelect: function (suggestion) {
        alert("You selected: " + suggestion.value + ", " + suggestion.data);
    }
});
</script>';


	$ret .= '<input type="text" id="input-tags" class="demo-default selectized" value="ต.โคกม่วง" tabindex="-1" style="display: none;"><br /><br />';


	$ret .= '<input class="sg-address form-text -fill" />';

	$ret .= '<script>

	/*
	var options = {
    delimiter: ",",
    persist: false,
    create: function(input) {
        return {
            value: input,
            text: input
        }
    }
  }*/


var options = {
    valueField: "title",
    labelField: "title",
    searchField: "title",
    options: [],
    create: false,
    render: {
        option: function(item, escape) {
            	console.log("ITEM ",item,escape)

            var actors = [];
            for (var i = 0, n = item.length; i < n; i++) {
                actors.push("<span>" + escape(item[i].label) + "</span>");
            }

            return "<div>" +
                "<img src=\"" + escape(item.posters.thumbnail) + "\" alt=\"\">" +
                "<span class=\"title\">" +
                    "<span class=\"name\">" + escape(item.name) + "</span>" +
                "</span>" +
                "<span class=\"description\">" + escape(item.value || "No synopsis available at this time.") + "</span>" +
            "</div>";
        }
    },
    load: function(query, callback) {
    	console.log("Query "+query)
        if (!query.length) return callback();
        $.ajax({
            url: "/happy/mehealthpromotion.com/api/address",
            type: "GET",
            dataType: "json",
            data: {
                q: query,
                page_limit: 10,
                apikey: "w82gs68n8m2gur98m6du5ugc"
            },
            error: function(e) {
            	console.log("ERROR",e)
                callback();
            },
            success: function(res) {
                callback(res.movies);
            }
        });
    }
}

	var $select = $("#input-tags").selectize(options);

	</script>';
	return $ret;
}
?>