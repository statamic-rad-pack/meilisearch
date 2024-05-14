<?php

namespace StatamicRadPack\Meilisearch\Meilisearch;

use Illuminate\Support\Collection;
use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    /**
     * The options to be sent with the search request.
     * See https://www.meilisearch.com/docs/reference/api/search#body for available options.
     */
    public array $options = ['hitsPerPage' => 1000000, 'showRankingScore' => true];

    public function getSearchResults($query)
    {
        $results = $this->index->searchUsingApi($query, $this->options);

        return $results->map(function ($result, $i) {
            $result['search_score'] = (int) ceil($result['_rankingScore'] * 1000);

            return $result;
        });
    }

    public function orderBy($column, $direction = 'asc')
    {
        $this->options['sort'][] = "{$column}:{$direction}";

        return parent::orderBy($column, $direction);
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (! in_array($operator, ['=', '!=', '>', '>=', '<', '<='])) {
            $value = $operator;
            $operator = '=';
        }

        $indexOfLastWhere = array_key_last($this->options['filter'] ?? []);

        if ($boolean === 'and' or ($boolean === 'or' and $indexOfLastWhere === null)) {
            $this->options['filter'][] = "{$column} {$operator} {$this->escapeValue($value)}";
        } else {
            $this->options['filter'][$indexOfLastWhere] = "{$this->options['filter'][$indexOfLastWhere]} OR {$column} {$operator} {$this->escapeValue($value)}";
        }

        return $this;
    }

    public function whereIn($column, $values, $boolean = 'and')
    {
        $values = $this->prepareValues($values);

        $indexOfLastWhere = array_key_last($this->options['filter'] ?? []);

        if ($boolean === 'and' or ($boolean === 'or' and $indexOfLastWhere === null)) {
            $this->options['filter'][] = "{$column} IN [{$values}]";
        } else {
            $this->options['filter'][$indexOfLastWhere] = "{$this->options['filter'][$indexOfLastWhere]} OR {$column} IN [{$values}]";
        }

        return $this;
    }

    public function whereNotIn($column, $values, $boolean = 'and')
    {
        $values = $this->prepareValues($values);

        $indexOfLastWhere = array_key_last($this->options['filter'] ?? []);

        if ($boolean === 'and' or ($boolean === 'or' and $indexOfLastWhere === null)) {
            $this->options['filter'][] = "{$column} NOT IN [{$values}]";
        } else {
            $this->options['filter'][$indexOfLastWhere] = "{$this->options['filter'][$indexOfLastWhere]} OR {$column} NOT IN [{$values}]";
        }

        return $this;
    }

    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $indexOfLastWhere = array_key_last($this->options['filter'] ?? []);

        $operator = $not ? 'IS NOT NULL' : 'IS NULL';

        if ($boolean === 'and' or ($boolean === 'or' and $indexOfLastWhere === null)) {
            $this->options['filter'][] = "{$column} {$operator}";
        } else {
            $this->options['filter'][$indexOfLastWhere] = "{$this->options['filter'][$indexOfLastWhere]} OR {$column} {$operator}";
        }

        return $this;
    }

    public function whereBetween($column, $values, $boolean = 'and', $not = false)
    {
        $values = $this->prepareValues($values);

        $indexOfLastWhere = array_key_last($this->options['filter'] ?? []);

        $not = $not ? 'NOT ' : '';

        if ($boolean === 'and' or ($boolean === 'or' and $indexOfLastWhere === null)) {
            $this->options['filter'][] = "{$not}{$column} TO {$values}";
        } else {
            $this->options['filter'][$indexOfLastWhere] = "{$this->options['filter'][$indexOfLastWhere]} OR {$not}{$column} TO {$values}";
        }

        return $this;
    }

    protected function escapeValue($value)
    {
        if (is_string($value)) {
            $escapedValue = addslashes($value);

            return "'{$escapedValue}'";
        }

        return $value;
    }

    protected function prepareValues($values)
    {
        if ($values instanceof Collection) {
            $values = $values->map(fn ($value) => $this->escapeValue($value))->implode(', ');
        } else {
            $values = collect($values)->map(fn ($value) => $this->escapeValue($value))->implode(', ');
        }

        return $values;
    }
}
