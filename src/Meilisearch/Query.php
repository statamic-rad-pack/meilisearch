<?php

namespace StatamicRadPack\Meilisearch\Meilisearch;

use Statamic\Search\QueryBuilder;

class Query extends QueryBuilder
{
    public function getSearchResults($query)
    {
        $results = $this->index->searchUsingApi($query);

        return $results->map(function ($result, $i) {
            $result['search_score'] = (int) ceil($result['_rankingScore'] * 1000);

            return $result;
        });
    }
}
