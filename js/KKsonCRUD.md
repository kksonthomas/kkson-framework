# KKsonCRUD

`KKsonCRUD` is a JavaScript class that wires together a **DataTables list view** and an **Ajax form** for CRUD pages built on the KKson Framework (AdminLTE + Bootstrap). A single global instance called `crud` is expected to exist on every CRUD page.

---

## Dependencies

| Library | Notes |
|---|---|
| jQuery | Required for all DOM manipulation |
| DataTables 2.x | Core list view rendering (with ColReorder, FixedHeader, FixedColumns, Buttons) |
| Select2 | Auto-initialized on every `.select2` element on page load |
| SweetAlert2 | Used by `AlertUtils` for confirm/error/success dialogs |
| ekko-lightbox | Used for `[data-toggle="lightbox"]` elements |
| Bootstrap 5 | Dropdown, general styling |

---

## Required DOM Elements

| Selector | Purpose |
|---|---|
| `#kkson-crud-table` | The `<table>` that DataTables is initialized on |
| `#kkson-form` | The AJAX form on create/edit pages |
| `.kkson-crud-table-header` | Sticky header bar above the table (used for `fixedHeader` offset calculation) |
| `.main-header` | AdminLTE top navbar (used for `fixedHeader` offset calculation) |
| `.ext-dt-paging` | Container where DataTables pagination is relocated |
| `.crud-dt-colFilter-container` | Container where the column visibility button is relocated |
| `.main-sidebar .nav-item` / `.nav-link` | Sidebar nav items for auto active-state highlighting |
| `.btn-delete` | Delete buttons — requires `data-url` attribute with the DELETE endpoint |
| `.btnRefreshDatatable` | Optional refresh button that reloads the DataTable via Ajax |

---

## Global Variable

```js
var crud = new KKsonCRUD();
```

This instance must be named `crud` — the class internally references `crud.table` and `crud.getDataTable()` in callbacks.

A global `csrfToken` variable must also be defined before the class is used:

```js
var csrfToken = "your-csrf-token-here";
```

---

## `initListView(config)` — Main Configuration

Call this on list pages to initialize the DataTable.

```js
crud.initListView({
    ajaxUrl: "/admin/users/list-data",
    ajaxOptions: { /* optional extra $.ajax options — enables serverSide mode */ },
    enableSearch: true,       // default: true  — DataTables global search box
    enableSorting: true,      // default: true  — column sorting
    enableColSearch: false,   // default: false — per-column search row
    searchableColFieldMap: {  // required when enableColSearch: true
        /* see searchableColFieldMap section below */
    },
    customData: {},           // optional — merged into DataTables init config
                              //            can also be a function: (defaultData) => ({...})
});
```

### `ajaxOptions`

When provided, switches DataTables to **server-side processing** mode. Any key/value pairs here are merged on top of the default ajax config:

```js
{
    url: config.ajaxUrl,
    type: "POST",
    data: { csrf_token: csrfToken }
}
```

Example — add extra POST params:

```js
ajaxOptions: {
    data: function(d) {
        d.filter_status = $("#filter_status").val();
        return d;
    }
}
```

### `customData`

Merges additional DataTables options into the defaults. Use to override `pageLength`, add custom `columns`, etc.

```js
customData: {
    pageLength: 50,
    columns: [
        { data: "id" },
        { data: "name" },
        { data: null, orderable: false, searchable: false }
    ]
}
```

Or as a function to extend based on defaults:

```js
customData: (defaults) => ({
    ...defaults,
    pageLength: 50
})
```

### `searchableColFieldMap`

Maps **column index** (0-based) to a per-column search configuration. Only used when `enableColSearch: true`.

```js
searchableColFieldMap: {
    1: {
        displayName: "Name",
        condition: "like",          // passed to backend as search logic
        render: {
            tag: "input",           // "input" or "select"
            attr: {
                type: "text",
                class: "my-extra-class"
            }
        }
    },
    2: {
        displayName: "Status",
        condition: "eq",
        render: {
            tag: "select",
            placeholder: "-- All --",
            options: {
                "active": "Active",
                "inactive": "Inactive"
            }
        }
    },
    3: {
        displayName: "Custom HTML",
        condition: "like",
        render: '<input type="text" class="form-control col-search-input">'
        // when render is a string, it is used as raw HTML
    }
}
```

**`render` object options:**

| Key | Type | Description |
|---|---|---|
| `tag` | string | HTML tag, e.g. `"input"`, `"select"` |
| `attr` | object | Key/value HTML attributes; `class` is appended, others are set directly |
| `options` | object | `{ value: label }` pairs — only for `select` |
| `placeholder` | string | Placeholder option text for `select` |
| `manualInitValue` | bool | Set `true` to skip auto-populating from the global `keyword` variable |

