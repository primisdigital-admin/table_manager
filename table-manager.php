<?php
/*
* Plugin Name: Table Manager
* Plugin URL: https://primisdigital.com/
* Author: primisdigital 
* Author URL: https://primisdigital.com/  
* Description: A powerful WordPress plugin to create tables, add columns, insert data, update data, and display data via shortcode.
* Version: 1.0.0
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: table-manager  
*/

if ( ! defined( 'ABSPATH' ) ) exit;


// Register Admin Menu
add_action('admin_menu', 'tablemanager_data_menu');
function tablemanager_data_menu() {
    add_menu_page(
        'Table Manager', 'Table Manager', 'manage_options',
        'tablemanager_data_menu', 'tablemanager_data_admin_page',
        'dashicons-database',plugin_dir_url(__FILE__) . 'icon.png', 20
    );

    $created_tables = get_option('tablemanager_created_tables', []);
    foreach ($created_tables as $table) {
        add_submenu_page(
            'tablemanager_data_menu',
            ucfirst($table) . ' Table',
            ucfirst($table),
            'manage_options',
            'form_data_' . $table,
            function() use ($table) {
                tablemanager_data_admin_page($table);
            }
        );
    }
}

// Admin Page Function
function tablemanager_data_admin_page($selected_table = null) {
    global $wpdb;
    
    $created_tables = get_option('tablemanager_created_tables', []);

    echo "<div class='wrap'><h1>Table Manager</h1>";

    // Create Table & Generate Shortcode
    if (isset($_POST['create_table']) && !empty($_POST['table_name'])) {
        check_admin_referer('create_table_action', 'create_table_nonce');

        $table_name = sanitize_key($_POST['table_name']);
        $full_table_name = $wpdb->prefix . $table_name;

        if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $full_table_name)) != $full_table_name) {
            $sql = "CREATE TABLE $full_table_name (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);

            $created_tables[] = $table_name;
            update_option('tablemanager_created_tables', $created_tables);
        }
    }


    
echo '<div class="column-actions">';
    

/* ---- Create a New Table ---- */

echo '<form method="post" class="tm-form"><h3>Create a New Table</h3>';
wp_nonce_field('create_table_action', 'create_table_nonce');
echo '
    <div class="form-group">
        <input type="text" name="table_name" placeholder="Enter Table Name" required>
    </div>
    <input type="submit" name="create_table" value="Create Table" class="button button-primary">
</form><hr>';



/* ---- Delete a Table ---- */

echo '<form method="post" class="tm-form"><h3>Delete a Table</h3>';
wp_nonce_field('delete_table_action', 'delete_table_nonce'); // ✅ Correct placement
echo '
    <div class="form-group">
        <select name="delete_table" class="full-width-input">
            <option value="">Select Table</option>';

foreach ($created_tables as $table) {
    echo '<option value="' . esc_attr($table) . '">' . esc_html($table) . '</option>';
}

echo '</select>
    </div>
    <input type="submit" name="delete_table_submit" value="Delete Table" class="button button-primary">
</form>';


    

echo '</div><hr>'; 

// Handle Delete Table
if (isset($_POST['delete_table_submit']) && !empty($_POST['delete_table'])) {
    check_admin_referer('delete_table_action', 'delete_table_nonce');

    $table_to_delete = sanitize_key($_POST['delete_table']);
    $full_table_name = $wpdb->prefix . $table_to_delete;

    // Drop the table from the database
    $wpdb->query(
        $wpdb->prepare(
            "DROP TABLE IF EXISTS %s",
            $full_table_name
        )
    );
    

    // Remove from the stored option
    $created_tables = array_diff($created_tables, [$table_to_delete]);
    update_option('tablemanager_created_tables', $created_tables);

    echo '<p style="color: red;">Table <strong>' . esc_html( $table_to_delete ) . '</strong> has been deleted.</p>';

}


