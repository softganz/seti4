<h1>ImedPatientSearchWidget</h1>

<ul class="docs-page-nav-list">
<li>Contents</li>
<li>Before you begin</li>
<li><a href="{widget/widget}">Extends from Widget</a></li>
<li>Property</li>
<li>Method</li>
</ul>

<h2>Class constructor</h2>
<code>
new ImedPatientSearchWidget([
	String type,
	String linkUrl,
	String patientUrl,
	Int orgId,
	Boolean autoFocus,
	Boolean showAddPatientButton,
]);
</code>

<h2>Property :</h2>
<code>
	type 				↔ String default is search value search, link
	linkUrl			↔ String
	patientUrl	↔ String
	refApp			↔ String
	orgId				↔ Int
	autoFocus		↔ Boolean default is true
	showAddPatientButton ↔ Boolean default is true
</code>

<h2>Method :</h2>
<code>
AppBar::build() → Widget
</code>

<h2>Dependency</h2>
<ul>
	<li>Class <a href="{module/imed/widget/patient-add-form}">ImedPatientAddFormWidget()</a></li>
</ul>




# ImedPatientSearchWidget

Patient Search Widget for iMed Module

**Author:** Little Bear <softganz@gmail.com>  
**Created:** 2022-05-10  
**Modified:** 2026-04-24  
**Version:** 7

---

## Overview

`ImedPatientSearchWidget` is a reusable widget component that provides a patient search interface with autocomplete functionality. It allows users to search for patients by name, surname, or 13-digit ID. The widget supports adding new patients on-the-fly and can be integrated into various views within the iMed application.

---

## Features

- **Two Display Modes:**
  - `search` - Full form-based search interface (default)
  - `link` - Simplified link-style interface

- **Patient Search:**
  - Search by first name, surname, or 13-digit ID
  - Real-time autocomplete with customizable query endpoint
  - Pagination support for large result sets

- **Patient Management:**
  - Optional button to add new patients directly from the search widget
  - Seamless integration with patient add form
  - Support for adding patients to specific organizations

- **Mobile Support:**
  - Android WebView compatibility
  - Auto-focus option for better UX

- **Customizable:**
  - Configurable URL redirects after patient selection
  - Organization filtering support
  - Customizable UI appearance through Card and Form classes

---

## Installation & Usage

### Basic Usage

```php
<?php
// Basic search widget
$widget = new ImedPatientSearchWidget([]);
echo $widget->build();
?>
```

### Advanced Usage

```php
<?php
$widget = new ImedPatientSearchWidget([
    'type' => 'search',                           // Display type: 'search' or 'link'
    'patientUrl' => 'imed/patient/{{psnId}}',    // URL template after patient selection
    'orgId' => 123,                               // Organization ID for filtering
    'refApp' => 'app-name',                       // Reference app identifier
    'autoFocus' => true,                          // Auto-focus search input
    'showAddPatientButton' => true,               // Show "Add Patient" button
]);

echo $widget->build();
?>
```

---

## Configuration Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `type` | string | `'search'` | Widget display type: `'search'` (form) or `'link'` (clickable link) |
| `patientUrl` | string | `'imed/patient/{{psnId}}'` | URL template for patient detail page. Use `{{psnId}}` as placeholder for patient ID |
| `linkUrl` | string | `NULL` | Link target for `'link'` type widget |
| `refApp` | string | `NULL` | Reference app identifier passed to the patient add form |
| `orgId` | int/string | `NULL` | Organization ID for organization-specific filtering |
| `autoFocus` | boolean | `true` | Auto-focus the search input on page load |
| `showAddPatientButton` | boolean | `true` | Show button to add new patients |

---

## Display Modes

### 1. Search Mode (Default)

Full search interface with form input, autocomplete results, and optional add patient button.

```php
$widget = new ImedPatientSearchWidget([
    'type' => 'search',
    'orgId' => $organizationId,
]);
echo $widget->build();
```

**Features:**
- Text input for search query
- Optional checkbox to search within organization
- Autocomplete dropdown with patient results
- Add patient button (if enabled)
- Embedded add patient form (hidden by default)

### 2. Link Mode

Compact link-style interface for integration into action menus.

```php
$widget = new ImedPatientSearchWidget([
    'type' => 'link',
    'linkUrl' => 'imed/app/search',
]);
echo $widget->build();
```

**Features:**
- Simple clickable link
- WebView integration support
- Mobile-friendly menu support

---

## Search Functionality

### Autocomplete Query Endpoint

The widget queries an API endpoint as the user types. The default endpoint is:

```
/api/imed/person/search
```

**Request Parameters:**
- `q` - Search query (name, surname, or ID)
- `p` - Page number (for pagination)

**Expected Response Format:**

```json
[
  {
    "value": "9999999999999",           // Patient ID (13-digit)
    "label": "Surname",                 // Patient surname
    "prename": "Mr./Mrs.",              // Patient title/prefix
    "desc": "Additional info"           // Additional description
  },
  {
    "value": "...",                     // Pagination indicator
    "nextpage": 2                       // Next page number
  }
]
```

---

## URL Parameters

### Patient URL Template

Use placeholder `{{psnId}}` in patient URLs:

```php
'patientUrl' => 'imed/patient/{{psnId}}'
'patientUrl' => 'imed/app/patient/view/{{psnId}}'
'patientUrl' => 'module/submodule/patient/{{psnId}}?ref=app'
```

