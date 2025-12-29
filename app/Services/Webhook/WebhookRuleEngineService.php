<?php

namespace App\Services\Webhook;

use App\Models\Webhook\WebhookSubscription;
use Illuminate\Support\Arr;

class WebhookRuleEngineService
{
    public function shouldDeliver(WebhookSubscription $subscription, array $payload): bool
    {
        $rules = $subscription->rules;

        if ($rules->isEmpty()) {
            return true;
        }

        foreach ($rules as $rule) {
            $result = $this->evaluateRule($rule, $payload);

            if ($rule->rule_type === 'all' && ! $result) {
                return false;
            }

            if ($rule->rule_type === 'any' && $result) {
                return true;
            }
        }

        return $rules->first()->rule_type !== 'any';
    }

    protected function evaluateRule($rule, array $payload): bool
    {
        $conditions = $rule->conditions;

        foreach ($conditions as $condition) {
            $field = $condition['field'];
            $operator = $condition['operator'];
            $expectedValue = $condition['value'];

            $actualValue = Arr::get($payload, $field);

            if (! $this->evaluateCondition($actualValue, $operator, $expectedValue)) {
                if ($rule->rule_type === 'all') {
                    return false;
                }
            } else {
                if ($rule->rule_type === 'any') {
                    return true;
                }
            }
        }

        return $rule->rule_type === 'all';
    }

    protected function evaluateCondition($actual, string $operator, $expected): bool
    {
        return match ($operator) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'contains' => is_string($actual) && str_contains($actual, $expected),
            'in' => in_array($actual, (array) $expected),
            'gt' => $actual > $expected,
            'gte' => $actual >= $expected,
            'lt' => $actual < $expected,
            'lte' => $actual <= $expected,
            default => false,
        };
    }

    public function transformPayload(WebhookSubscription $subscription, array $payload): array
    {
        $rules = $subscription->rules->first();

        if (! $rules || ! $rules->transform_template) {
            return $payload;
        }

        $template = $rules->transform_template;
        $transformed = [];

        foreach ($template as $targetKey => $sourcePath) {
            $transformed[$targetKey] = Arr::get($payload, $sourcePath);
        }

        return $transformed;
    }
}
