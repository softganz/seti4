#ImedPatientAddFormWidget

<ul class="docs-page-nav-list">
<li>Contents</li>
<li>Before you begin</li>
<li><a href="{widget/widget}">Extends from Widget</a></li>
<li>Property</li>
<li>Method</li>
</ul>

##Class constructor
<code>
new ImedPatientAddFormWidget([
	String action,
	String class,
	String rel,
	String done,
	Int addToOrg,
	String fullName,
	String cid,
	String script
]);
</code>

##Property :
<code>
	action		↔ Url String default is api/imed/patient/create
	class			↔ String default is sg-form
	rel				↔ String
	done			↔ String
	addToOrg	↔ Int
	fullName	↔ String
	cid				↔ String
	script		↔ String
</code>

##Method :
<code>
AppBar::build() → Widget
</code>

##Dependency
<ul>
	<li>Class <a href="{widget/form}">Form()</a></li>
</ul>

# ImedPatientAddFormWidget

Patient Addition Form Widget for iMed Module

**Author:** Little Bear <softganz@gmail.com>  
**Created:** 2021-08-23  
**Modified:** 2026-04-23  
**Version:** 2

---

## Overview

`ImedPatientAddFormWidget` is a reusable form widget that enables users to add new patient records to the iMed system. The form collects essential patient information including demographics, identification, and address details. It integrates with the Thai administrative division system (Province/District/Subdistrict) and supports automated form submission and callbacks.

---

## Features

- **Comprehensive Patient Data Collection:**
  - Title/Prefix (คำนำหน้านาม)
  - Full Name (ชื่อ-นามสกุล)
  - 13-Digit ID (หมายเลขประจำตัวประชาชน)
  - Gender (เพศ)
  - Address with autocomplete
  - Geographic location (Province/District/Subdistrict)

- **Smart Address Handling:**
  - Autocomplete address input
  - Cascading dropdowns for Province → District → Subdistrict
  - Automatic population of geographic data
  - Support for Thai administrative divisions

- **Organizational Integration:**
  - Automatic assignment to organization
  - Hidden organization ID field
  - Group-based patient management

- **Form Processing:**
  - Client-side validation
  - AJAX submission
  - Configurable success callbacks
  - Custom action endpoints

- **Flexible Configuration:**
  - Custom action URLs
  - CSS class customization
  - Form relation settings
  - Success callback handling
  - Custom script injection

---

## Installation & Usage

### Basic Usage

```php
<?php
// Basic patient add form
$form = new ImedPatientAddFormWidget([]);
echo $form->build();
?>
```

### Advanced Usage

```php
<?php
$form = new ImedPatientAddFormWidget([
    'action' => 'api/custom/patient/create',      // Custom API endpoint
    'addToOrg' => 5,                              // Assign to organization
    'fullName' => 'Initial Name',                 // Pre-fill name
    'cid' => '1234567890123',                     // Pre-fill ID
    'rel' => '.patient-list',                     // Target element for results
    'done' => 'reload:imed/patient/{{psnId}}',       // Post-submit action
    'class' => 'sg-form custom-form',             // CSS classes
    'script' => '<script>console.log("Form loaded")</script>', // Custom JS
]);

echo $form->build();
?>
```

---

## Configuration Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `action` | string | `'api/imed/patient/create'` | API endpoint URL for form submission |
| `class` | string | `'sg-form'` | CSS classes applied to the form |
| `rel` | string | `NULL` | CSS selector for target element to populate with results |
| `done` | string | `NULL` | Action after successful submission: `'reload:url'`, `'redirect:url'`, `'refresh'`, or callback function |
| `addToOrg` | int/string | `NULL` | Organization ID to auto-assign new patient |
| `fullName` | string | `NULL` | Pre-fill full name field |
| `cid` | string | `NULL` | Pre-fill CID (13-digit ID) field |
| `script` | string | `NULL` | Custom JavaScript code to append to form |

---

## Form Fields

