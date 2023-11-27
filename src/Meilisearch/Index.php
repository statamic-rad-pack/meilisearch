<?php

namespace StatamicRadPack\Meilisearch\Meilisearch;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use Statamic\Contracts\Search\Searchable;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;

class Index extends BaseIndex
{
    protected $client;

    public function __construct(Client $client, $name, array $config, string $locale = null)
    {
        $this->client = $client;

        parent::__construct($name, $config, $locale);
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

    public function searchUsingApi($query, array $options = ['hitsPerPage' => 1000000, 'showRankingScore' => true]): Collection
    {
        try {
            $searchResults = $this->getIndex()->search($query, $options);
        } catch (\Exception $e) {
            $this->handleMeilisearchException($e, 'searchUsingApi');
        }

        return collect($searchResults->getHits());
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

    /**
     * Custom error parsing for Meilisearch exceptions.
     */
    private function handleMeilisearchException($e, $method)
    {
        // Ignore if already created.
        if ($e->errorCode === 'index_already_exists' && $method === 'createIndex') {
            return true;
        }

        // Ignore if not found.
        if ($e->errorCode === 'index_not_found' && $method === 'deleteIndex') {
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
