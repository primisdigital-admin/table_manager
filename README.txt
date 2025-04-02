=== Table Manager ===
Contributors: primisdigital
Plugin Name: Table Manager
Plugin URI: https://www.primisdigital.com/wordpress-plugins/
version :1.0
Author: Prims digital
Tags: tables, database, table manager, database tables, shortcode, table creator, custom tables, data management, WordPress database
Requires at least: 6.0
Tested up to: 6.7.2
Requires PHP: 7.4
License: GNU General Public License v3.0 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Table Manager plugin helps to create table from wordpress posts, page.

== Description ==
The "Table Manager" plugin for WordPress allows users to create, manage and display tables easily using a shortcode. It enables creating tables, adding and updating columns, and managing data (insert, update, delete). Each table generates a unique shortcode for displaying content on posts or pages. The plugin also provides secure form handling, an easy-to-use admin interface, and custom CSS/JS for table display on the front end.

==Features==

Create and manage custom tables from the WordPress admin panel.
Add, delete, and update table columns dynamically.
Insert and update table data through an easy-to-use interface.
The Display tables using a simple shortcode.
The Secure and optimized queries using WordPress best practices.


==Screenshots==

1. Dashboard Overview– A simple and intuitive interface to manage tables.
2. Create New Table– Easily create custom tables with required columns.
3. Manage Existing Tables– Edit, delete, and update tables effortlessly.
4. Shortcode Generation– Auto-generate shortcodes to display tables on any page.
5. Table Display on Frontend– Beautifully formatted tables on your WordPress site.


==Installation==

==Admin Installer via search:
 
Visit the Add New plugin screen and search for "Table Manager".
Click the "Install Now" button.
Activate the plugin.

==Admin Installer via zip:

Download the Plugin.
Extract the ZIP file and upload.
Activate the plugin through the "Plugins" menu in WordPress.


==Usage==

1. Creating a New Table

Navigate to Table Manager in the WordPress admin panel.
Click on "Create Table" and enter the table name.
Add columns as needed and save the table.

2. Managing Table Data

Select a table from the dropdown menu.
Insert new records using the input form.
Delete or update records directly from the interface.

3. Displaying Tables on the Frontend

Use the following shortcode in posts or pages to display a table:
[display_table name="your_table_name"]


==Security Best Practices==

Admin Capability Check: Access is restricted to administrators using manage_options capability.
SQL Injection Prevention: Queries are secured with WordPress $wpdb->prepare() where applicable.
Input Sanitization: All user inputs are sanitized before being inserted into the database.

==Code Example==

Registering Admin Menu:

function form_datamenu() {
    add_menu_page(
        'Table Manager', 'Table Manager', 'manage_options',
        'form_datamenu', 'form_data_adminpage',
        'dashicons-database', 20
    );
}
add_action('admin_menu', 'form_datamenu');


== Frequently Asked Questions ==

1. What is Table Manager?
Table Manager is a WordPress plugin that allows users to create, manage, and display database tables using an intuitive interface and shortcodes.

2.How do I install the plugin?

Downloading the plugin ZIP file.
Navigating to Plugins > Add New in your WordPress dashboard.
Clicking Upload Plugin and selecting the ZIP file.
Clicking Install Now, then Activate the plugin.

3.How do I create a table?

Go to Table Manager in the WordPress admin panel.
Click Add New Table and define your columns.
Click Save Table to store it in the database.

4.How do I display a table on a post or page?

Use the shortcode format:
[table_manager table='table name']

5.Can I edit or delete a table after creating it?

Yes! You can edit or delete any table from the Table Manager dashboard.



