# Statamic MeiliSearch Driver

***Disclaimer: It's a dev release. I would not recommend using this in production until more testing has been done.***

### Installation

```bash
composer require elvenstar/statamic-meilisearch
```

Add the following variables to your env file:

```txt
MEILI_SEARCH_URL=http://127.0.0.1:7700
MEILI_MASTER_KEY=
```

The master key is like a password, if you auto-deploy a MeiliSearch server they will most likely provide you with keys. On localhost you can make up your own master key then use that to generate your private and public keys. You will need these keys for front-end clients:

```bash
# Export the key
$ export MEILI_MASTER_KEY=AWESOMESAUCE

# Start the meilisearch server again
$ meilisearch

# Generate the keys
$ curl \
  -H "X-Meili-API-Key: AWESOMESAUCE" \
  -X GET 'http://localhost:7700/keys'
```

Add the new driver to the `statamic.search` config file:

```php
    'drivers' => [
        
        // other drivers
        
        'meilisearch' => [
            'credentials' => [
                'url' => env('MEILI_SEARCH_URL', 'http://localhost:7700'),
                'secret' => env('MEILI_MASTER_KEY', ''),
            ],
        ],
    ],
```

### Quirks

MeiliSearch can only index 1000 words... which isn't so great for long markdown articles. You can overcome this by breaking the content into smaller chunks:

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

