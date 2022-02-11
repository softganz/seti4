<?php
function test_autocomplete_json($self) {
	die('{
    "query": "Unit",
    "suggestions": ["United Arab Emirates", "United Kingdom", "United States"]
}');
}
?>