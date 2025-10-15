# Creating Schemas

Schemas in ARC Blueprint define the structure and field configuration for your data models. They work in conjunction with Collections to provide a complete data management solution with automatic form generation.

## Table of Contents

- [Basic Concepts](#basic-concepts)
- [Simplest Approach](#simplest-approach)
- [Field Configuration](#field-configuration)
- [Available Field Types](#available-field-types)
- [Advanced Examples](#advanced-examples)
- [Registration](#registration)

## Basic Concepts

A Schema is a PHP class that:
- Extends `ARC\Blueprint\Schema`
- Defines a `$collection` property pointing to the associated Collection class
- Defines a `$fields` array with field configurations
- Must be registered with a unique key (lowercase letters and underscores only)

## Simplest Approach

The most minimal schema requires only a collection reference. All fields will use default configurations derived from the Collection's table structure.

```php
<?php

namespace YourPlugin\Schemas;

use ARC\Blueprint\Schema;
use YourPlugin\Collections\ItemCollection;

class ItemSchema extends Schema
{
    protected $collection = ItemCollection::class;
}
```

**Register it:**

```php
ItemSchema::register('item');
```

That's it! This schema will automatically generate forms based on your Collection's table structure with sensible defaults.

## Field Configuration

To customize field behavior, add field configurations to the `$fields` array. Each field can have various properties depending on its type.

### Common Field Properties

All field types support these common properties:

- `type` - Field type (text, textarea, number, select, etc.)
- `label` - Display label for the field
- `required` - Whether the field is required (boolean)
- `placeholder` - Placeholder text
- `helpText` - Additional help text displayed below the field
- `default` - Default value
- `hidden` - Hide the field from forms (boolean)
- `readonly` - Make the field read-only (boolean)

### Example with Common Properties

```php
<?php

namespace Waypoint\Schemas;

use ARC\Blueprint\Schema;
use Waypoint\Collections\DocSetCollection;

class DocSetSchema extends Schema
{
    protected $collection = DocSetCollection::class;

    protected $fields = [
        'name' => [
            'type' => 'text',
            'label' => 'Doc Set Name',
            'required' => true,
            'placeholder' => 'Doc set name...',
        ],
        'description' => [
            'type' => 'textarea',
            'label' => 'Description',
            'required' => false,
            'placeholder' => 'Doc set description...',
        ],
        'icon' => [
            'type' => 'text',
            'label' => 'Icon',
            'required' => false,
            'placeholder' => 'Icon name or URL...',
        ]
    ];
}
```

## Available Field Types

### Text Input Fields

#### text
Basic single-line text input.

```php
'title' => [
    'type' => 'text',
    'label' => 'Title',
    'required' => true,
    'placeholder' => 'Enter title...',
]
```

#### email
Email input with validation.

```php
'email' => [
    'type' => 'email',
    'label' => 'Email Address',
    'required' => true,
    'placeholder' => 'user@example.com',
]
```

#### url
URL input with validation.

```php
'website' => [
    'type' => 'url',
    'label' => 'Website',
    'placeholder' => 'https://example.com',
]
```

#### password
Password input (masked).

```php
'api_key' => [
    'type' => 'password',
    'label' => 'API Key',
    'required' => true,
]
```

### Multi-line Text Fields

#### textarea
Basic multi-line text input.

```php
'description' => [
    'type' => 'textarea',
    'label' => 'Description',
    'placeholder' => 'Enter description...',
    'rows' => 5, // Optional: number of visible rows
]
```

#### markdown
Markdown editor with preview and toolbar.

```php
'content' => [
    'type' => 'markdown',
    'label' => 'Content',
    'placeholder' => 'Enter markdown text...',
    'minHeight' => '200px', // Optional
    'maxHeight' => '500px', // Optional
]
```

#### wysiwyg
Rich text WYSIWYG editor.

```php
'body' => [
    'type' => 'wysiwyg',
    'label' => 'Body Content',
    'required' => true,
]
```

### Numeric Fields

#### number
Numeric input.

```php
'position' => [
    'type' => 'number',
    'label' => 'Position',
    'default' => 0,
    'min' => 0, // Optional
    'max' => 100, // Optional
    'step' => 1, // Optional
]
```

#### range
Slider input for numeric values.

```php
'priority' => [
    'type' => 'range',
    'label' => 'Priority',
    'min' => 1,
    'max' => 10,
    'step' => 1,
    'default' => 5,
]
```

### Selection Fields

#### select
Dropdown selection.

```php
'status' => [
    'type' => 'select',
    'label' => 'Status',
    'required' => true,
    'options' => [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ],
    'default' => 'draft',
]
```

#### radio
Radio button group.

```php
'type' => [
    'type' => 'radio',
    'label' => 'Type',
    'required' => true,
    'options' => [
        'article' => 'Article',
        'tutorial' => 'Tutorial',
        'guide' => 'Guide',
    ],
]
```

#### button_group
Button group for mutually exclusive options.

```php
'alignment' => [
    'type' => 'button_group',
    'label' => 'Alignment',
    'options' => [
        'left' => 'Left',
        'center' => 'Center',
        'right' => 'Right',
    ],
    'default' => 'left',
]
```

#### checkbox
Single checkbox for boolean values.

```php
'is_featured' => [
    'type' => 'checkbox',
    'label' => 'Featured',
    'default' => false,
]
```

### Special Fields

#### relation
Relates to another collection via API endpoint.

```php
'doc_group_id' => [
    'type' => 'relation',
    'label' => 'Doc Group',
    'required' => true,
    'relation' => [
        'endpoint' => '/wp-json/gateway/v1/doc-groups',
        'labelField' => 'title',
        'valueField' => 'id',
        'placeholder' => 'Select a doc group...',
    ]
]
```

#### color
Color picker.

```php
'theme_color' => [
    'type' => 'color',
    'label' => 'Theme Color',
    'default' => '#3b82f6',
]
```

#### readonly
Display-only field (not editable).

```php
'created_at' => [
    'type' => 'readonly',
    'label' => 'Created At',
]
```

#### hidden
Hidden field (not visible in forms, but submitted with data).

```php
'internal_id' => [
    'type' => 'hidden',
    'default' => '',
]
```

## Advanced Examples

### Complete Example from Waypoint Plugin

Here's a real-world example showing multiple field types and relationships:

```php
<?php

namespace Waypoint\Schemas;

use ARC\Blueprint\Schema;
use Waypoint\Collections\DocCollection;

class DocSchema extends Schema
{
    protected $collection = DocCollection::class;

    protected $fields = [
        'title' => [
            'type' => 'text',
            'label' => 'Doc Title',
            'required' => true,
            'placeholder' => 'Doc title...',
        ],
        'content' => [
            'type' => 'markdown',
            'label' => 'Content',
        ],
        'doc_group_id' => [
            'type' => 'relation',
            'label' => 'Doc Group',
            'required' => true,
            'relation' => [
                'endpoint' => '/wp-json/gateway/v1/doc-groups',
                'labelField' => 'title',
                'valueField' => 'id',
                'placeholder' => 'Select a doc group...',
            ]
        ],
        'position' => [
            'type' => 'number',
            'label' => 'Position',
            'required' => false,
            'default' => 0,
        ]
    ];
}
```

### Schema with Nested Relationships

```php
<?php

namespace Waypoint\Schemas;

use ARC\Blueprint\Schema;
use Waypoint\Collections\DocGroupCollection;

class DocGroupSchema extends Schema
{
    protected $collection = DocGroupCollection::class;

    protected $fields = [
        'title' => [
            'type' => 'text',
            'label' => 'Doc Group Title',
            'required' => true,
            'placeholder' => 'Doc group title...',
        ],
        'doc_set_id' => [
            'type' => 'relation',
            'label' => 'Doc Set',
            'required' => true,
            'relation' => [
                'endpoint' => '/wp-json/gateway/v1/doc-sets',
                'labelField' => 'name',
                'valueField' => 'id',
                'placeholder' => 'Select a doc set...',
            ]
        ],
        'position' => [
            'type' => 'number',
            'label' => 'Position',
            'required' => false,
            'default' => 0,
        ]
    ];
}
```

## Registration

Schemas must be registered with a unique key during the `arc_gateway_loaded` action. The key must be lowercase letters and underscores only.

```php
public function registerSchemas() {
    if (!class_exists('\ARC\Blueprint\Schema')) {
        return;
    }

    Schemas\DocSetSchema::register('doc_set');
    Schemas\DocGroupSchema::register('doc_group');
    Schemas\DocSchema::register('doc');
}
```

**In your plugin initialization:**

```php
add_action('arc_gateway_loaded', [$this, 'registerSchemas']);
```

### Key Validation

Schema keys are validated to ensure they follow the correct format:

- ✅ Valid: `item`, `doc_set`, `user_profile`
- ❌ Invalid: `Item`, `doc-set`, `docSet`, `doc.set`

Invalid keys will throw an `\InvalidArgumentException`.

## Best Practices

1. **Use meaningful field labels** - Make forms user-friendly
2. **Add placeholder text** - Guide users on expected input
3. **Mark required fields** - Set `required => true` for mandatory fields
4. **Provide default values** - Use `default` for common initial values
5. **Add help text** - Use `helpText` to explain complex fields
6. **Use appropriate field types** - Choose the type that best matches your data
7. **Validate relationships** - Ensure relation endpoints are correct and accessible
8. **Group related schemas** - Organize schemas in a dedicated namespace folder

## Next Steps

- Learn about [Collections](./CreatingCollections.md) to understand the data layer
- Explore [Admin Pages](./CreatingAdminPages.md) to display your schemas in the WordPress admin
- Check out the [Waypoint plugin](../../../waypoint/) for complete working examples
