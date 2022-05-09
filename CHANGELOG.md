# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 1.0.6 - 2022-05-09

### Changed

- Fix missing type hint for `Statamic\Taxonomies\Term`

**Full Changelog**: https://github.com/elvenstar/statamic-meilisearch/compare/1.0.5...1.0.6

## 1.0.5 - 2022-05-09

### Changed

Nothing! Just a new version to release changes for real (I forgot to push before I created the previous release 🤦‍♂️)

**Full Changelog**: https://github.com/elvenstar/statamic-meilisearch/compare/1.0.4...1.0.5

## 1.0.4 - 2022-05-09

### Changed

- Pass `reference` and not whole object to delete method.

**Full Changelog**: https://github.com/elvenstar/statamic-meilisearch/compare/1.0.3...1.0.4

## 1.0.3 - 2022-05-09

### Changed

Add compare url to new changelog updates

## 1.0.2 - 2022-05-09

### Changed

Use correct target branch for tag.

**Full Changelog**: https://github.com/elvenstar/statamic-meilisearch/compare/1.0.0...1.0.2

## 1.0.1 - 2022-05-09

### Changes

- Some follow up changes after using changelog updater

## 1.0.0 - 2022-05-09

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
