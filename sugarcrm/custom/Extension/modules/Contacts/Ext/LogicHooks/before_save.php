<?php

$hook_version = 1;
if (!isset($hook_array)) {
    $hook_array = array();
}

$event = 'before_save';
if (!isset($hook_array[$event])) {
    $hook_array[$event] = array();
}

$hook_array[$event][] = array(
    count($hook_array[$event]),
    $event,
    'custom/modules/Contacts/ContactsHookImpl.php',
    'ContactsHookImpl',
    $event
);
