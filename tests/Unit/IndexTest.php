<?php

namespace StatamicRadPack\Meilisearch\Tests\Unit;

use Meilisearch\Client;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades;
use StatamicRadPack\Meilisearch\Tests\TestCase;

class IndexTest extends TestCase
{
    #[Test]
    public function it_sets_up_the_client_correctly()
    {
        $index = Facades\Search::index('meilisearch_index');

        $this->assertInstanceOf(Client::class, $index->client());
    }

    #[Test]
    public function it_adds_documents_to_the_index()
    {
        $collection = Facades\Collection::make()
            ->handle('pages')
            ->title('Pages')
            ->save();

        $entry1 = Facades\Entry::make()
            ->id('test-2')
            ->collection('pages')
            ->data(['title' => 'Entry 1'])
            ->save();

        $entry2 = Facades\Entry::make()
            ->id('test-1')
            ->collection('pages')
            ->data(['title' => 'Entry 2'])
            ->save();

        sleep(1); // give meili some time to process

        $index = Facades\Search::index('meilisearch_index');

        $this->assertCount(2, $index->searchUsingApi('Entry'));
    }

    #[Test]
    public function it_updates_documents_to_the_index()
    {
        $collection = Facades\Collection::make()
            ->handle('pages')
            ->title('Pages')
            ->save();

        $entry1 = Facades\Entry::make()
            ->id('test-2')
            ->collection('pages')
            ->data(['title' => 'Entry 1'])
            ->save();

        $entry2 = tap(Facades\Entry::make()
            ->id('test-1')
            ->collection('pages')
            ->data(['title' => 'Entry 2']))
            ->save();

        sleep(1); // give meili some time to process

        $index = Facades\Search::index('meilisearch_index');

        $results = collect($index->searchUsingApi('Entry'))->pluck('title');

        $this->assertContains('Entry 1', $results);
        $this->assertContains('Entry 2', $results);

        $entry2->merge(['title' => 'Entry 2 Updated'])->save();

        sleep(1); // give meili some time to process

        $results = collect($index->searchUsingApi('Entry'))->pluck('title');

        $this->assertContains('Entry 2 Updated', $results);
    }

    #[Test]
    public function it_removes_documents_from_the_index()
    {
        $collection = Facades\Collection::make()
            ->handle('pages')
            ->title('Pages')
            ->save();

        $entry1 = Facades\Entry::make()
            ->id('test-2')
            ->collection('pages')
            ->data(['title' => 'Entry 1'])
            ->save();

        $entry2 = tap(Facades\Entry::make()
            ->id('test-1')
            ->collection('pages')
            ->data(['title' => 'Entry 2']))
            ->save();

        $entry2->delete();

        $index = Facades\Search::index('meilisearch_index');

        sleep(1); // give meili some time to process

        $this->assertCount(1, $index->searchUsingApi('Entry'));
    }
}
