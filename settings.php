<?php

// Restore general settings
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    // Course Settings for restore
    $general_settings = array(
        'users' => 0,
        'user_files' => 0,
        'role_assignments' => 0,
        'activities' => 1,
        'blocks' => 1,
        'filters' => 1,
        'comments' => 0,
        'userscompletion' => 0,
        'logs' => 0,
        'grade_histories' => 0
    );

    $modules = $DB->get_records_menu('modules', null, 'id, name') +
            array(0 => 'section');

    $producer = function ($type, $default) {
        return function ($module) use ($type, $default) {
            return array("{$module}_{$type}" => $default);
        };
    };

    $flatmap = function ($in, $module) use ($producer) {
        $included = $producer('included', 1);
        $userinfo = $producer('userinfo', 0);
        return $in + $included($module) + $userinfo($module);
    };

    // Flat mapped the producer defaults with original modules
    $course_settings = array_reduce($modules, $flatmap, array());

    // Appropriate keys
    $_k = function ($key) { 
        return "simple_restore_{$key}";
    };

    $_s = function ($k, $a=null) {
        return get_string($k, 'block_simple_restore', $a);
    };

    // Start building the Admin screen.
    $settings->add(
        new admin_setting_heading(
            $_k('general'), $_s('general'), $_s('general_desc')
        )
    );

    foreach ($general_settings as $name => $default) {
        $str = $_s($name); 
        $settings->add(
            new admin_setting_configcheckbox($_k($name), $str, $str, $default)
        );
    }

    $settings->add(
        new admin_setting_heading(
            $_k('module'), $_s('module'), $_s('module_desc')
        )
    );

    foreach ($course_settings as $name => $default) {
        $data = explode('_', $name);
        $type = array_pop($data);
        $module = implode('_', $data);
        if ($module == 'section') {
            $module_name = $_s('section');
        } else {
            $module_name = get_string('pluginname', 'mod_'.$module);
        }
        $str = $_s('restore_'.$type, $module_name);
        $settings->add(
            new admin_setting_configcheckbox($_k($name), $str, $str, $default)
        );
    }
}
