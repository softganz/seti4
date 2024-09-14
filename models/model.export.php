<?php
/**
* Model   :: Export File
* Created :: 2024-09-14
* Modify  :: 2024-09-14
* Version :: 3
*
* @param Array $args
* @return Object
*
* @usage import('model:export.php')
* @usage new ExportModel([])
* @usage ExportModel::function($conditions)
*/

load_lib('SimpleXLSXGen.php', 'external/shuchkin'); // https://github.com/shuchkin/simplexlsxgen

class ExportModel {
	private static function header($fileName, $fileType) {
			header("Content-Disposition: attachment; filename=\"$fileName\"");
			header("Content-Type: application/'.$fileType");
			header("Content-Type: text/xls; charset=UTF-8");
	}

	static function cvs($args = []) {
		$args = (Object) array_merge(
			[
				'fileName' => cfg('domain.short').'-'.date('Y-m-d-H-i').".cvs", // String
				'delimiter' => ',', //
				'cleanTag' => true,
				'convertLeadingZero' => true,
				'debug' => false, // boolean
				'header' => [], // Array
				'children' => [], // Array
			],
			(Array) $args
		);

		if (!$args->debug) self::header($args->fileName, 'cvs');

		ob_start();
		$df = fopen("php://output", 'w');
		fputs($df, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
		fputcsv($df, (Array) $args->header);
		foreach ($args->children as $row) {
			fputcsv($df, (Array) $row);
		}
		fclose($df);
		return ob_get_clean();
	}

	static function xlsx($args = []) {
		$args = (Object) array_merge(
			[
				'fileName' => cfg('domain.short').'-'.date('Y-m-d-H-i').".xlsx", // String
				'delimiter' => ',', //
				'cleanTag' => true,
				'convertLeadingZero' => true,
				'debug' => false, // boolean
				'header' => [], // Array
				'children' => [], // Array
			],
			(Array) $args
		);

		// $books = [
		// 	['ISBN', 'title', 'author', 'publisher', 'ctry' ],
		// 	[618260307, 'The Hobbit', 'J. R. R. Tolkien', 'Houghton Mifflin', 'USA'],
		// 	[908606664, 'Slinky Malinki', 'Lynley Dodd', 'Mallinson Rendel', 'NZ']
		// ];
		// $xlsx = Shuchkin\SimpleXLSXGen::fromArray( $books );
		// $xlsx->saveAs('books.xlsx'); // or downloadAs('books.xlsx') or $xlsx_content = (string) $xlsx

		$xlsx = Shuchkin\SimpleXLSXGen::fromArray(array_merge([$args->header], $args->children));
		$xlsx->downloadAs($args->fileName);
	}

	static function xls($args = []) {
		$args = (Object) array_merge(
			[
				'fileName' => cfg('domain.short').'-'.date('Y-m-d-H-i').".xls", // String
				'delimiter' => ',', //
				'cleanTag' => true,
				'convertLeadingZero' => true,
				'debug' => false, // boolean
				'header' => [], // Array
				'children' => [], // Array
			],
			(Array) $args
		);

		if (!$args->debug) self::header($args->fileName, 'vnd.ms-excel');

		// Create header line
		$ret.='<tr>';
		foreach($args->header as $row) $ret.='<td>'.$row.'</td>';
		$ret.='</tr>';

		foreach($args->children as $row) {
			if(!$flag) {
				// display field/column names as first row
				$flag = true;
			}
			$row = array_filter(
				(Array) $row,
				function($text){return !(is_object($text) || is_array($text));}
			);
			// array_walk($row, '__r_setValueType');
			array_walk($row, 'sg_cleanXlsSepString');
			array_walk($row, 'strip_tags');
			$ret .= '<tr><td>'.implode('</td><td class="text">', (Array) $row).'</td></tr>';
		}
		//if ($options->cleanTag) $ret=strip_tags($ret);

		$ret = self::xlsHeader()
			. $ret
			. self::xlsFooter();
		return $ret;
	}

	/**
	* Clean XML Seperator String
	* @param String $str
	* @return
	*/
	private static function setValueType(&$str) {
		//$str=gettype($str).$str;
		// if (gettype($str)=='string' && substr($str,0,1)=='0') $str='="'.$str.'"';
		// if (gettype($str) == 'string' && substr($str,0,1) == '0') $str = '\''.$str;
	}

	private static function xlsHeader() {
		$header = '<html xmlns:o="urn:schemas-microsoft-com:office:office"
			xmlns:x="urn:schemas-microsoft-com:office:excel"
			xmlns="http://www.w3.org/TR/REC-html40">

			<head>
			<meta http-equiv=Content-Type content="text/html; charset=utf-8">
			<meta name=ProgId content=Excel.Sheet>
			<meta http-equiv="Content-Language" content="th" />
			<!--[if gte mso 9]><xml>
			 <o:DocumentProperties>
			  <o:LastAuthor>Sriram</o:LastAuthor>
			  <o:LastSaved>2005-01-02T07:46:23Z</o:LastSaved>
			  <o:Version>10.2625</o:Version>
			 </o:DocumentProperties>
			 <o:OfficeDocumentSettings>
			  <o:DownloadComponents/>
			 </o:OfficeDocumentSettings>
			</xml><![endif]-->
			<style>
			<!--table
				{mso-displayed-decimal-separator:"\.";
				mso-displayed-thousand-separator:"\,";}
			@page
				{margin:1.0in .75in 1.0in .75in;
				mso-header-margin:.5in;
				mso-footer-margin:.5in;}
			tr
				{mso-height-source:auto;}
			col
				{mso-width-source:auto;}
			br
				{mso-data-placement:same-cell;}
			.style0
				{mso-number-format:General;
				text-align:general;
				vertical-align:bottom;
				white-space:nowrap;
				mso-rotate:0;
				mso-background-source:auto;
				mso-pattern:auto;
				color:windowtext;
				font-size:10.0pt;
				font-weight:400;
				font-style:normal;
				text-decoration:none;
				font-family:Tahoma;
				mso-generic-font-family:auto;
				mso-font-charset:0;
				border:none;
				mso-protection:locked visible;
				mso-style-name:Normal;
				mso-style-id:0;}
			td
				{mso-style-parent:style0;
				padding-top:1px;
				padding-right:1px;
				padding-left:1px;
				mso-ignore:padding;
				color:windowtext;
				font-size:10.0pt;
				font-weight:400;
				font-style:normal;
				text-decoration:none;
				font-family:Tahoma;
				mso-generic-font-family:auto;
				mso-font-charset:0;
				mso-number-format:General;
				text-align:general;
				vertical-align:bottom;
				border:none;
				mso-background-source:auto;
				mso-pattern:auto;
				mso-protection:locked visible;
				white-space:nowrap;
				mso-rotate:0;}
			.xl24
				{mso-style-parent:style0;
				white-space:normal;}
			.num {
				mso-number-format:General;
			}
			.text{
				mso-style-parent:style0;
				mso-number-format:"\@";/*force text*/
			}
			-->
			</style>
			<!--[if gte mso 9]><xml>
			 <x:ExcelWorkbook>
			  <x:ExcelWorksheets>
			   <x:ExcelWorksheet>
				<x:Name>srirmam</x:Name>
				<x:WorksheetOptions>
				 <x:Selected/>
				 <x:ProtectContents>False</x:ProtectContents>
				 <x:ProtectObjects>False</x:ProtectObjects>
				 <x:ProtectScenarios>False</x:ProtectScenarios>
				</x:WorksheetOptions>
			   </x:ExcelWorksheet>
			  </x:ExcelWorksheets>
			  <x:WindowHeight>10005</x:WindowHeight>
			  <x:WindowWidth>10005</x:WindowWidth>
			  <x:WindowTopX>120</x:WindowTopX>
			  <x:WindowTopY>135</x:WindowTopY>
			  <x:ProtectStructure>False</x:ProtectStructure>
			  <x:ProtectWindows>False</x:ProtectWindows>
			 </x:ExcelWorkbook>
			</xml><![endif]-->
			</head>

			<body link=blue vlink=purple>
			<table x:str border=0 cellpadding=0 cellspacing=0 style="border-collapse: collapse;table-layout:fixed;">';
		return $header;
	}

	static function xlsFooter() {
		return "</table></body></html>";
	}
}
?>