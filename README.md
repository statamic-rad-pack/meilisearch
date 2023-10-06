# Statamic MeiliSearch Driver

### Few words about Document IDs in MeiliSearch

When you index your Statamic Entries, the driver will always transform the ID. This is required because MeiliSearch only allows `id` to be a string containing alphanumeric characters (a-Z, 0-9), hyphens (-) and underscores (_).
You can read more about this in the [MeiliSearch documentation](https://docs.meilisearch.com/reference/api/error_codes.html#invalid-document-id)

As an Entry, Asset, User or Taxonomy reference is a combination of the type, handle/container and ID separated with a `::` (e.g. assets::heros/human01.jpg, categories::cats) this could not be indexed by MeiliSearch.

As a Workaround, we take care add reference while indexing your entries automatically 🎉.

Internally Statamic will use `\Statamic\Facades\Data::find($reference)` to resolve the corresponding Statamic Entry, Asset, User or Taxonomy.

### Installation

```bash
composer require elvenstar/statamic-meilisearch
```

Add the following variables to your env file:

```txt
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=
```

The master key is like a password, if you auto-deploy a MeiliSearch server they will most likely provide you with keys. On localhost you can make up your own master key then use that to generate your private and public keys. You will need these keys for front-end clients:

```bash
# Export the key
$ export MEILISEARCH_KEY=AWESOMESAUCE

# Start the meilisearch server again
$ meilisearch

# Generate the keys
curl \
  -H 'Authorization: Bearer AWESOMESAUCE' \
  -X GET 'http://localhost:7700/keys'
```

Add the new driver to the `statamic/search.php` config file:

```php
    'drivers' => [

        // other drivers

        'meilisearch' => [
            'credentials' => [
                'url' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
                'secret' => env('MEILISEARCH_KEY', ''),
                // 'search_api_key' => env('MEILISEARCH_SEARCH_KEY')
            ],
        ],
    ],
```

You can optionally add `search_api_key` which makes it easier to call the key on the frontend javascript code:

```html
<script type="text/javascript">
window.meilisearch = new MeiliSearch({
    host: '{{ config:statamic:search:drivers:meilisearch:credentials:url }}',
    apiKey: '{{ config:statamic:search:drivers:meilisearch:credentials:search_api_key }}',
});
</script>
```

### Search Settings

Any additional settings you want to define per index can be included in the `statamic/search.php` config file. The settings will be updated when the index is created.

```php
// articles
'articles' => [
    'driver' => 'meilisearch',
    'searchables' => ['collection:articles'],
    'fields' => ['id', 'title', 'url', 'type', 'content', 'locale'],
    'settings' => [
      'filterableAttributes' => ['type', 'locale'],
    ],
],
```

You may include different types of settings in each index:

```php
'articles' => [
    'driver' => 'meilisearch',
    'searchables' => ['collection:articles'],
    'settings' => [
        'filterableAttributes' => ['type', 'country', 'locale'],
        'distinctAttribute' => 'thread',
        'stopWords' => ['the', 'of', 'to'],
        'sortableAttributes' => ['timestamp'],
        'rankingRules' => [
          'sort',
          'words',
          'typo',
          'proximity',
          'attribute',
          'exactness',
        ],
    ],
 ],
```

### Extending

You can extend the drivers functionality (e.g. in order to customize calls to meilisearch) by creating a class that extends
`Elvenstar\StatamicMeiliSearch\MeiliSearch\Index` and instructing the DI-container to use it:

```php
class MyIndex extends Index {
    // Your custom logic here
}
```

```php
// In your ServiceProvider
$this->app->bind(\Elvenstar\StatamicMeiliSearch\MeiliSearch\Index::class, MyIndex::class);
```

### Common Errors

#### 413 Request Entity Too Large

You may encounter this bug on Laravel Forge for example, when you try sync the search documents for the first time. To overcome this you need to update the upload size limit in nginx.

Add `client_max_body_size` to the http section on `/etc/nginx/nginx.conf`:

```
http {

  client_max_body_size 100M;

  // other settings

}
```

Then restart the server, or run `sudo service nginx restart`.

### Quirks

MeiliSearch can only index 1000 words... which isn't so great for long markdown articles.

#### Update
As of version 0.24.0 the 1000 word limit [no longer exists](https://github.com/meilisearch/MeiliSearch/issues/1770) on documents, which makes the driver a lot more suited for longer markdown files you may use on Statamic.

#### Solution 1
On earlier versions, you can overcome this by breaking the content into smaller chunks:

```php
'articles' => [
    'driver' => 'meilisearch',
    'searchables' => ['collection:articles'],
    'fields' => ['id', 'title', 'locale', 'url', 'date', 'type', 'content'],
    'transformers' => [
        'date' => function ($date) {
            return $date->format('F jS, Y'); // February 22nd, 2020
        },
        'content' => function ($content) {
            // determine the number of 900 word sections to break the content field into
            $segments = ceil(Str::wordCount($content) / 900);

            // count the total content length and figure out how many characters each segment needs
            $charactersLimit = ceil(Str::length($content) / $segments);

            // now create X segments of Y characters
            // the goal is to create many segments with only ~900 words each
            $output = str_split($content, $charactersLimit);

            $response = [];
            foreach ($output as $key => $segment) {
                 $response["content_{$key}"] = utf8_encode($segment);
            }

            return $response;
      }
    ],
],
```

This will create a few extra fields like `content_1`, `content_2`, ... `content_12`, etc. When you perform a search it'll still search through all of them and return the most relevant result, but it's not possible to show highlights anymore for matching words on the javascript client. You'll have trouble figuring out if you should show `content_1` or `content_8` highlights. So if you go this route, make sure each entry has a synopsis you could show instead of highlights. I wouldn't recommend it at the moment.


#### Solution 2
If you need a lot more fine-grained control, and need to break content down into paragraphs or even sentences. You could use a artisan command to parse the entries in a Statamic collection, split the content and store it in a database. Then sync the individual items to MeiliSearch using the `php artisan scout:import` command.

1. Create a new database migration (make sure the migration has an origin UUID so you can link them to the parent entry)
2. Create a new Model and add the `searchables` trait from Scout.
3. Create an artisan command to parse all the entries and bulk import existing ones

```php
private function parseAll()
    {
        // disable search
        Articles::withoutSyncingToSearch(function () {
            // process all
            $transcripts = Entry::query()
                ->where('collection', 'articles')
                ->where('published', true)
                ->get()
                ->each(function ($entry) {
                    // push individual paragraphs or sentences to a collection
                    $segments = $entries->customSplitMethod();

                    $segments->each(function ($data) {
                        try {
                            $article = new Article($data);
                            $article->save();
                        } catch (\Illuminate\Database\QueryException $e) {
                            dd($e);
                        }
                    });
                });
        });

        $total = Article::all()->count();
        $this->info("Imported {$total} entries into the articles index.");

        $this->info("Bulk import the records with: ");
        $this->info("php artisan scout:import \"App\Models\Article\" --chunk=100");
    }
```

4. Add some Listeners to the EventServiceProvider to watch for update or delete events on the collection (to keep it in sync)

```php
    protected $listen = [
        'Statamic\Events\EntrySaved' => [
            'App\Listeners\ScoutArticleUpdated',
        ],
        'Statamic\Events\EntryDeleted' => [
            'App\Listeners\ScoutArticleDeleted',
        ],
    ];
```

4. Create the Event Listeners, for example:

```php
    public function handle(EntryDeleted $event)
    {
        if ($event->entry->collectionHandle() !== 'articles') return;

        // get the ID of the original transcript
        $id = $event->entry->id();

        // delete all from Scout with this origin ID
        $paragraphs = Article::where('origin', $id);
        $paragraphs->unsearchable();
        $paragraphs->delete();
    }

    public function handle(EntrySaved $event)
    {
        // ... same as above ...

        // if state:published
        if (!$event->entry->published()) return;

        // TODO: split $event->entry into paragraphs again and save them to the database,
        // they will re-sync automatically with the Searchables Trait.
    }
```

5. Create a placeholder, or empty index into the search config so you can create the index on MeiliSearch before importing the existing entries

```php
        // required as a placeholder where we store the paragraphs later
        'articles' => [
            'driver' => 'meilisearch',
            'searchables' => [], // empty
            'settings' => [
                'filterableAttributes' => ['type', 'entity', 'locale'],
                'distinctAttribute' => 'origin', // if you only want to return one result per entry
                // any search settings
            ],
         ],
```
