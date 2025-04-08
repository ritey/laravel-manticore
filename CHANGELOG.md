All notable changes to this project will be documented in this file.

---

# Changelog

## [1.0.1] - 2025-04-01

### Added
- Graceful error handling if `manticoresoftware/manticoresearch-php` is missing or Manticore server is unreachable
- Exception handling for `update()`, `delete()`, and `search()` operations in `ManticoreEngine`
- Support for Laravel Scout required methods: `map()` and `getTotalCount()`
- Automatic configuration merge via `mergeConfigFrom()` in the service provider
- Config publishing via `vendor:publish --tag=manticore-config`

### Improved
- Safer integration with Laravel through better fallback mechanisms


## [1.0.0] - 2025-03-30

### Added
- Laravel Scout driver for Manticore Search
- Support for full-text search
- Support for vector search with `dotproduct`, `cosine`, `l2`
- JSON filtering with `FilterBuilder` helper
- Field boosting and hybrid queries
- Pagination using `from` + `size`
- Sorting on any field
- Artisan commands:
  - `manticore:create-index`
  - `manticore:sync-index`
- Automatic schema syncing with optional vector, float, text, and JSON types
- GitHub Actions CI (PHP 8.0–8.2)
- Documentation: `README.md`, `SETUP.md`, `EXAMPLES.md`
- MIT License, `.gitignore`, and `CONTRIBUTING.md`
- Config file: `config/manticore.php`
