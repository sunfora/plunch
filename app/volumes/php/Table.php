<?
namespace Plunch\Util\Table;
// a collection of methods to work with tables

function descheme(Array $table, Array $schema) {    
    $value_by_key = fn ($map) => fn ($key) => $map[$key];
    $form_array = fn ($entry) => array_map($value_by_key($entry), $schema);
    return array_map($form_array, $table);
}

function scheme(Array $table, Array $schema) {
    $solo_kv = fn ($key, $value) => [$key => $value];
    $scheme_entry = fn ($entry) => array_merge(
        ...array_map($solo_kv, $schema, $entry)
    );
    return array_map($scheme_entry, $table);
}

function bring_schema_on_top(Array $table, Array $schema) {
    return [$schema, ...descheme($table, $schema)];
}

function to_string(Array $table) {
    $tabify = fn ($entry) => implode("\t", $entry);
    return implode("\n", array_map($tabify, $table));
}
