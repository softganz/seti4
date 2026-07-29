# Widget System Technical Documentation

> **File:** `core/widget/widget.systems.php`  
> **Version:** 82 (2026-07-29)  
> **Author:** Little Bear <softganz@gmail.com>  
> **Purpose:** Widget System Implementation

---

## 1. ภาพรวมระบบ

`widget.systems.php` เป็น **Widget System Implementation** สำหรับ Seti CMS Framework — เป็นระบบ UI Component แบบ Tree-based ที่ออกแบบมาให้คล้ายกับ Flutter/widget-based architecture โดยมี Widget เป็น building block หลักในการสร้างหน้าเว็บ

### จุดประสงค์หลัก

1. **รองรับ Widget-based UI** — สร้าง UI ด้วย Tree-based components
2. **Render Pipeline** — กระบวนการ render เป็นขั้นตอน: `build()` → `toString()` → methods ย่อย
3. **Override Points** — method names เปลี่ยน: `_render` → `render`
4. **Config System** — แต่ละ Widget มี `$config` object สำหรับเก็บ attr/data/header

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

### 3.1 Method Naming Convention

| Method | คำอธิบาย |
|--------|----------|
| `renderWidgetContainerStart()` | render opening tag ของ widget |
| `renderWidgetContainerEnd()` | render closing tag ของ widget |
| `renderChildrenContainerStart()` | render children container opening |
| `renderChildrenContainerEnd()` | render children container closing |
| `renderChildContainerStart()` | render child container opening |
| `renderChildContainerEnd()` | render child container closing |
| `renderEachChildWidget()` | render child แต่ละตัว |
| `renderChildren()` | render children ทั้งหมด |

### 3.2 Render Flow

```
build()
  │
  ├── เรียก onBuild callback (ถ้ามี)
  │
  └── toString()
        │
        ├── renderWidgetContainerStart()
        │     └── สร้าง opening tag + id + class + data-* + style + attributes
        │
        ├── header (ถ้ามี)
        │
        ├── renderChildren(children())
        │     ├── renderChildrenContainerStart()
        │     ├── [loop children]
        │     │     ├── renderChildContainerStart()
        │     │     ├── renderEachChildWidget()
        │     │     └── renderChildContainerEnd()
        │     └── renderChildrenContainerEnd()
        │
        └── renderWidgetContainerEnd()
              └── สร้าง closing tag
```

### 3.3 Override Points

| Method | ไว้ใช้ทำอะไร |
|--------|-------------|
| `initWidget()` | กำหนดค่าเริ่มต้นเพิ่มเติม (เรียกใน constructor) |
| `renderWidgetContainerStart()` | เปลี่ยน HTML opening tag ของ widget |
| `renderWidgetContainerEnd()` | เปลี่ยน HTML closing tag |
| `renderChildrenContainerStart()` | เปิด container ที่ครอบ children ทั้งหมด |
| `renderChildrenContainerEnd()` | ปิด container ที่ครอบ children ทั้งหมด |
| `renderChildContainerStart()` | เปิด container สำหรับ child แต่ละตัว |
| `renderChildContainerEnd()` | ปิด container สำหรับ child แต่ละตัว |
| `renderEachChildWidget()` | เปลี่ยนวิธี render child แต่ละตัว |
| `renderChildren()` | เปลี่ยนวิธี render children ทั้งหมด |
| `toString()` | เปลี่ยนโครงสร้างการ render ทั้งหมด |
| `build()` | จุดเริ่มต้น — เปลี่ยน flow การทำงาน |

---

## 4. Core Classes

### 4.1 WidgetBase

```php
class WidgetBase {
    public $widgetName = 'Widget';
    public $version;
    
    function __construct($args = []) {
        foreach ($args as $argKey => $argValue) {
            $this->{$argKey} = $argValue;
        }
    }
    
    function extension() {/* Not implement */}
    
    protected function valid($value, $regx, $debug = false) {
        return \SG\valid($value, $regx, $debug);
    }
}
```

