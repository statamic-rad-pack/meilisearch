# Changelog

All notable changes to this project will be documented in this file.

## v3.3.0 - 2024-05-14

### What's Changed

* Community Health Files by @duncanmcclean in https://github.com/statamic-rad-pack/meilisearch/pull/34
* Add Statamic 5 support by @godismyjudge95 in https://github.com/statamic-rad-pack/meilisearch/pull/38

### New Contributors

* @godismyjudge95 made their first contribution in https://github.com/statamic-rad-pack/meilisearch/pull/38

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/v3.2.1...v3.3.0

## v3.2.1 - 2023-12-18

### What's Changed

* Fixed inserting single documents by @naabster in https://github.com/statamic-rad-pack/meilisearch/pull/33

### New Contributors

* @naabster made their first contribution in https://github.com/statamic-rad-pack/meilisearch/pull/33

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/v3.2.0...v3.2.1

## v3.2.0 - 2023-12-14

### What's Changed

* Use lazy indexing/updating for performance and reduced memory usage by @ryanmitchell in https://github.com/statamic-rad-pack/meilisearch/pull/31

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/v3.1.0...v3.2.0

## v3.1.0 (2023-11-27)

### What's new

* Site based indexes are now supported #28 #29 by @ryanmitchell

## v3.0.3 (2023-11-02)

### What's improved

* Results will now be sorted using the search score from Meilisearch #26 by @robbanl

## v3.0.2 (2023-10-17)

### What's new

* You can now use `maxTotalHits` and `hitsPerPage` options when configuring indexes #24 by @ryanmitchell

## v3.0.1 (2023-10-17)

### What's fixed

* Fixed typo in the package name #23 by @ryanmitchell

## v3.0.0 (2023-10-16)

The meilisearch addon is now part of [The Rad Pack](https://github.com/statamic-rad-pack). As part of this, please run the following commands when upgrading:

1. `composer remove elvenstar/statamic-meilisearch`
2. `composer require statamic-rad-pack/meilisearch`

### What's new

* You can now extend the `Index` class #20 by @j6s

### What's fixed

* Use getSearchReference method instead of reference #18 by @duncanmcclean
* The `Searchable` interface is now used in place of concrete implementations #19 by @j6s

### Breaking changes

* This addon *only* supports Statamic 4 now
* The package name has changed to `statamic-rad-pack/meilisearch`

## 2.0.1 - 2023-08-23

### What's Changed

- Remove paragraph about versioning system from readme by @lakkes-ra in https://github.com/statamic-rad-pack/meilisearch/pull/15
- Add support for Statamic 3.3

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/2.0.0...2.0.1

## 2.0.0 - 2023-06-20

### What's Changed

- Allow new version of `meilisearch/meilisearch-php` by @Z3d0X in https://github.com/statamic-rad-pack/meilisearch/pull/10
- Update for MS v1.0, Statamic 4.0 and Laravel 10 by @lakkes-ra in https://github.com/statamic-rad-pack/meilisearch/pull/13
- Update Authorization Header in README by @lakkes-ra in https://github.com/statamic-rad-pack/meilisearch/pull/12

### New Contributors

- @Z3d0X made their first contribution in https://github.com/statamic-rad-pack/meilisearch/pull/10
- @lakkes-ra made their first contribution in https://github.com/statamic-rad-pack/meilisearch/pull/13

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/1.1.0...2.0.0

## Allow latest meilisearch client - 2022-08-02

### Changed

- Allow use of latest meilisearch/meilisearch-php `v0.24.*`

**meilisearch 0.28 has a lot of breaking changes in its api. You may need to require an older meilisearch Client if you use an older Version of meilisearch.**

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/1.0.6...1.1.0

## 1.0.6 - 2022-05-09

### Changed

- Fix missing type hint for `Statamic\Taxonomies\Term`

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/1.0.5...1.0.6

## 1.0.5 - 2022-05-09

### Changed

Nothing! Just a new version to release changes for real (I forgot to push before I created the previous release 🤦‍♂️)

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/1.0.4...1.0.5

## 1.0.4 - 2022-05-09

### Changed

- Pass `reference` and not whole object to delete method.

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/1.0.3...1.0.4

## 1.0.3 - 2022-05-09

### Changed

Add compare url to new changelog updates

## 1.0.2 - 2022-05-09

### Changed

Use correct target branch for tag.

**Full Changelog**: https://github.com/statamic-rad-pack/meilisearch/compare/1.0.0...1.0.2

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

- We no longer follow the meilisearch release cycle (the search client is used so superficially that we do not expect any breaking changes here).
- The ID of the search documents will now be transformed according to the meilisearch rules https://github.com/statamic-rad-pack/meilisearch/pull/5 .
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
- The Package Service Provider has been renamed to `StatamicmeilisearchServiceProvider`. If you have problems updating, you may have to remove the package with `composer remove statamic-rad-pack/meilisearch` and add it again with `composer require statamic-rad-pack/meilisearch`.

### Removed

- We have removed PHP support for versions older than 8.0.
- We have removed Laravel support for versions older than 8.0.