### 1. Title/Prefix (คำนำหน้านาม)
- **Type:** Text input
- **Required:** Yes
- **Max Length:** 20 characters
- **Placeholder:** "eg. นาย นาง"
- **Purpose:** Honorific prefix (Mr./Mrs./Ms./Dr. etc.)
- **Examples:** "นาย", "นาง", "นางสาว"

### 2. Full Name (ชื่อ - นามสกุล)
- **Type:** Text input
- **Required:** Yes
- **Max Length:** 100 characters
- **Placeholder:** "ชื่อ นามสกุล"
- **Purpose:** Patient's first and last name
- **Rules:** Must contain exactly one space between first and last name
- **Example:** "สมชาย ใจดี"

### 3. CID - ID Number (หมายเลขประจำตัวประชาชน 13 หลัก)
- **Type:** Text input
- **Required:** Yes
- **Max Length:** 13 characters
- **Placeholder:** "หมายเลข 13 หลัก"
- **Purpose:** National ID number
- **Notes:** Enter "?" if no ID is available
- **Example:** "1234567890123"

### 4. Gender (เพศ)
- **Type:** Radio buttons
- **Required:** Yes
- **Options:** 
  - `1` = ชาย (Male)
  - `2` = หญิง (Female)

### 5. Address Field (ที่อยู่)
- **Type:** Text input with autocomplete
- **Required:** Yes
- **Max Length:** 100 characters
- **Class:** `sg-address`
- **Purpose:** Street address and location details
- **Autocomplete:** Linked to subdistrict field
- **Format Guide:** 
  - House number, alley, street, village number, subdistrict name
  - Example: "0/0 ซอยประชายินดี ถนนมิตรภาพ ม.1 ต.คอหงส์"
- **Smart Features:**
  - Autocomplete as user types
  - Auto-populate district and province
  - Click to select from suggestions

### 6. Province (จังหวัด)
- **Type:** Select dropdown
- **Required:** Yes
- **Default:** "== เลือกจังหวัด =="
- **Options:** All Thai provinces from database
- **Features:**
  - Southern provinces (provid >= 80) listed first
  - Alphabetically sorted
  - Thai language support

### 7. District (อำเภอ)
- **Type:** Select dropdown
- **Required:** Conditional (depends on address selection)
- **Default:** "== เลือกอำเภอ =="
- **Features:** Hidden by default, populated when province selected
- **Dynamic:** Updates based on province selection

### 8. Subdistrict (ตำบล)
- **Type:** Select dropdown
- **Required:** Conditional (depends on address selection)
- **Default:** "== เลือกตำบล =="
- **Features:** Hidden by default, populated when district selected
- **Linked to:** `#edit-patient-areacode` hidden field
- **Dynamic:** Updates based on district selection

### 9. Organization (Hidden Field)
- **Type:** Hidden input
- **Condition:** Only present if `addToOrg` parameter provided
- **Purpose:** Auto-assign patient to organization during creation
- **Value:** Organization ID from `addToOrg` parameter

### 10. Submit Button
- **Type:** Button
- **Label:** "เพิ่มชื่อรายใหม่" (Add New Record)
- **Icon:** Material Icon "add"
- **Action:** Submit form via AJAX

---

## Component Structure

```
Card
├── Header (Header with title and icon)
├── Form (Main patient add form)
│   ├── Hidden Fields (organization ID, area code)
│   ├── Text Fields (prename, fullname, CID, address)
│   ├── Radio Group (sex/gender)
│   ├── Cascading Selects (province, district, subdistrict)
│   └── Submit Button
└── Custom Script (if provided)
```

---

## Form Validation

### Client-Side Validation

The form includes automatic validation for:

| Field | Rules |
|-------|-------|
| Title | Required, max 20 chars |
| Full Name | Required, max 100 chars, must include space |
| CID | Required, max 13 chars (numeric or "?") |
| Gender | Required, radio selection |
| Area Code | Required (hidden field) |
| Address | Required, max 100 chars |
| Province | Required, dropdown selection |

### Submission

- **Method:** AJAX POST
- **Validation:** `checkValid: true` - validates before sending
- **Error Handling:** Displays validation errors in-form

---

## API Integration

### Default Submission Endpoint