The placeholder is replaced with the actual patient ID on result selection.

---

## Patient Addition Flow

When users click the "Add Patient" button:

1. **Desktop:** The embedded `ImedPatientAddFormWidget` is revealed
2. **Android:** A WebView opens to the patient add page (`imed/patient/add`)
3. After successful addition, the application:
   - Reloads with the new patient's detail page
   - Uses the configured `patientUrl` template
   - Passes the new patient ID

### Configuration for Patient Addition

```php
$widget = new ImedPatientSearchWidget([
    'showAddPatientButton' => true,     // Enable add button
    'orgId' => 5,                       // Add patient to this organization
    'refApp' => 'myapp',                // Reference app for tracking
]);
```

---

## Organization Filtering

When `orgId` is specified, a checkbox appears: **"ค้นหาในกลุ่ม"** (Search within organization)

```php
$widget = new ImedPatientSearchWidget([
    'orgId' => $organizationId,  // Enable org-specific filtering
]);
```

---

## Mobile/WebView Support

The widget includes Android WebView support for mobile applications:

```javascript
// Automatically handled - no configuration needed
// If Android API is available, opens WebView for patient addition
if (typeof Android == "object") {
    Android.showWebView(location, androidData);
}
```

---

## JavaScript Integration

### Module Load

The widget automatically initializes a JavaScript module from:

```
/imed/js/imed.patient.search.js
```

**Initialization Parameters:**
- `form` - Form selector
- `orgId` - Organization ID
- `queryUrl` - Autocomplete endpoint URL
- `patientUrl` - Patient detail page URL

---

## Styling

The widget uses the following CSS classes:

| Class | Purpose |
|-------|---------|
| `.imed-search-patient` | Main widget container (Card) |
| `.chat-box.-imed-app-home` | Chat-style container |
| `.-person-search` | Search form |
| `#patient-list` | Results container |
| `#patient-name` | Search input |
| `.ui-item` | Individual result item |
| `.-get-more` | Pagination button |

---

## Examples

### Example 1: Basic Patient Search in Admin View

```php
<?php
$searchWidget = new ImedPatientSearchWidget([
    'patientUrl' => 'imed/admin/patient/{{psnId}}',
]);
echo $searchWidget->build();
?>
```

### Example 2: Organization-Scoped Search

```php
<?php
$orgId = 42;
$searchWidget = new ImedPatientSearchWidget([
    'patientUrl' => 'imed/group/patient/{{psnId}}',
    'orgId' => $orgId,
    'autoFocus' => true,
]);
echo $searchWidget->build();
?>
```

### Example 3: Link Mode for Navigation Menu

```php
<?php
$searchWidget = new ImedPatientSearchWidget([
    'type' => 'link',
    'linkUrl' => 'imed/app/search',
]);
echo $searchWidget->build();
?>
```

### Example 4: Minimal Configuration

```php
<?php
// Uses all defaults
$searchWidget = new ImedPatientSearchWidget();
echo $searchWidget->build();
?>
```

---

## Class Structure

### Properties

```php
class ImedPatientSearchWidget extends Widget {
    var $type = 'search';           // Display type
    var $linkUrl;                   // Link for 'link' type
    var $patientUrl;                // URL template for patient pages
    var $refApp;                    // Reference app identifier
    var $orgId;                     // Organization ID
    var $autoFocus = true;          // Auto-focus search input
    var $showAddPatientButton = true; // Show add button
}
```

### Methods

| Method | Access | Description |
|--------|--------|-------------|
| `__construct($args)` | public | Initialize widget with configuration array |
| `build()` | public | Generate and return widget HTML/structure |
| `searchTypeForm()` | private | Build search form interface |
| `searchTypeLink()` | private | Build link interface |
| `_script()` | private | Generate JavaScript initialization code |

---

## Dependencies

- **Base Class:** `Widget` - Core widget framework
- **Components:** 
  - `Card` - Layout wrapper
  - `Container` - Content container
  - `Form` - Search form element
  - `ImedPatientAddFormWidget` - Patient addition form
- **External:**
  - jQuery 3.5+ or 3.7+
  - Bootstrap/Selectize for autocomplete
  - `/imed/js/imed.patient.search.js` - Client-side module

---

## API Integration

### Query Endpoint

**URL:** `/api/imed/person/search`

**Method:** POST

**Parameters:**
```
q = search query
p = page number
```

**Response:**
```json
[
  {
    "value": "patient_id",
    "label": "surname",
    "prename": "title",
    "desc": "description",
    "nextpage": 2
  }
]
```

---

## Tips & Best Practices

1. **URL Templates:** Always use `{{psnId}}` placeholder for patient URLs
2. **Organization Context:** Set `orgId` when widget operates within organizational scope
3. **Auto-Focus:** Set `autoFocus: false` when widget is not the primary page element
4. **Add Patient Button:** Disable if your use case doesn't allow adding new patients
5. **Mobile Testing:** Test WebView functionality on actual Android devices
6. **Error Handling:** Ensure query endpoint returns proper JSON responses

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 7 | 2026-04-24 | Current version |
| ... | ... | Previous versions |

---

## Support

For issues, feature requests, or documentation updates, contact:  
**Little Bear** <softganz@gmail.com>
