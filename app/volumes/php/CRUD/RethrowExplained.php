<?
namespace Plunch\CRUD;

trait RethrowExplained {
    protected function rethrow_explained(callable $fn, callable $error_maker, ...$args) {
        try {
            return $fn(...$args);
        } catch (\Throwable $e) {
            $explanation = $this->explain($e, ...$args);
            if (is_string($explanation)) {
                throw $error_maker($explanation);
            } else {
                throw $e;
            }
        }
    }
}
