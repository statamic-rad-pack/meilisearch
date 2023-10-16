# Changelog

All notable changes to this project will be documented in this file.

## 2.0.1 - 2023-08-23

### What's Changed

- Remove paragraph about versioning system from readme by @lakkes-ra in https://github.com/statamic-rad-pack/mellisearch/pull/15
- Add support for Statamic 3.3

**Full Changelog**: https://github.com/statamic-rad-pack/mellisearch/compare/2.0.0...2.0.1

## 2.0.0 - 2023-06-20

### What's Changed

- Allow new version of `meilisearch/meilisearch-php` by @Z3d0X in https://github.com/statamic-rad-pack/mellisearch/pull/10
- Update for MS v1.0, Statamic 4.0 and Laravel 10 by @lakkes-ra in https://github.com/statamic-rad-pack/mellisearch/pull/13
- Update Authorization Header in README by @lakkes-ra in https://github.com/statamic-rad-pack/mellisearch/pull/12

### New Contributors

- @Z3d0X made their first contribution in https://github.com/statamic-rad-pack/mellisearch/pull/10
- @lakkes-ra made their first contribution in https://github.com/statamic-rad-pack/mellisearch/pull/13

**Full Changelog**: https://github.com/statamic-rad-pack/mellisearch/compare/1.1.0...2.0.0

## Allow latest Meilisearch client - 2022-08-02

### Changed

- Allow use of latest meilisearch/meilisearch-php `v0.24.*`

**Meilisearch 0.28 has a lot of breaking changes in its api. You may need to require an older Meilisearch Client if you use an older Version of Meilisearch.**

**Full Changelog**: https://github.com/statamic-rad-pack/mellisearch/compare/1.0.6...1.1.0

## 1.0.6 - 2022-05-09

### Changed

- Fix missing type hint for `Statamic\Taxonomies\Term`

**Full Changelog**: https://github.com/statamic-rad-pack/mellisearch/compare/1.0.5...1.0.6

## 1.0.5 - 2022-05-09

### Changed

Nothing! Just a new version to release changes for real (I forgot to push before I created the previous release 🤦‍♂️)

**Full Changelog**: https://github.com/statamic-rad-pack/mellisearch/compare/1.0.4...1.0.5

## 1.0.4 - 2022-05-09

### Changed

- Pass `reference` and not whole object to delete method.

**Full Changelog**: https://github.com/statamic-rad-pack/mellisearch/compare/1.0.3...1.0.4

## 1.0.3 - 2022-05-09

### Changed

Add compare url to new changelog updates

## 1.0.2 - 2022-05-09

### Changed

Use correct target branch for tag.

**Full Changelog**: https://github.com/statamic-rad-pack/mellisearch/compare/1.0.0...1.0.2

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
- The ID of the search documents will now be transformed according to the MeiliSearch rules https://github.com/statamic-rad-pack/mellisearch/pull/5 .
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
- The Package Service Provider has been renamed to `StatamicMeiliSearchServiceProvider`. If you have problems updating, you may have to remove the package with `composer remove statamic-rad-pack/mellisearch` and add it again with `composer require statamic-rad-pack/mellisearch`.

### Removed

- We have removed PHP support for versions older than 8.0.
- We have removed Laravel support for versions older than 8.0.
