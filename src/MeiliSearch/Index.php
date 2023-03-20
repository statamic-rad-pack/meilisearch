<?php

namespace Elvenstar\StatamicMeiliSearch\MeiliSearch;

use Illuminate\Support\Str;
use MeiliSearch\Client;
use MeiliSearch\Exceptions\ApiException;
use Statamic\Assets\Asset;
use Statamic\Auth\User;
use Statamic\Entries\Entry;
use Statamic\Search\Documents;
use Statamic\Search\Index as BaseIndex;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Term;

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
        $this->getIndex()->deleteDocument($this->getSafeDocmentID($document->reference()));
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
            $this->handleMeiliSearchException($e, 'deleteIndex');
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

    public function searchUsingApi($query, $filters = [], $options = [])
    {
        try {
            $searchResults = $this->getIndex()->search($query, $filters, $options);
        } catch (\Exception $e) {
            $this->handleMeiliSearchException($e, 'searchUsingApi');
        }

        return collect($searchResults->getHits());
    }

    private function getIndex()
    {
        return $this->client->index($this->name);
    }

    private function getDefaultFields(Entry|Term|LocalizedTerm|Asset|User $entry)
    {
        return [
            'id' => $this->getSafeDocmentID($entry->reference()),
            'reference' => $entry->reference(),
        ];
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

    /**
     * Get the document ID for the given entry.
     * As a document id is only allowed to be an integer or string composed only of alphanumeric characters (a-z A-Z 0-9), hyphens (-), and underscores (_) we need to make sure that the ID is safe to use.
     * More under https://docs.meilisearch.com/reference/api/error_codes.html#invalid-document-id
     *
     * @param string $entryReference
     * @return string
     */
    private function getSafeDocmentID(string $entryReference)
    {
        return Str::of($entryReference)
            ->explode('::')
            ->map(function ($part) {
                return Str::slug($part);
            })
            ->implode('---');
    }
}