if (!$selected_table) {
    echo '<h1>Existing Tables</h1>';
    echo '<div class="existing-tables">';
    echo '<table class="tablemanager-list widefat fixed striped">';
    echo '<thead><tr><th>Table Name</th><th>Shortcode</th><th>Actions</th></tr></thead>';
    echo '<tbody>';

    foreach ($created_tables as $table) {
        echo "<tr>
        <td><strong>" . esc_html( ucfirst( $table ) ) . "</strong></td>
        <td><code>[table_manager table='" . esc_attr( $table ) . "']</code></td>
        <td>
        <a href='" . esc_url( "?page=form_data_" . $table ) . "' class='button button-primary'>Manage</a>
        <form method='post' style='display:inline; margin:0; padding:0;'>
        " . wp_kses(
        wp_nonce_field( 'delete_table_action', 'delete_table_nonce', true, false ),
          array(
               'input' => array(
                'type'  => true,
                'name'  => true,
                'id'    => true,
                'value' => true,
            ),
        )
        ) . "

        <input type='hidden' name='delete_table' value='" . esc_attr( $table ) . "'>
        <button type='submit' name='delete_table_submit' class='button button-danger' style='margin-left:px;' onclick='return confirm(\"" . esc_js( "Are you sure you want to delete this table?" ) . "\")'>Delete</button>
        </form>
        </td>
        </tr>";

    }

    echo '</tbody></table>';
    echo '</div>';
    return;
}




    // Display shortcode for each table
    if ($selected_table) {
        echo '<h3>Use this Shortcode to Display the Table</h3>';
        echo '<code>[table_manager table="' . esc_attr($selected_table) . '"]</code><hr>';
    }



    // Add/Delete Column Section
    $table_name = $wpdb->prefix . $selected_table;
    $table_name = esc_sql($table_name); // Sanitize the table name
    $columns = $wpdb->get_col("DESC `$table_name`", 0); // Use backticks


    // Update Column Name
    if (isset($_POST['update_column']) && !empty($_POST['old_column_name']) && !empty($_POST['new_column_name'])) {
        $old_column = sanitize_key($_POST['old_column_name']);
        $new_column = sanitize_key($_POST['new_column_name']);
        $table_name   = esc_sql($table_name);
        $old_column   = esc_sql($old_column);
        $new_column   = esc_sql($new_column);

        $sql = "ALTER TABLE `$table_name` CHANGE `$old_column` `$new_column` TEXT";
        $wpdb->query($sql);

    }

    // Remove Column
    if (isset($_POST['remove_column']) && !empty($_POST['delete_column'])) {
        $column_name = sanitize_key($_POST['delete_column']);
        $table_name   = esc_sql($table_name);
        $column_name  = esc_sql($column_name);

        $sql = "ALTER TABLE `$table_name` DROP COLUMN `$column_name`";
        $wpdb->query($sql);

    }

    echo '<h3>Manage Columns</h3>';
    echo '<div class="column-actions">';

// Add Column Form
echo '<form method="post" class="tm-form"><h3>Add Columns</h3>
    <input type="text" name="column_name" placeholder="New Column Name" required>
    <input type="submit" name="add_column" value="Add Column" class="button button-primary">
</form>';


echo '<form method="post" class="tm-form"><h3>Update Column</h3>';
echo '<form method="post" class="update-column-form" class="tm-form">
    <div class="update-column-flex">
        <select name="old_column_name" required>
            <option value="">Choose a column</option>';
            foreach ($columns as $column) {
                echo "<option value='" . esc_attr( $column ) . "'>" . esc_html( $column ) . "</option>";

            }
echo '</select>

        <input type="text" name="new_column_name" placeholder="New Column Name" required>
    </div>

    <div class="update-column-button">
        <input type="submit" name="update_column" value="Rename Column" class="button button-primary">
    </div>
</form>';





// Delete Column Form
echo '<form method="post" class="tm-form"><h3>Delete Columns</h3>
    <select name="delete_column">
        <option value="">Select Column to Delete</option>';
        foreach ($columns as $column) {
            if ($column !== 'id') {
                echo "<option value='" . esc_attr( $column ) . "'>" . esc_html( $column ) . "</option>";

            }
        }
echo '</select>
    <input type="submit" name="remove_column" value="Delete Column" class="button button-primary">
</form>';

echo '</div><hr>';

    
    
    // Add Column
    if (isset($_POST['add_column']) && !empty($_POST['column_name'])) {
        $column_name = sanitize_key($_POST['column_name']);
$column_name = esc_sql($column_name);
$sql = $wpdb->prepare("ALTER TABLE `$table_name` ADD `$column_name` TEXT", []);
$wpdb->query($sql);


    }

    // Update Column Name
    if (isset($_POST['update_column']) && !empty($_POST['old_column_name']) && !empty($_POST['new_column_name'])) {
        $old_column = sanitize_key($_POST['old_column_name']);
$new_column = sanitize_key($_POST['new_column_name']);
$old_column = esc_sql($old_column);
$new_column = esc_sql($new_column);
$sql = $wpdb->prepare("ALTER TABLE `$table_name` CHANGE `$old_column` `$new_column` TEXT", []);
$wpdb->query($sql);

    }


    // Remove Column
    if (isset($_POST['remove_column']) && !empty($_POST['delete_column'])) {
        $column_name = sanitize_key($_POST['delete_column']);
        $wpdb->query("ALTER TABLE $table_name DROP COLUMN $column_name");
    }

    // Insert Data
