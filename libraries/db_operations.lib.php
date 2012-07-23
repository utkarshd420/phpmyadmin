<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * set of functions with the operations section in pma
 *
 * @package PhpMyAdmin
 */

if (! defined('PHPMYADMIN')) {
    exit;
}

/**
 * Get HTML output for database comment
 * 
 * @return string $html_output
 */
function PMA_getHtmlForDatabaseComment()
{
    $html_output = '<div class="operations_half_width">'
        . '<form method="post" action="db_operations.php">'
        . PMA_generate_common_hidden_inputs($GLOBALS['db'])
        . '<fieldset>'
        . '<legend>';
    if ($GLOBALS['cfg']['PropertiesIconic']) {
        $html_output .= '<img class="icon ic_b_comment" src="themes/dot.gif" alt="" />';
    }
    $html_output .=  __('Database comment: ');
    $html_output .= '</legend>';
    $html_output .= '<input type="text" name="comment" class="textfield" size="30"'
        . 'value="' . htmlspecialchars(PMA_getDBComment($GLOBALS['db'])) . '" />'
        . '</fieldset>';
    $html_output .= '<fieldset class="tblFooters">'
        . '<input type="submit" value="' . __('Go') . '" />'
        . '</fieldset>'
        . '</form>'
        . '</div>';
    
    return $html_output;
}

/**
 * Get HTML output for rename database
 * 
 * @return string $html_output
 */
function PMA_getHtmlForRenameDatabase()
{
    $html_output = '<div class="operations_half_width">'
        . '<form id="rename_db_form" ' . ($GLOBALS['cfg']['AjaxEnable'] ? ' class="ajax" ' : '')
        . 'method="post" action="db_operations.php"'
        . 'onsubmit="return emptyFormElements(this, ' . "'newname'" . ')">';
    if (isset($_REQUEST['db_collation'])) {
        $html_output .= '<input type="hidden" name="db_collation" value="' . $_REQUEST['db_collation']
            .'" />' . "\n";
    }
    $html_output .= '<input type="hidden" name="what" value="data" />'
        . '<input type="hidden" name="db_rename" value="true" />'
        . PMA_generate_common_hidden_inputs($GLOBALS['db'])
        . '<fieldset>'
        . '<legend>';

    if ($GLOBALS['cfg']['PropertiesIconic']) {
        $html_output .= PMA_CommonFunctions::getInstance()->getImage('b_edit.png');
    }
    $html_output .= __('Rename database to') . ':'
        . '</legend>';
    
    $html_output .= '<input id="new_db_name" type="text" name="newname" ' 
        . 'size="30" class="textfield" value="" />'
        . '</fieldset>'
        . '<fieldset class="tblFooters">'
        . '<input id="rename_db_input" type="submit" value="' . __('Go') . '" />'
        . '</fieldset>'
        . '</form>'
        . '</div>';

    return $html_output;
}

/**
 * Get HTML for database drop link
 * 
 * @return string $html_output
 */
function PMA_getHtmlForDropDatabaseLink()
{
    $common_functions = PMA_CommonFunctions::getInstance();
    
    $this_sql_query = 'DROP DATABASE ' . $common_functions->backquote($GLOBALS['db']);
    $this_url_params = array(
            'sql_query' => $this_sql_query,
            'back' => 'db_operations.php',
            'goto' => 'main.php',
            'reload' => '1',
            'purge' => '1',
            'message_to_show' => sprintf(
                __('Database %s has been dropped.')
                , htmlspecialchars($common_functions->backquote($GLOBALS['db']))
            ),
            'db' => null,
        );
    
    $html_output = '<div class="operations_half_width">'
        . '<fieldset class="caution">';
    $html_output .= '<legend>';
    if ($GLOBALS['cfg']['PropertiesIconic']) {
        $html_output .= $common_functions->getImage('b_deltbl.png');
    }
    $html_output .= __('Remove database')
        . '</legend>';
    $html_output .= '<ul>';
    $html_output .= '<li>' 
        . '<a href="sql.php' . PMA_generate_common_url($this_url_params) . '"' 
        . ($GLOBALS['cfg']['AjaxEnable'] ? 'id="drop_db_anchor"' : '') . '>'
        . __('Drop the database (DROP)')
        . '</a>'
        .  $common_functions->showMySQLDocu('SQL-Syntax', 'DROP_DATABASE')
        . '</li>'
        . '</ul></fieldset>'
        . '</div>';
    
    return $html_output;
}

