# Class InlineEdit Widget

## Overview
**File:** `sgui.js`  
**Author:** Little Bear `<softganz@gmail.com>`  
**Version:** 67  
**Last Modified:** 2026-06-11 
**Created:** 2021-12-24

The `sgui` is a JavaScript library.

---

<a id="table-of-contents"></a>
## Table of Contents
- Before you begin
- [Extends from Widget]({widget/widget})
- [Property](#Property)
- [Method](#Method)

### Methods Overview
1. [**isOpen($projectId)**](#method-is-open) - Check project status is open

### Additional Sections
- [Database Tables Referenced](#database-tables-referenced)

---

## Methods

<a id="method-1-info"></a>

## Class constructor

```php
new InlineEdit([
	String id,
	String class,
	Boolean editMode,
	String action,
	Boolean useParentEditClass,
	Array child,
	Array of Array children,
]);
```

<a name="Property"></a>

## Property

```php
Child and children element

All input type property
[
	'editMode' : Boolean default false
	'type' : 'label,text,textarea,datepicker,radio,checkbox,select,textfield,method,comment' Default is text
	'inputName' : String
	'label' : String
	'value': Mixed
	'ret': String
	'convert': String
	'variable1' : Mixed
	'variable2' : Mixed
	'options' : Array
		key:
			debug: Boolean default false
			placeholder: String
			done: String callback,load,close,back
]

Additionall for type radio,checkbox,select:
[
	'choices' => ['value' => Text, ...],
]
```

```
	editMode ↔ Boolean
	action ↔ String
```

```
type ↔ method
new InlineEdit([
	'child' => [
		'type' => 'method',
		'label' => 'Label Name',
		'method' => $this->methodName(),
	]
])
action ↔ String
```

<a name="Method"></a>

## Method
```
Inlineedit::build() → String
```