**Properties:**
- `$widgetName` - ชื่อ widget (default: 'Widget')
- `$version` - เวอร์ชันของ widget

**Methods:**
- `__construct($args)` - รับ args แล้ว assign เป็น properties
- `extension()` - extension hook
- `valid($value, $regx)` - ตรวจสอบด้วย regex

### 4.2 Widget

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
    public $config = NULL;
    
    // ... constructor และ methods ...
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


### 4.3 DOM

```php
class DOM extends Widget {
    public $widgetName;
    public $version = '0.00.01';
    public $tagName;
    public $class;
    public $settings = [];
    
    function __construct($args = []) {
        $this->tagName = array_shift($args);
        $this->widgetName = 'dom-' . $this->tagName;
        $this->settings = (Array) $args['settings'];
        if ($args['children']) $this->children = $args['children'];
        else if ($args['child']) $this->children[] = $args['child'];
        $this->class = ($args['class'] ? $args['class'] : '');
        
        unset($args['tag'], $args['settings'], $args['child'], $args['children'], $args['class']);
        parent::__construct(['attribute' => $args]);
        
        if ($this->settings['debug']) debugMsg($this, '$this');
    }
}
```

**คุณสมบัติเฉพาะ:**
- รับ tag name เป็น argument แรก
- สร้าง widgetName เป็น `dom-{tagName}`
- มี `$settings` array สำหรับ configuration

### 4.4 Header

```php
class Header extends Widget {
    public $widgetName = 'Header';
    public $tagName = 'header';
    public $titleTag = 'span';
    public $leading;
    public $title;
    public $subTitle;
    public $trailing;
    
    // ... constructor และ toString() method ...
}
```

**คุณสมบัติเฉพาะ:**
- มี leading, title, subTitle, trailing สำหรับ header structure
- ใช้ `$titleTag` (default: 'span') สำหรับ title element

### 4.5 Container

```php
class Container extends Widget {
    public $widgetName = 'Container';
    public $tagName = 'div';
    public $fillButton = false;
    
    // ... constructor และ toString() method ...
}
```

**คุณสมบัติเฉพาะ:**
- `<div>` container ทั่วไป
- มี `$fillButton` flag (default: false)

### 4.6 Center

```php
class Center extends Widget {
    public $widgetName = 'Center';
    public $tagName = 'div';
    public $class = '-sg-text-center';
    
    // ... constructor ...
}
```

**คุณสมบัติเฉพาะ:**
- จัดเนื้อหากึ่งกลาง (เพิ่ม class `-sg-text-center` โดยอัตโนมัติ)

### 4.7 ListOrder

```php
class ListOrder extends Widget {
    public $widgetName = 'ListOrder';
    public $tagName = 'ul';
    public $childContainer = ['tagName' => 'li', 'class' => '-item'];
    
    // ... constructor ...
}
```

**คุณสมบัติเฉพาะ:**
- สร้าง `<ul>` list
- สามารถ override `$tagName` ด้วย `$type` property (default: 'ul', 'ol')

### 4.8 Column

```php
class Column extends Widget {
    public $widgetName = 'Column';
    public $tagName = 'div';
    public $childContainer = ['tagName' => 'div', 'class' => '-item'];
    
    // ... constructor ...
}
```

**คุณสมบัติเฉพาะ:**
- Flex column layout
- child container เป็น `<div class="-item">`

### 4.9 Row

```php
class Row extends Widget {
    public $widgetName = 'Row';
    public $version = '0.0.10';
    public $tagName = 'div';
    public $childContainer = ['tagName' => 'div', 'class' => '-item'];
}
```

**คุณสมบัติเฉพาะ:**
- Flex row layout
- child container เป็น `<div class="-item">`

### 4.10 FloatingActionButton

```php
class FloatingActionButton extends Widget {
    public $widgetName = 'FloatingActionButton';
    public $tagName = 'div';
    public $childContainer = ['tagName' => 'div', 'class' => '-item'];
}
```

