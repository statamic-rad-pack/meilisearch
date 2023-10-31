<?php

namespace StatamicRadPack\Meilisearch\Meilisearch;

use Illuminate\Support\Str;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;

class Index extends BaseIndex
{
    protected $client;

    public function __construct(Client $client, $name, $config)
    {
        $this->client = $client;

        parent::__construct($name, $config);
    }

    public function search($query)
    {
        return (new Query($this))->query($query);
    }

    public function insert($document)
    {
        $fields = array_merge(
            $this->searchables()->fields($document),
            $this->getDefaultFields($document),
        );
        $this->getIndex()->updateDocuments([$fields]);
    }

    public function delete($document)
    {
        $this->getIndex()->deleteDocument($this->getSafeDocumentID($document->getSearchReference()));
    }

    public function exists()
    {
        try {
            $this->getIndex()->fetchRawInfo();

            return true;
        } catch (ApiException $e) {
            return false;
        }
    }

    protected function insertDocuments(Documents $documents)
    {
        try {
            if ($documents->isEmpty()) {
                return true;
            }

            return $this->getIndex()->updateDocuments($documents->all());
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    protected function deleteIndex()
    {
        try {
            $this->getIndex()->delete();
        } catch (ApiException $e) {
            $this->handlemeilisearchException($e, 'deleteIndex');
        }
    }

    protected function createIndex()
    {
        try {
            $this->client->createIndex($this->name, ['primaryKey' => 'id']);

            if (! isset($this->config['settings'])) {
                return;
            }

            $this->getIndex()->updateSettings($this->config['settings']);
            $this->getIndex()->updatePagination($this->config['pagination'] ?? ['maxTotalHits' => 1000000]);
        } catch (ApiException $e) {
            $this->handlemeilisearchException($e, 'createIndex');
        }
    }

    public function update()
    {
        $this->deleteIndex();
        $this->createIndex();

        // Prepare documents for update
        $searchables = $this->searchables()->all()->map(function ($entry) {
            return array_merge(
                $this->searchables()->fields($entry),
                $this->getDefaultFields($entry),
            );
        });

        // Update documents
        $documents = new Documents($searchables);
        $this->insertDocuments($documents);

        return $this;
    }

    public function searchUsingApi($query, $options = ['hitsPerPage' => 1000000, 'showRankingScore' => true])
    {
        try {
            $searchResults = $this->getIndex()->search($query, $options);
        } catch (\Exception $e) {
            $this->handlemeilisearchException($e, 'searchUsingApi');
        }

        collect($searchResults->getHits())->map(function ($hit) {
            $hit['search_score'] = (int) ceil($hit['_rankingScore'] * 1000);
            unset($hit['_rankingScore']);

            return $hit;
        });
    }

    private function getIndex()
    {
        return $this->client->index($this->name);
    }

    private function getDefaultFields(Searchable $entry): array
    {
        return [
            'id' => $this->getSafeDocumentID($entry->getSearchReference()),
            'reference' => $entry->getSearchReference(),
        ];
    }

    private function handlemeilisearchException($e, $method)
    {
        // custom error parsing for meilisearch exceptions
        if ($e->errorCode === 'index_already_exists' && $method === 'createIndex') {
            // ignore if already created
            return true;
        }

        if ($e->errorCode === 'index_not_found' && $method === 'deleteIndex') {
            // ignore if not found
            return true;
        }

        throw $e;
    }

    /**
     * Get the document ID for the given entry.
     * As a document id is only allowed to be an integer or string composed only of alphanumeric characters (a-z A-Z 0-9), hyphens (-), and underscores (_) we need to make sure that the ID is safe to use.
     * More under https://docs.meilisearch.com/reference/api/error_codes.html#invalid-document-id
     *
     * @return string
     */
    private function getSafeDocumentID(string $entryReference)
    {
        return Str::of($entryReference)
            ->explode('::')
            ->map(function ($part) {
                return Str::slug($part);
            })
            ->implode('---');
    }
}