**URL:** `/api/imed/patient/create`

**Method:** POST

**Request Format:**
```
patient[prename] = title/prefix
patient[fullname] = full name
patient[cid] = 13-digit ID
patient[sex] = 1|2 (male|female)
patient[areacode] = subdistrict code
patient[address] = address string
patient[changwat] = province ID
patient[ampur] = district ID
patient[tambon] = subdistrict ID
addToOrg = organization ID (if configured)
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Patient created successfully",
  "data": {
    "psnId": "123456",
    "fullname": "สมชาย ใจดี",
    "cid": "1234567890123"
  }
}
```

---

## Callbacks & Actions

### Success Actions (`done` parameter)

#### 1. Reload with URL
```php
'done' => 'reload:imed/patient/{{id}}'
```
Reloads page and navigates to patient detail page. Use `{{id}}` or `{{psnId}}` as placeholder.

#### 2. Redirect to URL
```php
'done' => 'redirect:imed/app/patient-list'
```
Redirects to specified URL.

#### 3. Refresh Current Page
```php
'done' => 'refresh'
```
Reloads current page.

#### 4. Custom Callback
```php
'done' => 'onPatientAdded'
```
Calls JavaScript function with response data.

### Result Population (`rel` parameter)

Insert form results into specific element:

```php
'rel' => '.patient-list'        // CSS selector
'rel' => '#results'             // ID selector
'rel' => '[data-target]'        // Attribute selector
```

---

## Usage Examples

### Example 1: Standalone Patient Addition Page

```php
<?php
$form = new ImedPatientAddFormWidget([
    'done' => 'reload:imed/patient/{{id}}',
    'script' => '<script>
        console.log("Patient addition form loaded");
    </script>'
]);
echo $form->build();
?>
```

### Example 2: Organization-Scoped Addition

```php
<?php
$orgId = $_GET['org_id'] ?? 0;
$form = new ImedPatientAddFormWidget([
    'addToOrg' => $orgId,
    'rel' => '.org-patient-list',
    'done' => 'refresh',
]);
echo $form->build();
?>
```

### Example 3: Pre-filled Form from Search

```php
<?php
$searchQuery = $_GET['q'] ?? '';
$form = new ImedPatientAddFormWidget([
    'fullName' => $searchQuery,
    'class' => 'sg-form minimal',
    'done' => 'reload:imed/admin/patient/{{id}}',
]);
echo $form->build();
?>
```

### Example 4: Embedded in Modal with Custom Script

```php
<?php
$form = new ImedPatientAddFormWidget([
    'addToOrg' => 42,
    'rel' => '#modal-results',
    'done' => 'onPatientCreated',
    'script' => '<script>
        function onPatientCreated(response) {
            console.log("New patient: ", response.data);
            $.modal.close();
            location.reload();
        }
    </script>'
]);
echo $form->build();
?>
```

### Example 5: Custom API Endpoint

```php
<?php
$form = new ImedPatientAddFormWidget([
    'action' => 'api/custom-org/patient/register',
    'addToOrg' => 99,
    'done' => 'redirect:imed/org/dashboard',
    'class' => 'sg-form org-form'
]);
echo $form->build();
?>
```

---

## Address & Geographic Data

### Address Input Workflow

1. User types address with subdistrict name
2. Autocomplete suggests matching subdistricts
3. User clicks suggestion
4. Form auto-populates:
   - `areacode` field (hidden)
   - `tambon` dropdown (Subdistrict)
   - `ampur` dropdown (District)
   - `changwat` dropdown (Province)

### Address Autocomplete Endpoint

The form expects an autocomplete API that:
- Accepts address input as query parameter
- Returns matching subdistricts with province/district info
- Updates related fields automatically

### Geographic Data Sources

Province/District/Subdistrict data sourced from:
- Table: `co_province` (provinces)
- Related tables for districts and subdistricts
- Grouped by geographic zone (South vs. Others)

---

## CSS Classes & Styling