/**
 * Get HTML snippet for copy database
 * 
 * @return string $html_output
 */
function PMA_getHtmlForCopyDatabase()
{
    $drop_clause = 'DROP TABLE / DROP VIEW';
    $choices = array(
        'structure' => __('Structure only'),
        'data'      => __('Structure and data'),
        'dataonly'  => __('Data only')
    );
        
    if (isset($_COOKIE)
        && isset($_COOKIE['pma_switch_to_new'])
        && $_COOKIE['pma_switch_to_new'] == 'true'
    ) {
        $pma_switch_to_new = 'true';
    }
    
    $html_output = '<div class="operations_half_width clearfloat">';
    $html_output .= '<form id="copy_db_form" '
        . ($GLOBALS['cfg']['AjaxEnable'] ? ' class="ajax" ' : '')
        . 'method="post" action="db_operations.php"'
        . 'onsubmit="return emptyFormElements(this' . "'newname'" . ')">';
    
    if (isset($_REQUEST['db_collation'])) {
        $html_output .= '<input type="hidden" name="db_collation" ' 
        . 'value="' . $_REQUEST['db_collation'] .'" />' . "\n";
    }
    $html_output .= '<input type="hidden" name="db_copy" value="true" />' . "\n"
        . PMA_generate_common_hidden_inputs($GLOBALS['db']);
    $html_output .= '<fieldset>'
        . '<legend>';
    
    if ($GLOBALS['cfg']['PropertiesIconic']) {
        $html_output .= PMA_CommonFunctions::getInstance()->getImage('b_edit.png');
    }
    $html_output .= __('Copy database to') . ':'
        . '</legend>'
        . '<input type="text" name="newname" size="30" class="textfield" value="" /><br />'
        . PMA_CommonFunctions::getInstance()->getRadioFields(
            'what', $choices, 'data', true
        );
    $html_output .= '<input type="checkbox" name="create_database_before_copying" '
        . 'value="1" id="checkbox_create_database_before_copying"'
        . 'checked="checked" />';
    $html_output .= '<label for="checkbox_create_database_before_copying">'
        . __('CREATE DATABASE before copying') . '</label><br />';
    $html_output .= '<input type="checkbox" name="drop_if_exists" value="true"'
        . 'id="checkbox_drop" />';
    $html_output .= '<label for="checkbox_drop">'
        . sprintf(__('Add %s'), $drop_clause)
        . '</label><br />';
    $html_output .= '<input type="checkbox" name="sql_auto_increment" value="1" '
        . 'checked="checked" id="checkbox_auto_increment" />';
    $html_output .= '<label for="checkbox_auto_increment">'
        . __('Add AUTO_INCREMENT value') . '</label><br />';
    $html_output .= '<input type="checkbox" name="add_constraints" value="1"'
        . 'id="checkbox_constraints" />';
    $html_output .= '<label for="checkbox_constraints">'
        . __('Add constraints') . '</label><br />';
    $html_output .= '<input type="checkbox" name="switch_to_new" value="true"'
        . 'id="checkbox_switch"'
        . ((isset($pma_switch_to_new) && $pma_switch_to_new == 'true')
            ? ' checked="checked"' 
            : '')
        . '/>';
    $html_output .= '<label for="checkbox_switch">'
        . ('Switch to copied database') . '</label>'
        . '</fieldset>';
    $html_output .= '<fieldset class="tblFooters">'
        . '<input type="submit" name="submit_copy" value="' . __('Go') . '" />'
        . '</fieldset>'
        . '</form>'
        . '</div>';
    
    return $html_output;
}

/**
 * Get HTML snippet for change database charset
 * 
 * @return string $html_output
 */
