<?php
/**
* SOFTGANZ :: Class editor
*
* Copyright (c) 2000-2006 The SoftGanz Group By Panumas Nontapan
* Authors : Panumas Nontapan <webmaster@softganz.com>
*             : http://www.softganz.com/
* ============================================
* This program is free software. You can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License.
* ============================================
*/
/**
--- Created 2006-12-12
--- Modify   2006-12-12
*/

class editor {
	public static function softganz_editor($id) {
		$ret .= '
	<div id="'.$id.'-control" class="editor" title="'.$id.'">
	<img title="Bold" alt="Bold" src="'._img.'richtext/bold.gif" onclick="editor.insert(\'**\',\'**\')" />
	<img title="Italic" alt="Italic" src="'._img.'richtext/italic.gif" onclick="editor.insert(\'[i]\',\'[/i]\')" />
	<img title="Underline" alt="Underline" src="'._img.'richtext/underline.gif" onclick="editor.insert(\'[u]\',\'[/u]\')" />
	<img title="Left" alt="Left" src="'._img.'richtext/left.gif" onclick="editor.insert(\'[left]\',\'[/left]\')" />
	<img title="Center" alt="Center" src="'._img.'richtext/center.gif" onclick="editor.insert(\'[center]\',\'[/center]\')" />
	<img title="Right" alt="Right" src="'._img.'richtext/right.gif" onclick="editor.insert(\'[right]\',\'[/right]\')" />

	<img title="Ordered List" alt="Ordered List" src="'._img.'richtext/ordlist.gif" onclick="editor.insert(\'1. \')" />
	<img title="Bulleted List" alt="Bulleted List" src="'._img.'richtext/bullist.gif" onclick="editor.insert(\'* \')" />
	<img title="Horizontal Rule" alt="Horizontal Rule" src="'._img.'richtext/rule.gif" onclick="editor.insert(\'[hr]\')" />

	<img title="Insert Page break or split summary at cursor" alt="Page break " src="'._img.'richtext/pagebreak.png" onclick="editor.insert(\'<!--break-->\')" />
	<img title="Hyperlink" alt="Hyperlink" src="'._img.'richtext/link.gif" onclick="editor.url()" />
	'.(user_access('upload photo')?'<img title="Image" alt="Image : member only" src="'._img.'richtext/image.gif" onclick="editor.image()" />':'').'
	<img title="Text Color" alt="Text Color" src="'._img.'richtext/forecol.gif" onclick="editor.color(\''.$id.'-control-color\')" />
	<img title=":)" alt=":)" src="'._img.'emotions/emotions.gif" onclick="editor.emotion(\''.$id.'-control-emotion\')" />
	<img title="Quote" alt="Quote" src="'._img.'richtext/quote.gif" onclick="editor.insert(\'[quote]\',\'[/quote]\')" />
	</div>

	<div id="'.$id.'-control-emotion" class="editor" title="'.$id.'" style="display:none;">
	<img title=":)" alt=":)" src="'._img.'emotions/smiley-smile.gif" onclick="editor.insert(\' :) \')" />
	<img title=":d" alt=":d" src="'._img.'emotions/smiley-laughing.gif" onclick="editor.insert(\' :d \')" />
	<img title=";)" alt=";)" src="'._img.'emotions/smiley-wink.gif" onclick="editor.insert(\' ;) \')" />
	<img title="+)" alt="+)" src="'._img.'emotions/smiley-good.gif" onclick="editor.insert(\' +) \')" />
	<img title="8)" alt="8)" src="'._img.'funny/rolleyes.gif" onclick="editor.insert(\' 8) \')" />
	<img title=":p" alt=":p" src="'._img.'emotions/smiley-tongue-out.gif" onclick="editor.insert(\' :p \')" />
	<img title=":s" alt=":s" src="'._img.'emotions/smiley-undecided.gif" onclick="editor.insert(\' :s \')" />
	<img title=":|" alt=":|" src="'._img.'emotions/smiley-frown.gif" onclick="editor.insert(\' :| \')" />
	<img title=":@" alt=":@" src="'._img.'emotions/smiley-sealed.gif" onclick="editor.insert(\' :@ \')" />
	<img title=":o" alt=":o" src="'._img.'emotions/smiley-surprised.gif" onclick="editor.insert(\' :o \')" />
	<img title="#)" alt="#)" src="'._img.'emotions/smiley-cool.gif" onclick="editor.insert(\' #) \')" />
	<img title="bullet" alt="bullet" src="'._img.'funny/b1.gif" onclick="editor.insert(\'[f:b1]\')" />
	<img title="bullet" alt="bullet" src="'._img.'funny/b2.gif" onclick="editor.insert(\'[f:b2]\')" />
	<img title="bullet" alt="bullet" src="'._img.'funny/b3.gif" onclick="editor.insert(\'[f:b3]\')" />
	</div>
	<div id="'.$id.'-control-color" class="editor" style="display:none;" title="'.$id.'"></div>';
		return $ret;
	}
}
?>
