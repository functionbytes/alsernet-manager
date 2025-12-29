<?php

namespace App\Library;

class HookManager
{
    protected array $hooks = [];

    public function register(string $name, callable $callback): void
    {
        if (! isset($this->hooks[$name])) {
            $this->hooks[$name] = [];
        }
        $this->hooks[$name][] = $callback;
    }

    public function registerIfEmpty(string $name, callable $callback): void
    {
        if (! isset($this->hooks[$name])) {
            $this->hooks[$name] = [$callback];
        }
    }

    public function execute(string $name, array $params = []): array
    {
        if (! isset($this->hooks[$name])) {
            return [];
        }

        $results = [];
        foreach ($this->hooks[$name] as $callback) {
            $results[] = call_user_func_array($callback, $params);
        }

        return $results;
    }

    public function isEmpty(string $name): bool
    {
        return ! isset($this->hooks[$name]) || empty($this->hooks[$name]);
    }
}