**URL hash persistence:** column search values are automatically encoded into `window.location.hash` as `colIndex=value` pairs so the state survives page refreshes. A "Clear All" button is injected into the first column's second header row.

---

## `setInitListViewCustomData(data)`

Alternative to `customData` inside `initListView`. Useful to set DataTables options from a shared base file before calling `initListView`.

```js
crud.setInitListViewCustomData({
    pageLength: 100,
    crudCustomClasses: {
        colSeachClearButton: "btn-sm",
        colSearchInput: "input-sm"
    }
});
```

### `crudCustomClasses`

Optional sub-object inside `initListViewCustomData` (or `customData`) for styling column search controls:

| Key | Applies to |
|---|---|
| `colSeachClearButton` | The "Clear All" button in the first column header |
| `colSearchInput` | Every generated `col-search-input` element |

---

## Form Handling

The class automatically intercepts `form.ajax` submissions on the page:

- Serializes `#kkson-form`
- Runs all registered validators
- POSTs to the form's `action` using the method in `data-method`
- Calls `ajaxFormCallback` on success

```html
<form id="kkson-form" class="ajax" action="/admin/users/store" data-method="POST">
    ...
</form>
```

### `setAjaxFormCallback(callback)`

```js
crud.setAjaxFormCallback(function(result) {
    if (result.ok) {
        location.href = result.redirectUrl;
    } else {
        AlertUtils.showError("Error", result.error);
    }
});
```

### `addValidator(func)` / `addErrorMsg(msg)`

Register custom validation before form submit. Return `false` from the function to block submission.

```js
crud.addValidator(function(data) {
    if (!data.name) {
        crud.addErrorMsg("Name is required.");
        return false;
    }
});
```

---

## Sidebar Active-State Highlighting

On page load, the class automatically marks the matching sidebar nav link as active using `location.pathname`.

### Nav link `data` attributes

| Attribute | Description |
|---|---|
| `data-alt-hrefs` | JSON array of alternative URL paths that also activate this menu item |
| `data-alt-options` | JSON array of option arrays matching each `alt-href` |

### CSS classes on `<a class="nav-link">`

| Class | Behavior |
|---|---|
| `crud` | Matches `/create`, `/edit`, `/list` URL segments interchangeably |
| `match_get` | Also matches the query string exactly |

```html
<a class="nav-link crud" href="/admin/users/list">Users</a>

<a class="nav-link match_get" href="/admin/reports?type=monthly">Monthly Report</a>
```

---

## Public API Summary

| Method | Description |
|---|---|
| `initListView(config)` | Initialize DataTables on `#kkson-crud-table` |
| `setInitListViewCustomData(data)` | Pre-set DataTables options before `initListView` |
| `getDataTable()` | Returns the DataTables API instance |
| `setAjaxFormCallback(fn)` | Set callback for successful Ajax form submit |
| `addValidator(fn)` | Register a form validation function |
| `addErrorMsg(msg)` | Push an error message (called inside validators) |
| `field(name)` | Shortcut for `$("#field-{name}")` |
| `resetColOrder()` | Reset DataTables column order to default |
| `refresh()` | Re-bind `.btn-delete` and `.btn-confirm` click handlers (auto-called on draw) |
| `setUploading(bool)` | Block form submission while a file upload is in progress |
| `mergeObject(obj1, obj2)` | Shallow-merge two objects (obj2 wins) |

---

## Minimal List Page Bootstrap

```js
// list.blade.php inline script (or list.js)
var crud = new KKsonCRUD();

crud.setInitListViewCustomData({
    pageLength: 25
});

crud.initListView({
    ajaxUrl: "/admin/users/list-data",
    ajaxOptions: {},
    enableSearch: true,
    enableSorting: true,
    enableColSearch: true,
    searchableColFieldMap: {
        1: { condition: "like", render: { tag: "input", attr: { type: "text" } } },
        2: { condition: "eq",   render: { tag: "select", options: { "1": "Active", "0": "Inactive" } } }
    },
    customData: {
        columns: [
            { data: "id" },
            { data: "name" },
            { data: "status" },
            { data: null, orderable: false, render: function(data, type, row) {
                return `<a href="/admin/users/${row.id}/edit">Edit</a>`;
            }}
        ]
    }
});
```

## Minimal Create/Edit Page Bootstrap

```js
var crud = new KKsonCRUD();

crud.setAjaxFormCallback(function(result) {
    if (result.ok) {
        ToastUtils.showSuccess("Saved!");
        setTimeout(() => location.href = "/admin/users/list", 1000);
    } else {
        AlertUtils.showError("Error", result.error);
    }
});

crud.addValidator(function(data) {
    if (!data.name || data.name.trim() === "") {
        crud.addErrorMsg("Name cannot be empty.");
        return false;
    }
});
```
