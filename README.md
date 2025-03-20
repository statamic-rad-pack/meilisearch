# Statamic Meilisearch Driver

This addon provides a [Meilisearch](https://www.meilisearch.com/) search driver for Statamic sites.

## Requirements

* PHP 8.1+
* Laravel 10+
* Statamic 5
* Meilisearch 1.0+

### Installation

```bash
composer require statamic-rad-pack/meilisearch
```

Add the following variables to your env file:

```txt
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=
```

The master key is like a password, if you auto-deploy a Meilisearch server they will most likely provide you with keys. On localhost you can make up your own master key then use that to generate your private and public keys. You will need these keys for front-end clients:

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
window.meilisearch = new meilisearch({
    host: '{{ config:statamic:search:drivers:meilisearch:credentials:url }}',
    apiKey: '{{ config:statamic:search:drivers:meilisearch:credentials:search_api_key }}',
});
</script>
```

You can optionally publish the config file for this package using:

```
php artisan vendor:publish --tag=statamic-meilisearch-config
```

### Few words about Document IDs in meilisearch

When you index your Statamic Entries, the driver will always transform the ID. This is required because meilisearch only allows `id` to be a string containing alphanumeric characters (a-Z, 0-9), hyphens (-) and underscores (_). You can read more about this in the [meilisearch documentation](https://www.meilisearch.com/docs/learn/core_concepts/primary_key#invalid_document_id)

As an Entry, Asset, User or Taxonomy reference is a combination of the type, handle/container and ID separated with a `::` (e.g. assets::heros/human01.jpg, categories::cats) this could not be indexed by meilisearch.

As a Workaround, we take care add reference while indexing your entries automatically 🎉.

Internally Statamic will use `\Statamic\Facades\Data::find($reference)` to resolve the corresponding Statamic Entry, Asset, User or Taxonomy.

### Search Settings

Any additional settings you want to define per index can be included in the `statamic/search.php` config file. The settings will be updated when the index is created.

```php
'indexes' => [
    // articles
    'articles' => [
        'driver' => 'meilisearch',
        'searchables' => ['collection:articles'],
        'fields' => ['id', 'title', 'url', 'type', 'content', 'locale'],
        'settings' => [
          'filterableAttributes' => ['type', 'locale'],
        ],
    ],
    :
    :
],
```

You may include different types of settings in each index:

```php
'indexes' => [
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
    :
    :
],
```

### Search Pagination

By default we limit the `maxTotalHits` to 1000000, if you want to modify this or any other pagination settings on the index, specify a pagination key:

```php
'indexes' => [
    // articles
    'articles' => [
        'driver' => 'meilisearch',
        'searchables' => ['collection:articles'],
        'fields' => ['id', 'title', 'url', 'type', 'content', 'locale'],
        'pagination' => [
            'maxTotalHits' => 100,
        ],
    ],
    :
    :
],
```


### Extending

You can extend the drivers functionality (e.g. in order to customize calls to meilisearch) by creating a class that extends
`StatamicRadPack\meilisearch\meilisearch\Index` and instructing Laravel's [service container](https://laravel.com/docs/master/container#main-content) to use it:

```php
class MyIndex extends Index {
    // Your custom logic here
}
```

```php
// app/Providers/AppServiceProvider.php

$this->app->bind(\StatamicRadPack\meilisearch\meilisearch\Index::class, MyIndex::class);
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
