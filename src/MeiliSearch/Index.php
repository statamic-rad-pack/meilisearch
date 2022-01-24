<?php

namespace Elvenstar\StatamicMeiliSearch\MeiliSearch;

use MeiliSearch\Exceptions\ApiException;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;
use MeiliSearch\Client;

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
        $fields = $this->searchables()->fields($document);
        $this->getIndex()->updateDocuments([$fields]);
    }

    public function delete($document)
    {
        $this->getIndex()->deleteDocument($document->id());
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
            $this->handleMeiliSearchException($e, 'deleteIndex');
        }
    }

    protected function createIndex()
    {
        try {
            $this->client->createIndex($this->name, ['primaryKey' => 'id']);
            $this->getIndex()->updateSettings($this->config['settings'] ?? []);
        } catch (ApiException $e) {
            $this->handleMeiliSearchException($e, 'createIndex');
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

    public function searchUsingApi($query)
    {
        try {
            $searchResults = $this->getIndex()->search($query);
        } catch (\Exception $e) {
            $this->handleMeiliSearchException($e);
        }

        return collect($searchResults->getHits())->map(function ($hit) {
            $hit['reference'] = $hit['id'];

            return $hit;
        });
    }

    private function getIndex()
    {
        return $this->client->index($this->name);
    }

    private function getDefaultFields($entry)
    {
        $fields = ['id' => $entry->id()];

        $entries = collect(['Statamic\Entries\Entry']);
        if ($entries->contains(get_class($entry))) {
            $fields['collection'] = $entry->collectionHandle();
        }

        return $fields;
    }

    private function handleMeiliSearchException($e, $method)
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
}