**คุณสมบัติเฉพาะ:**
- Floating Action Button (FAB) widget
- child container เป็น `<div class="-item">`

### 4.11 ListTile

```php
class ListTile extends Widget {
    public $widgetName = 'ListTile';
    public $tagName = 'div';
    public $titleTag = 'span';
    public $leading;
    public $title;
    public $subTitle;
    public $trailing;
    
    // ... constructor และ toString() method ...
}
```

**คุณสมบัติเฉพาะ:**
- List item แบบมี leading icon, title, subtitle, trailing
- คล้าย Header แต่ใช้ `<div>` tag

---

## 5. Container & Children System

### 5.1 Widget Container

Widget container คือ HTML tag ที่ห่อ widget ทั้งหมด ควบคุมโดย:
- `$tagName` - HTML tag (ถ้าว่าง = ไม่มี container)
- `$id` - HTML id
- `$class` - CSS class (เพิ่มจาก `widget-{name}` auto)
- `$style` - inline CSS
- `$attribute` - custom attributes
- `$mainAxisAlignment` - เพิ่ม class `-main-axis-{value}`
- `$crossAxisAlignment` - เพิ่ม class `-cross-axis-{value}`

### 5.2 Child Container

Child container คือ HTML tag ที่ห่อ child แต่ละตัว ควบคุมโดย:
- `$childContainer` - array `['tagName' => 'div', 'class' => '-item']`
- `$childTagName` - string — ใช้แทน `$childContainer['tagName']`
- `$itemClass` - string — เพิ่ม class ให้ child แต่ละตัว
- `$childrenContainer` - array — container ที่ครอบ children ทั้งหมด

### 5.3 Container Inheritance

`renderChildContainerStart()` รองรับการ merge attributes จากหลายแหล่ง:
```
$container (global)
  → $container['children'][$childKey] (per-child)
    → $attributes (argument)
```

---

## 6. Special Children Tokens

| Token | ความหมาย | HTML Output |
|-------|----------|-------------|
| `'<sep>'` | Separator | `<hr class="separator" size="0" />` |
| `'<spacer>'` | Spacer | `<div class="-spacer"></div>` (empty) |

---

## 7. Best Practices

### 7.1 การสร้าง Widget ใหม่

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
        return $this->renderWidgetContainerStart()
            . '<div class="-custom">' . $this->renderChildren($this->children()) . '</div>'
            . $this->renderWidgetContainerEnd();
    }
}
```

### 7.2 การใช้ onBuild Callback

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

### 7.3 การใช้ data-* Attributes

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

### 7.4 การตรวจสอบสิทธิ์ใน Button

```php
new Button([
    'text' => 'Admin Only',
    'access' => 'RIGHT_ADMIN',  // ต้องมี constant นี้
    'variable' => $userVariable, // ใช้ตรวจสอบ $variable->RIGHT
]);
```

---

## 8. Appendix: Property Reference

### 8.1 CSS Class Naming Convention

| รูปแบบ | ตัวอย่าง | ที่มา |
|--------|----------|------|
| `widget-{name}` | `widget-button` | Auto จาก `$widgetName` |
| `-{type}` | `-primary` | จาก property `$type` |
| `-main-axis-{value}` | `-main-axis-center` | จาก `$mainAxisAlignment` |
| `-cross-axis-{value}` | `-cross-axis-end` | จาก `$crossAxisAlignment` |
| `-{childKey}` | `-home` | จาก key ของ child (ถ้าไม่ใช่ numeric) |

### 8.2 Data Attributes ที่ใช้ในระบบ

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

### 8.3 Alignment Values

| Property | ค่าที่รองรับ |
|----------|-------------|
| `$mainAxisAlignment` | `start`, `center`, `end`, `space-between`, `space-around` |
| `$crossAxisAlignment` | `start`, `center`, `end`, `stretch` |

### 8.4 Button Types

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
> **Last Updated:** 2026-07-29  
> **Next Review:** การปรับปรุงครั้งใหญ่ (อาจมีการเปลี่ยนแปลงโครงสร้าง)