echo '<h3>Insert Data</h3>
<form method="post" class="tm-form">';
foreach ($columns as $column) {
    if ($column !== 'id') {
        echo '<label>' . esc_html( ucfirst( $column ) ) . '</label>';
        echo '<input type="text" name="' . esc_attr( $column ) . '"><br>';
    }
}
echo '<input type="submit" name="insert_data" value="Insert" class="button button-primary">
</form><hr>';

if (isset($_POST['insert_data'])) {
    // Only process specific insert fields
    $data = [];
    foreach ($columns as $column) {
        if ($column !== 'id' && isset($_POST[$column])) {
            $data[$column] = sanitize_text_field($_POST[$column]);
        }
    }
    
    if (!empty($data)) {
        $wpdb->insert($table_name, $data);
    }
}

// Update Data
if (isset($_POST['update_data']) && isset($_POST['update_id'])) {
    $update_id = intval($_POST['update_id']);
    $update_data = [];
    
    foreach ($columns as $column) {
        // Skip id and other non-update fields
        if ($column !== 'id' && isset($_POST[$column])) {
            $update_data[$column] = sanitize_text_field($_POST[$column]);
        }
    }

    if (!empty($update_data)) {
        // Perform the update only if there are changes
        $wpdb->update($table_name, $update_data, ['id' => $update_id]);
    }
}

// Delete Data
if (isset($_GET['delete_id'])) {
    $wpdb->delete($table_name, ['id' => intval($_GET['delete_id'])]);
}

// Display Data
$results = $wpdb->get_results("SELECT * FROM $table_name");

echo '<h3>Table Data</h3>';
echo '<table class="tablemanager-list widefat fixed striped">';
echo '<thead><tr>';
foreach ($columns as $column) {
    echo '<th>' . esc_html( ucfirst( $column ) ) . '</th>';
}
echo '<th>Actions</th>';
echo '</tr></thead><tbody>';

foreach ($results as $row) {
    echo '<tr>';
    
    foreach ($columns as $column) {
        echo '<td>' . (isset($row->$column) ? esc_html($row->$column) : 'N/A') . '</td>';
    }
    
    echo '<td>
         <a href="?page=form_data_' . esc_attr($selected_table) . '&edit_id=' . esc_attr($row->id) . '" class="button button-primary">Edit</a>
         <a href="?page=form_data_' . esc_attr($selected_table) . '&delete_id=' . esc_attr($row->id) . '" class="button button-danger" onclick="return confirm(\'Delete this entry?\')">Delete</a>
    </td>';
    
    echo '</tr>';
}

echo '</tbody></table>';

// Edit Form
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $edit_id));

    if ($edit_row) {
        echo '<h3>Edit Data</h3>
        <form method="post" class="tm-form">';
        foreach ($columns as $column) {
            echo '<label>' . esc_html( ucfirst( $column ) ) . '</label>';
            echo '<input type="text" name="' . esc_attr( $column ) . '" value="' . esc_attr( $edit_row->$column ) . '">';
        }
        
        echo '<input type="hidden" name="update_id" value="' . esc_attr($edit_id) . '">
        <input type="submit" name="update_data" value="Update" class="button button-primary">
        </form>';
    }
}

// Handle Update Data Processing (post form submission for update)
if (isset($_POST['update_data']) && isset($_POST['update_id'])) {
    $update_id = intval($_POST['update_id']);
    $update_data = [];

    foreach ($columns as $column) {
        if ($column !== 'id' && isset($_POST[$column])) {
            $update_data[$column] = sanitize_text_field($_POST[$column]);
        }
    }

    if (!empty($update_data)) {
        // Update only if there's data to update
        $wpdb->update($table_name, $update_data, ['id' => $update_id]);
        echo "<script>window.location.href = '?page=form_data_" . esc_attr($selected_table) . "';</script>";
        exit;
    }
}
}

