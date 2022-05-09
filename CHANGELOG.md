# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## v1.0.0 RC1 - 2022-05-09

In consultation with @tao, I have revised the adapter, and we present the first "stable" version.

### Added

- `reference` is automatically added to the index. (Statamic needs this to resolve the search results).
- The code will now be automatically formatted (php-cs-fixer).
- We added a basic test setup to ensure at least the packages can be installed together.

### Changed

- We no longer follow the MeiliSearch release cycle (the search client is used so superficially that we do not expect any breaking changes here).
- The ID of the search documents will now be transformed according to the MeiliSearch rules https://github.com/elvenstar/statamic-meilisearch/pull/5 .
- The property `collection` is no longer indexed by default. If you still need it, add this transformer to your search configuration:

```php
'tags' => [
    'driver' => 'meilisearch',
    'searchables' => ['taxonomy:tags'],
    'fields' => ['title', 'slug', 'collection'],
    'transformers' => [
        'collection' => fn($collection) => $collection?->handle(),
    ],
 ],

```
- The Package Service Provider has been renamed to `StatamicMeiliSearchServiceProvider`. If you have problems updating, you may have to remove the package with `composer remove elvenstar/statamic-meilisearch` and add it again with `composer require elvenstar/statamic-meilisearch`.

### Removed

- We have removed PHP support for versions older than 8.0.
- We have removed Laravel support for versions older than 8.0.

## [Unreleased]
