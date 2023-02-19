<?
namespace Plunch\Util\Table;
// a collection of methods to work with tables

function descheme_row(Array $row, Array $schema) {
    $value_by_key = fn ($map) => fn ($key) => $map[$key];
    return \array_map($value_by_key($row), $schema);
}

function descheme(Array $table, Array $schema) {
    return \array_map(fn ($row) => descheme_row($row, $schema), $table);
}

function scheme_row(Array $row, Array $schema) {
    $solo_kv = fn ($key, $value) => [$key => $value];
    return \array_merge(
        ...\array_map($solo_kv, $schema, $row)
    );
}

function scheme(Array $table, Array $schema) {
    return \array_map(fn ($row) => scheme_row($row, $schema), $table);
}

function schemap_row(callable $kv_func, Array $row, Array $schema) {
    $values = descheme_row($row, $schema);
    $casted = \array_map($kv_func, $schema, $values);
    return scheme_row($casted, $schema);
}

function schemap(callable $kv_func, Array $table, Array $schema) {
    return \array_map(
        fn ($x) => schemap_row($kv_func, $x, $schema), $table
    );
}

function shed_row(Array $row, Array $schema) {
    return scheme_row(descheme_row($row, $schema), $schema); 
}

function shed(Array $table, Array $schema) {
    return \array_map(
        fn ($x) => shed_row($x, $schema),
        $table
    );
}

function bring_schema_on_top(Array $table, Array $schema) {
    return [$schema, ...descheme($table, $schema)];
}

function to_string(Array $table) {
    $tabify = fn ($entry) => \implode("\t", $entry);
    return \implode("\n", \array_map($tabify, $table));
}