// Shortcode for displaying tables
function tablemanager_render_table_shortcode($atts) {
    global $wpdb;
    echo "<style>
        table {
             width: 100%;
             border-collapse: collapse;
             background: #fff;
             box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
             border-radius: 8px;
             overflow: hidden;
             margin-top: 15px;
            }

       th {
       background: #0073aa;
       color: white;
       font-weight: bold;
       text-transform: capitalize;
       text-align: center;
       font-size: 16px;
       }

       td {
       text-align: center;
       font-size: 15px;
       color: black;
       }

       tr:nth-child(even) {
       background: #f9f9f9;
       }


        .button-small {
            padding: 5px 10px;
            font-size: 12px;
            background-color: red;
            color: white;
            border-radius: 3px;
            cursor: pointer;
        }
    </style>";


    $atts = shortcode_atts(['table' => ''], $atts, 'table_manager');
    $table_name = sanitize_key($atts['table']);
    $full_table_name = $wpdb->prefix . $table_name;

    if (empty($table_name)) {
        return '<p style="color:red;">Error: Table name is missing in the shortcode.</p>';
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") != $full_table_name) {
        return '<p style="color:red;">Error: Table <strong>' . esc_html($table_name) . '</strong> does not exist.</p>';
    }

    $columns = $wpdb->get_col("DESC $full_table_name", 0);
    $results = $wpdb->get_results("SELECT * FROM $full_table_name");

    $output = '<table class="custom-table">';
    $output .= '<thead><tr>';
    foreach ($columns as $column) {
        $output .= '<th>' . esc_html(ucfirst($column)) . '</th>';
    }
    $output .= '</tr></thead><tbody>';
    
    if (!empty($results)) {
        foreach ($results as $row) {
            $output .= '<tr>';
            foreach ($columns as $column) {
                $output .= '<td>' . esc_html($row->$column) . '</td>';
            }
            $output .= '</tr>';
        }
    } else {
        $output .= '<tr><td colspan="' . count($columns) . '">No data found.</td></tr>';
    }
    
    $output .= '</tbody></table>';
    return $output;
}
add_shortcode('table_manager', 'tablemanager_render_table_shortcode');

if (!function_exists('tablemanager_render_table_shortcode')) {
function tablemanager_render_table_shortcode($atts) {
    global $wpdb;

    $atts = shortcode_atts([
        'table' => ''
    ], $atts);

    $table = sanitize_key($atts['table']);
    if (empty($table)) {
        return '<p style="color:red;">Table not specified.</p>';
    }

    $table_name = $wpdb->prefix . $table;
    $columns = $wpdb->get_col("DESC `$table_name`", 0);
    if (empty($columns)) {
        return '<p style="color:red;">Invalid or empty table.</p>';
    }

    $rows = $wpdb->get_results("SELECT * FROM `$table_name`");

    ob_start();
    echo '<div class="tablemanager-frontend-table">';
    echo '<table border="1" style="width:100%; border-collapse:collapse;">';
    echo '<thead><tr>';
    foreach ($columns as $column) {
        echo '<th>' . esc_html(ucfirst($column)) . '</th>';
    }
    echo '</tr></thead><tbody>';

    foreach ($rows as $row) {
        echo '<tr>';
        foreach ($columns as $column) {
            echo '<td>' . esc_html($row->$column) . '</td>';
        }
        echo '</tr>';
    }

    echo '</tbody></table>';
    echo '</div>';

    return ob_get_clean();
}
}
add_shortcode('table_manager', 'tablemanager_render_table_shortcode');


add_action('wp_enqueue_scripts', 'tablemanager_enqueue_styles');
function tablemanager_enqueue_styles() {
    wp_enqueue_style('tablemanager-style', plugin_dir_url(__FILE__) . 'css/tablemanager.css');
}





function tablemanager_enqueue_assets($hook) {
    wp_enqueue_style('form-data-style', plugin_dir_url(__FILE__) . 'css/style.css', [], time()); // Force update with timestamp
    wp_enqueue_script('form-data-script', plugin_dir_url(__FILE__) . 'js/script.js', [], '3.4.1', true);
}
add_action('admin_enqueue_scripts', 'tablemanager_enqueue_assets');


function table_manager_enqueue_admin_scripts($hook) {
    if (strpos($hook, 'table_manager') === false) return;

    wp_register_script(
        'table-manager-admin-js',
        plugin_dir_url(__FILE__) . 'js/script.js',
        array('jquery'), // dependencies
        '1.0',
        true // in footer
    );
    wp_enqueue_script('table-manager-admin-js');

    // Optional: Add inline script
    $inline_js = "console.log('Inline JS loaded');";
    wp_add_inline_script('table-manager-admin-js', $inline_js);
}
add_action('admin_enqueue_scripts', 'table_manager_enqueue_admin_scripts');

function table_manager_enqueue_admin_styles($hook) {
    if (strpos($hook, 'table_manager') === false) return;

    wp_register_style(
        'table-manager-admin-css',
        plugin_dir_url(__FILE__) . 'css/style.css',
        array(),
        '1.0'
    );
    wp_enqueue_style('table-manager-admin-css');

    // Optional inline style
    $inline_css = '.tm-highlight { background: yellow; }';
    wp_add_inline_style('table-manager-admin-css', $inline_css);
}
add_action('admin_enqueue_scripts', 'table_manager_enqueue_admin_styles');

?>


