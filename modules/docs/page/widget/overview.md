# Widget System Documentation

> **Version:** 79 (2026-07-25)
> **Author:** Little Bear <softganz@gmail.com>
> **File:** `core/widget/class.widget.php`

---

## สารบัญ

1. [ภาพรวมระบบ](#1-ภาพรวมระบบ)
2. [Class Hierarchy](#2-class-hierarchy)
3. [Render Pipeline](#3-render-pipeline)
4. [Widget Base Classes](#4-widget-base-classes)
5. [Layout Widgets](#5-layout-widgets)
6. [Navigation Widgets](#6-navigation-widgets)
7. [Action Widgets](#7-action-widgets)
8. [Display Widgets](#8-display-widgets)
9. [Page System](#9-page-system)
10. [Utility Widgets](#10-utility-widgets)
11. [Container & Children System](#11-container--children-system)
12. [Special Children Tokens](#12-special-children-tokens)
13. [Best Practices](#13-best-practices)
14. [Appendix: Property Reference](#14-appendix-property-reference)

---

## 1. ภาพรวมระบบ

Widget System เป็น **UI Component Framework** แบบ Tree-based สำหรับ Seti CMS ออกแบบโดยได้รับแรงบันดาลใจจาก Flutter/widget-based architecture

### แนวคิดหลัก

| แนวคิด | คำอธิบาย |
|--------|----------|
| **Widget Tree** | Widgets สามารถซ้อนกันเป็นลำดับชั้นผ่าน `children` / `child` |
| **Declarative** | ประกาศ UI structure ผ่าน constructor arguments |
| **Render Pipeline** | กระบวนการ render เป็นขั้นตอน: `build()` → `toString()` → methods ย่อย |
| **Override Points** | Method ที่ขึ้นต้นด้วย `_render` สามารถ override เพื่อปรับแต่งการแสดงผล |
| **Config System** | แต่ละ Widget มี `$config` object สำหรับเก็บ attr/data/header |

### ตัวอย่างการใช้งานพื้นฐาน

```php
// สร้าง Container ที่มี Column และ Button
new Container([
    'children' => [
        new Header(['title' => 'Hello World']),
        new Button([
            'text' => 'Click Me',
            'type' => 'primary',
            'icon' => '<i class="icon -material">check</i>',
            'href' => '/page',
        ]),
    ],
]);
```

---

## 2. Class Hierarchy

```
WidgetBase                              ← Base class สูงสุด
├── Children                            ← กลุ่ม children สำหรับ nested layout
├── Message                             ← แสดงข้อความ
│   └── ErrorMessage                    ← Error โดยเฉพาะ
└── Widget                              ← Core widget (หลักของระบบ)
    │
    ├── [Layout Widgets]
    │   ├── Container                   ← <div> container ทั่วไป
    │   ├── Center                      ← จัดกึ่งกลาง
    │   ├── Column                      ← Flex column
    │   ├── Row                         ← Flex row
    │   ├── ListOrder                   ← <ul>/<ol> list
    │   ├── Stack                       ← (placeholder)
    │   ├── GridView                    ← (placeholder)
    │   ├── ScrollView                  ← Scroll container
    │   └── ListTile                    ← List item (leading + title + trailing)
    │
    ├── [Navigation Widgets]
    │   ├── Nav                         ← Navigation menu
    │   ├── SideBar                     ← Sidebar (<aside>)
    │   ├── TabBar                      ← Tab interface
    │   ├── StepMenu                    ← Step/stepper
    │   ├── AppBar                      ← Top app bar
    │   └── Scaffold                    ← Page scaffold
    │
    ├── [Action Widgets]
    │   ├── Button                      ← ปุ่ม (<a>)
    │   ├── BackButton                  ← ปุ่มย้อนกลับ
    │   ├── ExpandButton                ← ปุ่ม expand/collapse
    │   └── FloatingActionButton        ← FAB
    │
    ├── [Display Widgets]
    │   ├── Card                        ← Card component
    │   ├── Header                      ← Header component
    │   ├── Icon                        ← Material Icons
    │   ├── ProfilePhoto                ← รูปโปรไฟล์
    │   ├── Notify                      ← Notification
    │   ├── HtmlTemplate                ← <template> tag
    │   └── DOM                         ← HTML tag อิสระ
    │
    ├── [Page System]
    │   ├── PageBase                    ← Base สำหรับ page
    │   │   ├── Page                    ← หน้าเว็บทั่วไป
    │   │   ├── PageApi                 ← API endpoint
    │   │   └── PageController          ← Page router
    │   └── ListItem                    ← รายการแบบมี wrapper
    │
    └── [Utility]
        └── DebugMsg                    ← Debug output
```

---

## 3. Render Pipeline

การ render Widget ทำงานเป็นลำดับขั้นตอนดังนี้:

```
build()
  │
  ├── เรียก onBuild callback (ถ้ามี)
  │
  └── toString()
        │
        ├── _renderWidgetContainerStart()
        │     └── สร้าง opening tag + id + class + data-* + style + attributes
        │
        ├── header (ถ้ามี)
        │
        ├── _renderChildren(children())
        │     ├── _renderChildrenContainerStart()
        │     ├── [loop children]
        │     │     ├── _renderChildContainerStart()
        │     │     ├── _renderEachChildWidget()
        │     │     └── _renderChildContainerEnd()
        │     └── _renderChildrenContainerEnd()
        │
        └── _renderWidgetContainerEnd()
              └── สร้าง closing tag
```

### จุดที่ Override ได้

| Method | ไว้ใช้ทำอะไร |
|--------|-------------|
| `initWidget()` | กำหนดค่าเริ่มต้นเพิ่มเติม (เรียกใน constructor) |
| `_renderWidgetContainerStart()` | เปลี่ยน HTML opening tag ของ widget |
| `_renderWidgetContainerEnd()` | เปลี่ยน HTML closing tag |
| `_renderChildrenContainerStart()` | เปิด container ที่ครอบ children ทั้งหมด |
| `_renderChildrenContainerEnd()` | ปิด container ที่ครอบ children ทั้งหมด |
| `_renderChildContainerStart()` | เปิด container สำหรับ child แต่ละตัว |
| `_renderChildContainerEnd()` | ปิด container สำหรับ child แต่ละตัว |
| `_renderEachChildWidget()` | เปลี่ยนวิธี render child แต่ละตัว |
| `_renderChildren()` | เปลี่ยนวิธี render children ทั้งหมด |
| `toString()` | เปลี่ยนโครงสร้างการ render ทั้งหมด |
| `build()` | จุดเริ่มต้น — เปลี่ยน flow การทำงาน |

---

## 4. Widget Base Classes

### 4.1 WidgetBase

Base class สูงสุดของระบบทั้งหมด

```php
class WidgetBase {
    public $widgetName = 'Widget';
    public $version;

    function __construct($args = [])
    function extension()
    protected function valid($value, $regx, $debug = false)
}
```

**Properties:**

| Property | Type | Default | คำอธิบาย |
|----------|------|---------|----------|
| `$widgetName` | string | `'Widget'` | ชื่อ widget (ใช้สร้าง CSS class) |
| `$version` | string | — | เวอร์ชันของ widget |

**Methods:**

| Method | คำอธิบาย |
|--------|----------|
| `__construct($args)` | รับ associative array แล้ว assign เป็น properties |
| `extension()` | จุดสำหรับ extension hook |
| `valid($value, $regx)` | ตรวจสอบค่าด้วย regex ผ่าน `\SG\valid()` |

### 4.2 Widget

Core class — เป็น base ให้ widget ส่วนใหญ่

```php
class Widget extends WidgetBase {
    public $widgetName = 'Widget';
    public $version;
    public $tagName = '';
    public $childTagName;
    public $id;
    public $class;
    public $header;
    public $itemClass;
    public $mainAxisAlignment;
    public $crossAxisAlignment;
    public $href;
    public $dataUrl;
    public $webview;
    public $style;
    public $onBuild;
    public $rel;
    public $done;
    public $action;
    public $debug;
    public $child;
    public $children = [];
    public $attribute = [];
    public $childContainer = [];
    public $config = null;
}
```

**Properties ที่สำคัญ:**

| Property | Type | คำอธิบาย |
|----------|------|----------|
| `$tagName` | string | HTML tag ที่ใช้ห่อ widget (ถ้าว่าง = ไม่มี container) |
| `$childTagName` | string | HTML tag สำหรับ child แต่ละตัว |
| `$id` | string | HTML id |
| `$class` | string | CSS class เพิ่มเติม |
| `$header` | string/object/Widget | ส่วนหัว |
| `$itemClass` | string | CSS class สำหรับ child แต่ละตัว |
| `$mainAxisAlignment` | string | `'start'`, `'center'`, `'end'` |
| `$crossAxisAlignment` | string | `'start'`, `'center'`, `'end'` |
| `$href` | string | ลิงก์ |
| `$dataUrl` | string | `data-url` attribute |
| `$webview` | string | `data-webview` attribute |
| `$style` | string | inline CSS |
| `$onBuild` | callable | callback ก่อน render |
| `$rel` | string | `data-rel` attribute |
| `$done` | string | `data-done` attribute |
| `$child` | any | child ตัวเดียว (auto-add to `$children[]`) |
| `$children` | array | array ของ children |
| `$attribute` | array | custom HTML attributes |
| `$childContainer` | array | กำหนด tagName/class สำหรับ child container |
| `$config` | object | internal config (`attr`, `data`, `header`) |

**Methods:**

| Method | Signature | คำอธิบาย |
|--------|-----------|----------|
| `initConfig()` | `void` | เริ่มต้น `$config` object |
| `addClass($class)` | `void` | เพิ่ม CSS class |
| `addId($id)` | `void` | กำหนด id |
| `addConfig($key, $value)` | `void` | เพิ่มค่าใน config |
| `addAttr($key, $value)` | `void` | เพิ่ม HTML attribute |
| `addData($key, $value)` | `void` | เพิ่ม data-* attribute |
| `config($key, $value)` | `mixed` | getter/setter สำหรับ config |
| `attr($key, $value)` | `mixed` | getter/setter สำหรับ attributes |
| `data($key, $value)` | `mixed` | getter/setter สำหรับ data-* |
| `header($str, $attr, $options)` | `void` | กำหนด header |
| `children($value)` | `array` | getter/setter สำหรับ children |
| `build()` | `string` | จุดเริ่มต้น render |
| `toString()` | `string` | render widget เป็น HTML |
| `_renderWidgetContainerStart()` | `string` | render opening tag |
| `_renderWidgetContainerEnd()` | `string` | render closing tag |
| `_renderChildrenContainerStart()` | `string` | render children container opening |
| `_renderChildrenContainerEnd()` | `string` | render children container closing |
| `_renderChildContainerStart($key, $attr, $child)` | `string` | render child container opening |
| `_renderChildContainerEnd($child, $key)` | `string` | render child container closing |
| `_renderEachChildWidget($widget, $key, $callback, $options)` | `string` | render child แต่ละตัว |
| `_renderChildren($childrens, $args)` | `string` | render children ทั้งหมด |

### 4.3 Children

ใช้สำหรับจัดกลุ่ม children ที่มี container ของตัวเอง

```php
class Children extends WidgetBase {
    public $type;
    public $children = [];
}
```

**ตัวอย่าง:**

```php
new Column([
    'children' => [
        new Children([
            'tagName' => 'fieldset',
            'class' => '-group',
            'children' => [
                new Button(['text' => 'Save']),
                new Button(['text' => 'Cancel']),
            ],
        ]),
    ],
]);
```

---

## 5. Layout Widgets

### 5.1 Container

`<div>` container ทั่วไป

```php
new Container([
    'class' => '-my-style',
    'children' => [...],
]);
```

### 5.2 Center

จัดเนื้อหากึ่งกลาง (เพิ่ม class `-sg-text-center`)

```php
new Center([
    'child' => new Button(['text' => 'Centered Button']),
]);
```

### 5.3 Column

จัดเรียง children ในแนวตั้ง (flex column)

```php
new Column([
    'children' => [
        'Item 1',
        'Item 2',
        'Item 3',
    ],
]);
```

**HTML ที่ได้:**
```html
<div class="widget-column">
    <div class="-item">Item 1</div>
    <div class="-item">Item 2</div>
    <div class="-item">Item 3</div>
</div>
```

### 5.4 Row

จัดเรียง children ในแนวนอน (flex row)

```php
new Row([
    'children' => [
        new Button(['text' => 'A']),
        new Button(['text' => 'B']),
    ],
]);
```

### 5.5 ListOrder

สร้าง `<ul>` หรือ `<ol>` list

```php
new ListOrder([
    'type' => 'ol',  // 'ul' (default) หรือ 'ol'
    'children' => ['Item 1', 'Item 2'],
]);
```

### 5.6 ListTile

รายการที่มี leading icon, title, subtitle, trailing

```php
new ListTile([
    'leading' => new Icon('person'),
    'title' => 'John Doe',
    'subTitle' => 'Online',
    'trailing' => new Icon('chevron_right'),
]);
```

### 5.7 ScrollView

Container ที่สามารถ scroll ได้

```php
new ScrollView([
    'scrollDirection' => 'horizontal',  // หรือ 'vertical'
    'children' => [...],
]);
```

### 5.8 Stack / GridView

> **⚠️ Placeholder:** ยังไม่มี implementation

---

## 6. Navigation Widgets

### 6.1 Nav

Navigation menu — รองรับ single level และ multiple level

```php
// Single level
new Nav([
    'children' => [
        '<a href="/home">Home</a>',
        '<a href="/about">About</a>',
    ],
]);

// Multiple level (auto-detect)
new Nav([
    'children' => [
        ['label' => 'Menu 1', 'items' => [...]],
        ['label' => 'Menu 2', 'items' => [...]],
    ],
]);
```

**Properties:**

| Property | Type | Default | คำอธิบาย |
|----------|------|---------|----------|
| `$type` | string | — | เพิ่ม class `-type-{type}` |
| `$direction` | string | — | เพิ่ม class `-{direction}` |
| `$multipleLevel` | bool | `false` | auto-detect ถ้า children เป็น array ซ้อน |

### 6.2 SideBar

Sidebar ใช้ `<aside>` tag

```php
new SideBar([
    'type' => 'left',  // เพิ่ม class `-type-left`
    'children' => [...],
]);
```

### 6.3 TabBar

Tab interface — ต้องระบุ `action` (ปุ่ม tab) และ `content` (เนื้อหา)

```php
new TabBar([
    'children' => [
        (object) [
            'action' => new Button(['text' => 'Tab 1', 'href' => '#tab1']),
            'content' => 'Content 1',
            'id' => 'tab1',
            'active' => true,
        ],
        (object) [
            'action' => new Button(['text' => 'Tab 2', 'href' => '#tab2']),
            'content' => 'Content 2',
            'id' => 'tab2',
        ],
    ],
]);
```

### 6.4 StepMenu

Stepper / ขั้นตอนการทำงาน

```php
new StepMenu([
    'currentStep' => 2,
    'activeStep' => [1 => true, 2 => true],
    'children' => ['Step 1', 'Step 2', 'Step 3'],
]);
```

### 6.5 AppBar

Top app bar — มี leading, title, subtitle, trailing, navigator, dropbox

```php
new AppBar([
    'title' => 'Dashboard',
    'subTitle' => 'Welcome back',
    'leading' => new BackButton(),
    'trailing' => new Button(['text' => 'Logout']),
    'navigator' => [
        '<a href="/">Home</a>',
        '<a href="/report">Report</a>',
    ],
]);
```

**Navigator Format:**

| รูปแบบ | ตัวอย่าง |
|--------|----------|
| String | `'<a href="/">Home</a>'` |
| Array | `['<a>1</a>', '<a>2</a>', widget, dropbox]` |
| Array of Array | `[['<a>1</a>','<a>2</a>'], ['<a>3</a>','<a>4</a>'], widget]` |
| Widget Object | `new Nav(['children' => [...]])` |

### 6.6 Scaffold

โครงสร้างหลักของหน้า — ประกอบด้วย AppBar, body, SideBar, FloatingActionButton

```php
new Scaffold([
    'appBar' => new AppBar(['title' => 'My Page']),
    'body' => new Column([
        'children' => ['Content here'],
    ]),
    'floatingActionButton' => new FloatingActionButton([
        'children' => [new Icon('add')],
    ]),
    'sideBar' => new SideBar([...]),
    'script' => '<script>console.log("loaded")</script>',
]);
```

---

## 7. Action Widgets

### 7.1 Button

ปุ่ม — สร้าง `<a>` tag พร้อม class และ attributes ต่างๆ

```php
new Button([
    'text' => 'Save',
    'type' => 'primary',       // default, primary, secondary, success, info, warning, danger, link, cancel, floating
    'icon' => '<i class="icon -material">save</i>',
    'iconPosition' => 'left',  // left, right
    'href' => '/save',
    'rel' => 'ajax',           // data-rel
    'before' => 'confirm()',   // data-before
    'done' => 'reload()',      // data-done
    'boxType' => 'large',      // เปิดใน box modal
    'boxWidth' => '800',
    'boxHeight' => '600',
    'access' => 'RIGHT_ADMIN', // ตรวจสอบสิทธิ์
    'description' => 'Save all changes',
    'onClick' => 'alert("clicked")',
    'target' => '_blank',
]);
```

**CSS Class ที่ได้:**
```
widget-button btn -primary -icon-right my-custom-class
```

### 7.2 BackButton

ปุ่มย้อนกลับ — `href` default คือ `javascript:history.back()`

```php
new BackButton([
    'text' => 'Back',
]);
```

### 7.3 ExpandButton

ปุ่ม expand/collapse — ใช้ icon `chevron_right` (default)

```php
new ExpandButton([
    'icon' => 'expand_more',
]);
```

### 7.4 FloatingActionButton

ปุ่มลอย (FAB) — `tagName` = `div`

```php
new FloatingActionButton([
    'children' => [new Icon('add')],
]);
```

---

## 8. Display Widgets

### 8.1 Card

Card component — มี header (leading, title, subtitle, trailing) และ children

```php
new Card([
    'header' => [
        'leading' => new Icon('star'),
        'title' => 'Card Title',
        'subtitle' => 'Subtitle',
        'trailing' => new Icon('more_vert'),
    ],
    'children' => ['Card content here'],
]);
```

### 8.2 Header

Header component — คล้าย ListTile แต่ใช้ `<header>` tag

```php
new Header([
    'title' => 'Section Title',
    'subTitle' => 'Description',
    'leading' => new Icon('info'),
    'trailing' => new Button(['text' => 'View All']),
]);
```

### 8.3 Icon

Material Icons — รองรับ single, double (comma-separated), และ secondary icon

```php
// Single icon
new Icon('home');

// Two icons
new Icon('check,close');

// With secondary element
new Icon('person', [
    'secondary' => '<span class="badge">3</span>',
]);
```

### 8.4 ProfilePhoto

รูปโปรไฟล์ — ใช้ `UserModel::profilePhoto()` หา URL รูป

```php
new ProfilePhoto('username', [
    'size' => 'small',  // small, big
    'title' => 'John Doe',
]);
```

### 8.5 Notify

Notification widget

```php
new Notify([
    'children' => ['New message received'],
]);
```

### 8.6 HtmlTemplate

`<template>` tag

```php
new HtmlTemplate([
    'children' => ['<div>Template content</div>'],
]);
```

### 8.7 DOM

สร้าง HTML tag อิสระ — รับ tag name เป็น argument แรก

```php
// <img> tag
new DOM(['img', 'src' => '/image.jpg', 'class' => '-round', 'onClick' => 'open()']);

// <video> tag
new DOM(['video', 'src' => '/video.mp4', 'controls' => 'controls', 'children' => ['Your browser does not support video.']]);
```

---

## 9. Page System

ระบบจัดการหน้าเว็บ — แบ่งเป็น 3 ประเภทตามการใช้งาน

### 9.1 PageBase

Base class สำหรับ page-level widgets — auto-detect module name จาก class name

```php
class PageBase extends WidgetBase {
    public $module;  // auto-detect: 'flood' จาก 'FloodPage'
}
```

### 9.2 Page

หน้าเว็บทั่วไป — สร้าง Scaffold พร้อม AppBar และ body

```php
class MyPage extends Page {
    function appBar() {
        return new AppBar(['title' => 'My Page']);
    }

    function body() {
        return new Column([
            'children' => ['Hello World'],
        ]);
    }
}

// เรียกใช้
new MyPage();
```

### 9.3 PageApi

API endpoint handler — map action name ไปยัง method

```php
class FloodApi extends PageApi {
    protected $actionDefault = 'list';

    // GET /api/flood/list
    function list() {
        return apiSuccess(['items' => [...]]);
    }

    // GET /api/flood/getDetail?id=1
    function getDetail() {
        $id = Request::all('id');
        return apiSuccess(['data' => [...]]);
    }

    // ตรวจสอบสิทธิ์
    function rightToBuild() {
        return user_access('access flood api');
    }
}
```

**การทำงาน:**
- Action `api.list` → method `list()`
- Action `api.getDetail` → method `getDetail()`
- Action ที่ขึ้นต้นด้วย `api.` → เรียก `runExternalMethod()` → `R::PageWidget()`
- ตรวจสอบ `rightToBuild()` ก่อน execute (ถ้ามี method นี้)

### 9.4 PageController

Page controller/router — forward action ไปยัง `R::PageWidget()`

```php
class FloodController extends PageController {
    // action = 'flood.view'
    // args = [url, module, action, ...]
}
```

### 9.5 ListItem

รายการแบบมี wrapper — รองรับหลาย wrapper type

```php
new ListItem([
    'tagName' => 'ul',  // ul → <li>, div → <div>, span → <span>
    'type' => 'action',
    'children' => [
        (object) ['text' => 'Item 1', 'options' => '{"class":"-active"}'],
        (object) ['text' => '-', 'options' => '{}'],  // separator
        (object) ['text' => 'Item 2', 'options' => '{}'],
    ],
]);
```

---

## 10. Utility Widgets

### 10.1 DebugMsg

แสดง debug message — แสดงเฉพาะเมื่อ user มีสิทธิ์ `access debugging program`

```php
// แสดง string
new DebugMsg('Hello World', '$varName');

// แสดง object/array
new DebugMsg($someObject, 'myObject');

// แสดง SQL query
new DebugMsg('SELECT * FROM users');

// แสดง call stack
new DebugMsg($value, '$value', debug_backtrace());
```

### 10.2 Message / ErrorMessage

แสดงข้อความ — รองรับ AJAX และ non-AJAX

```php
// Success message
new Message(['text' => 'Operation completed']);

// Error message
new Message(['errorMessage' => 'Something went wrong', 'responseCode' => 400]);

// ErrorMessage (alias)
new ErrorMessage(['errorMessage' => 'Access denied', 'responseCode' => 403]);
```

---

## 11. Container & Children System

### 11.1 Widget Container

Widget container คือ HTML tag ที่ห่อ widget ทั้งหมด ควบคุมโดย:

| Property | หน้าที่ |
|----------|---------|
| `$tagName` | HTML tag (ถ้าว่าง = ไม่มี container) |
| `$id` | HTML id |
| `$class` | CSS class (เพิ่มจาก `widget-{name}` auto) |
| `$style` | inline CSS |
| `$attribute` | custom attributes |
| `$mainAxisAlignment` | เพิ่ม class `-main-axis-{value}` |
| `$crossAxisAlignment` | เพิ่ม class `-cross-axis-{value}` |

### 11.2 Child Container

Child container คือ HTML tag ที่ห่อ child แต่ละตัว ควบคุมโดย:

| วิธี | รายละเอียด |
|-----|------------|
| `$childContainer` | array `['tagName' => 'div', 'class' => '-item']` |
| `$childTagName` | string — ใช้แทน `$childContainer['tagName']` |
| `$itemClass` | string — เพิ่ม class ให้ child แต่ละตัว |
| `$childrenContainer` | array — container ที่ครอบ children ทั้งหมด |

### 11.3 Container Inheritance

`_renderChildContainerStart()` รองรับการ merge attributes จากหลายแหล่ง:

```
$container (global)
  → $container['children'][$childKey] (per-child)
    → $attributes (argument)
```

---

## 12. Special Children Tokens

| Token | ความหมาย | HTML Output |
|-------|----------|-------------|
| `'<sep>'` | Separator | `<hr class="separator" size="0" />` |
| `'<spacer>'` | Spacer | `<div class="-spacer"></div>` (empty) |

---

## 13. Best Practices

### 13.1 การสร้าง Widget ใหม่

```php
class MyWidget extends Widget {
    public $widgetName = 'MyWidget';
    public $tagName = 'div';
    public $version = '0.01';

    function __construct($args = []) {
        parent::__construct($args);
    }

    // override render method ถ้าต้องการ
    function toString() {
        return $this->_renderWidgetContainerStart()
            . '<div class="-custom">' . $this->_renderChildren($this->children()) . '</div>'
            . $this->_renderWidgetContainerEnd();
    }
}
```

### 13.2 การใช้ onBuild Callback

```php
new Button([
    'text' => 'Dynamic Button',
    'onBuild' => function($widget) {
        if (!user_access('admin')) {
            $widget->class .= ' -hidden';
        }
    },
]);
```

### 13.3 การใช้ data-* Attributes

```php
// ผ่าน constructor
new Button([
    'data-action' => 'delete',
    'data-id' => $id,
]);

// ผ่าน method
$widget->addData('action', 'delete');
$widget->data('id', $id);
```

### 13.4 การตรวจสอบสิทธิ์ใน Button

```php
new Button([
    'text' => 'Admin Only',
    'access' => 'RIGHT_ADMIN',  // ต้องมี constant นี้
    'variable' => $userVariable, // ใช้ตรวจสอบ $variable->RIGHT
]);
```

---

## 14. Appendix: Property Reference

### 14.1 CSS Class Naming Convention

| รูปแบบ | ตัวอย่าง | ที่มา |
|--------|----------|------|
| `widget-{name}` | `widget-button` | Auto จาก `$widgetName` |
| `-{type}` | `-primary` | จาก property `$type` |
| `-main-axis-{value}` | `-main-axis-center` | จาก `$mainAxisAlignment` |
| `-cross-axis-{value}` | `-cross-axis-end` | จาก `$crossAxisAlignment` |
| `-{childKey}` | `-home` | จาก key ของ child (ถ้าไม่ใช่ numeric) |

### 14.2 Data Attributes ที่ใช้ในระบบ

| Attribute | 用途 |
|-----------|------|
| `data-rel` | ความสัมพันธ์ (ajax, next, etc.) |
| `data-done` | callback หลังทำ action สำเร็จ |
| `data-url` | URL สำหรับโหลดข้อมูล |
| `data-webview` | URL สำหรับ webview |
| `data-options` | ตัวเลือกเพิ่มเติม (JSON) |
| `data-class-name` | ชื่อ class สำหรับ box/modal |
| `data-width` / `data-height` | ขนาด box |
| `data-before` | callback ก่อนทำ action |

### 14.3 Alignment Values

| Property | ค่าที่รองรับ |
|----------|-------------|
| `$mainAxisAlignment` | `start`, `center`, `end`, `space-between`, `space-around` |
| `$crossAxisAlignment` | `start`, `center`, `end`, `stretch` |

### 14.4 Button Types

| Type | CSS Class | ลักษณะ |
|------|-----------|--------|
| `default` | `widget-button` | ไม่มี class `btn` |
| `primary` | `widget-button btn -primary` | ปุ่มหลัก |
| `secondary` | `widget-button btn -secondary` | ปุ่มรอง |
| `success` | `widget-button btn -success` | success |
| `info` | `widget-button btn -info` | ข้อมูล |
| `warning` | `widget-button btn -warning` | คำเตือน |
| `danger` | `widget-button btn -danger` | อันตราย |
| `link` | `widget-button btn -link` | ลิงก์ |
| `cancel` | `widget-button btn -cancel` | ยกเลิก |
| `floating` | `widget-button btn -floating` | ปุ่มลอย |

---

> **Document Version:** 1.0 (Draft)
> **Last Updated:** 2026-07-28
> **Next Review:** การปรับปรุงครั้งใหญ่ (อาจมีการเปลี่ยนแปลงโครงสร้าง)