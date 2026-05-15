<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Support\Arr;

trait UppercasesInput
{
    abstract protected function uppercaseFields(): array;

    protected function prepareForValidation(): void
    {
        $payload = $this->all();

        foreach ($this->uppercaseFields() as $field) {
            if (! Arr::has($payload, $field)) {
                continue;
            }

            Arr::set($payload, $field, $this->uppercaseValue(Arr::get($payload, $field)));
        }

        $this->replace($payload);
    }

    protected function uppercaseValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return mb_strtoupper($value, 'UTF-8');
        }

        if (! is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->uppercaseValue($item);
        }

        return $value;
    }
}
