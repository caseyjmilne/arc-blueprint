=== ARC Blueprint ===
Contributors: arcsoftware
Tags: forms, schema, collections, api, react
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Schema-based form builder with dynamic React forms and API integration for WordPress.

== Description ==

ARC Blueprint is a powerful schema-based form builder that automatically generates forms from your data models. Built on top of the ARC Framework ecosystem, it seamlessly integrates with ARC Gateway collections to provide a complete form-to-API solution.

= Key Features =

* **Schema-Based Architecture** - Define schemas that reference your collections and models
* **Dynamic Form Generation** - Automatically creates forms from model fillable fields
* **React Form Builder** - Modern, responsive forms built with React and Tailwind CSS
* **API Integration** - Direct submission to ARC Gateway collection endpoints
* **Field Type Detection** - Intelligent input type selection based on field names and casts
* **Form Validation** - Built-in validation with React Hook Form and Zod
* **Extensible Field Types** - Create custom field types by extending the FieldType class

= Requirements =

* ARC Forge - Eloquent ORM integration
* ARC Gateway - Collection and API management

= How It Works =

1. Create a schema that references your collection
2. Register the schema with a unique key
3. The admin interface displays a dynamic form based on the model's fillable fields
4. Form submissions are sent directly to your collection's API endpoint

= Example =

```php
// Define your schema
class TicketSchema extends \ARC\Blueprint\Schema {
    protected $collection = TicketCollection::class;
}

// Register it
TicketSchema::register('ticket');
```

The plugin automatically:
- Generates form fields from your model
- Detects appropriate input types (text, email, textarea, etc.)
- Submits to the correct API endpoint
- Handles success and error states

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/arc-blueprint/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Ensure ARC Forge and ARC Gateway are installed and activated
4. Navigate to ARC Blueprint in the admin menu

= Manual Installation =

1. Download the plugin zip file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the downloaded file and click Install Now
4. Activate the plugin

== Frequently Asked Questions ==

= What are the requirements? =

ARC Blueprint requires:
- WordPress 5.0 or higher
- PHP 7.4 or higher
- ARC Forge plugin (for Eloquent ORM)
- ARC Gateway plugin (for API collections)

= How do I create a custom schema? =

1. Create a class extending `\ARC\Blueprint\Schema`
2. Set the `$collection` property to your collection class
3. Register it with `YourSchema::register('your_key')`

= Can I customize field types? =

Yes! Extend the `\ARC\Blueprint\FieldType\FieldType` abstract class to create custom field types with your own rendering and validation logic.

= Where are the forms displayed? =

Currently, forms are displayed in the ARC Blueprint admin page. Future versions will include shortcodes and Gutenberg blocks for frontend display.

== Screenshots ==

1. Schema registry showing registered schemas
2. Dynamic form generated from model
3. Form submission with success message
4. Admin interface overview

== Changelog ==

= 0.0.1 =
* Initial release
* Schema-based architecture
* Dynamic React form generation
* Integration with ARC Gateway collections
* Support for custom field types
* Admin interface for form management

== Upgrade Notice ==

= 0.0.1 =
Initial release of ARC Blueprint.

== Development ==

ARC Blueprint is part of the ARC Framework ecosystem. For more information, visit:
* GitHub: https://github.com/caseyjmilne/arc-blueprint
* Documentation: Coming soon

== Credits ==

Built with:
* React (via WordPress @wordpress/element)
* Tailwind CSS
* React Hook Form
* Zod validation
* Axios