function PMA_getHtmlForChangeDatabaseCharset()
{
    $html_output = '<div class="operations_half_width"><form id="change_db_charset_form" ';
    if ($GLOBALS['cfg']['AjaxEnable']) {
        $html_output .= ' class="ajax" ';
    }
    $html_output .= 'method="post" action="db_operations.php">';
    
    $html_output .= PMA_generate_common_hidden_inputs($GLOBALS['db'], $GLOBALS['table']);
    
    $html_output .= '<fieldset>' . "\n"
       . '    <legend>';
    if ($GLOBALS['cfg']['PropertiesIconic']) {
        $html_output .= PMA_CommonFunctions::getInstance()->getImage('s_asci.png');
    }
    $html_output .= '<label for="select_db_collation">' . __('Collation') . ':</label>' . "\n"
       . '</legend>' . "\n"
       . PMA_generateCharsetDropdownBox(
           PMA_CSDROPDOWN_COLLATION, 'db_collation',
           'select_db_collation',
           (isset ($_REQUEST['db_collation']) ? $_REQUEST['db_collation'] : ''),
           false, 3
       )
       . '</fieldset>'
       . '<fieldset class="tblFooters">'
       . '<input type="submit" name="submitcollation"'
       . ' value="' . __('Go') . '" />' . "\n"
       . '</fieldset>' . "\n"
       . '</form></div>' . "\n";
    
    return $html_output;
}

/**
 * Get HTML snippet for export relational schema view
 * 
 * @param string $url_query
 * 
 * @return string $html_output
 */
function PMA_getHtmlForExportRelationalSchemaView($url_query)
{
    $html_output = '<div class="operations_full_width">' 
        . '<fieldset><a href="schema_edit.php?' . $url_query . '">';
    if ($GLOBALS['cfg']['PropertiesIconic']) {
        $html_output .= PMA_CommonFunctions::getInstance()->getImage('b_edit.png');
    }
    $html_output .= __('Edit or export relational schema') 
        . '</a></fieldset>'
        . '</div>';
    
    return $html_output;
}

/**
 * Run the Procedure definitions and function definitions
 * 
 * to avoid selecting alternatively the current and new db
 * we would need to modify the CREATE definitions to qualify
 * the db name
 */
function PMA_runProcedureAndFunctionDefinitions()
{
    $procedure_names = PMA_DBI_get_procedures_or_functions($GLOBALS['db'], 'PROCEDURE');
    if ($procedure_names) {
        foreach ($procedure_names as $procedure_name) {
            PMA_DBI_select_db($GLOBALS['db']);
            $tmp_query = PMA_DBI_get_definition($GLOBALS['db'], 'PROCEDURE', $procedure_name);
            // collect for later display
            $GLOBALS['sql_query'] .= "\n" . $tmp_query;
            PMA_DBI_select_db($_REQUEST['newname']);
            PMA_DBI_query($tmp_query);
        }
    }

    $function_names = PMA_DBI_get_procedures_or_functions($GLOBALS['db'], 'FUNCTION');
    if ($function_names) {
        foreach ($function_names as $function_name) {
            PMA_DBI_select_db($GLOBALS['db']);
            $tmp_query = PMA_DBI_get_definition($GLOBALS['db'], 'FUNCTION', $function_name);
            // collect for later display
            $GLOBALS['sql_query'] .= "\n" . $tmp_query;
            PMA_DBI_select_db($_REQUEST['newname']);
            PMA_DBI_query($tmp_query);
        }
    }
}

/**
 * Get sql query and create database before copy
 * 
 * @return string $sql_query
 */
function PMA_getSqlQueryAndCreateDbBeforeCopy()
{
    // lower_case_table_names=1 `DB` becomes `db`
    if (! PMA_DRIZZLE) {
        $lower_case_table_names = PMA_DBI_fetch_value(
            'SHOW VARIABLES LIKE "lower_case_table_names"', 0, 1
        );
        if ($lower_case_table_names === '1') {
            $_REQUEST['newname'] = PMA_strtolower($_REQUEST['newname']);
        }
    }

    $local_query = 'CREATE DATABASE ' 
        . PMA_CommonFunctions::getInstance()->backquote($_REQUEST['newname']);
    if (isset($_REQUEST['db_collation'])) {
        $local_query .= ' DEFAULT' 
            . PMA_generateCharsetQueryPart($_REQUEST['db_collation']);
    }
    $local_query .= ';';
    $sql_query = $local_query;
    // save the original db name because Tracker.class.php which
    // may be called under PMA_DBI_query() changes $GLOBALS['db']
    // for some statements, one of which being CREATE DATABASE
    $original_db = $GLOBALS['db'];
    PMA_DBI_query($local_query);
    $GLOBALS['db'] = $original_db;

    // rebuild the database list because PMA_Table::moveCopy
    // checks in this list if the target db exists
    $GLOBALS['pma']->databases->build();
    
    return $sql_query;
}

?>