| Class | Element | Purpose |
|-------|---------|---------|
| `sg-form` | Form | Base form styling (default) |
| `-fill` | Input field | Full-width input |
| `sg-address` | Address input | Enables autocomplete styling |
| `sg-changwat` | Province select | Province dropdown specific styling |
| `sg-ampur` | District select | District dropdown specific styling |
| `sg-tambon` | Subdistrict select | Subdistrict dropdown specific styling |
| `-hidden` | Dropdown | Initially hidden (shown on demand) |
| `-sg-text-right` | Button container | Right-align button |

---

## Class Structure

### Properties

```php
class ImedPatientAddFormWidget extends Widget {
    var $action;        // Form submission endpoint
    var $class;         // CSS classes
    var $rel;          // Result target element
    var $done;         // Success callback
    var $addToOrg;     // Organization ID
    var $fullName;     // Pre-fill full name
    var $cid;          // Pre-fill CID
    var $script;       // Custom script
}
```

### Methods

| Method | Access | Description |
|--------|--------|-------------|
| `__construct($args)` | public | Initialize form with configuration |
| `build()` | public | Generate and return form widget |

---

## Dependencies

- **Base Class:** `Widget` - Core widget framework
- **Components:**
  - `Card` - Form container
  - `Header` - Form header with title
  - `Icon` - Material icons
  - `Form` - Form framework
- **Database:**
  - `co_province` table - Province data
  - Related tables for districts/subdistricts
- **External:**
  - jQuery for AJAX and interactions
  - Bootstrap/Selectize for select inputs
  - Thai locale support

---

## Validation Rules & Error Messages

### CID Field
- **Invalid:** Characters other than digits and "?"
- **Error:** "Please enter a valid 13-digit ID or ?"
- **Length:** Exactly 13 characters or single "?"

### Full Name Field
- **Invalid:** No space between first and last name
- **Error:** "Please separate first and last name with a single space"
- **Example Valid:** "สมชาย ใจดี"
- **Example Invalid:** "สมชายใจดี", "สมชาย ใจดี ขยายนาม"

### Required Fields
- All form fields are required
- **Error:** "[Field Name] is required"

---

## Tips & Best Practices

1. **Pre-fill Data:** Use `fullName` and `cid` parameters when redirecting from search
2. **Organization Context:** Always set `addToOrg` when adding patients within organizational scope
3. **Custom Scripts:** Use for tracking, analytics, or post-form processing
4. **Error Handling:** Implement proper error callbacks in custom scripts
5. **Mobile Testing:** Ensure address autocomplete works smoothly on mobile
6. **API Response:** Always return success/error status and patient ID
7. **Callbacks:** Use meaningful `done` callback URLs for better UX
8. **Validation:** Let client-side validation run before submission

---

## Common Integration Patterns

### Pattern 1: Search + Add Flow
```php
// From search results, user clicks "Add New Patient"
// Pre-fill with search query
$form = new ImedPatientAddFormWidget([
    'fullName' => $_GET['name'] ?? '',
    'done' => 'reload:imed/patient/{{id}}'
]);
```

### Pattern 2: Organization Management
```php
// Within organization context
$form = new ImedPatientAddFormWidget([
    'addToOrg' => $currentOrgId,
    'rel' => '.org-patients',
    'done' => 'refresh'
]);
```

### Pattern 3: Embedded Modal
```php
// In a modal/popup
$form = new ImedPatientAddFormWidget([
    'rel' => '#modal-body',
    'done' => 'customCallback'
]);
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Address autocomplete not working | Verify autocomplete API endpoint exists and returns data |
| Province/District dropdowns empty | Check database table exists with correct data |
| Form submission fails silently | Check API endpoint is accessible, returns valid JSON |
| Pre-filled data not showing | Verify field names match form structure exactly |
| Validation not triggering | Ensure `checkValid: true` is set in Form config |
| Style/layout issues | Verify CSS classes are correct, check theme compatibility |

---

## Changelog

| Version | Date | Changes |
|---------|------|---------|
| 2 | 2026-04-23 | Current version |
| 1 | 2021-08-23 | Initial release |

---

## Support

For issues, feature requests, or documentation updates, contact:  
**Little Bear** <softganz@gmail.com>
