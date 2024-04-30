<?php

namespace StatamicRadPack\Meilisearch\Meilisearch;

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
}
