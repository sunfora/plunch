<?
namespace Plunch\CRUD;

interface Explains {
    public function explain(\Throwable $e, ...$args): ?string;
}
