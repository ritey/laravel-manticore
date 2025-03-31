<?php

namespace Ritey\LaravelManticore;

class FilterBuilder
{
    protected array $filters = [];

    /**
     * Add an equals filter.
     *
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function where(string $field, $value): static
    {
        $this->filters[] = ['equals' => [$field => $value]];
        return $this;
    }

    /**
     * Add an IN filter.
     *
     * @param string $field
     * @param array $values
     * @return static
     */
    public function whereIn(string $field, array $values): static
    {
        $this->filters[] = ['in' => [$field => $values]];
        return $this;
    }

    /**
     * Add a NOT equals filter.
     *
     * @param string $field
     * @param mixed $value
     * @return static
     */
    public function whereNot(string $field, $value): static
    {
        $this->filters[] = ['not' => ['equals' => [$field => $value]]];
        return $this;
    }

    /**
     * Add a range filter.
     *
     * @param string $field
     * @param array $range
     * @return static
     */
    public function whereRange(string $field, array $range): static
    {
        $this->filters[] = ['range' => [$field => $range]];
        return $this;
    }

    /**
     * Retrieve the built filters.
     *
     * @return array
     */
    public function get(): array
    {
        return $this->filters;
    }
}
