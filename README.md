# Elgg Adapters

![Elgg 2.3](https://img.shields.io/badge/Elgg-2.3-orange.svg?style=flat-square)

## Features

 * Provides extendable adapters for exporting entities into serializable format
 * Provides endpoints for retrieving entity information in JSON format
 * Make it easy to add new endpoints by simply adding resource views to `/resources/data/`
 * Integrates with hypeLists and allows searching, sorting and filtering via query parameters when access endpoint URLs (e.g. `/data/members?query=Name&sort=alpha::asc`)
 * Convenince endpoint for listing and searching through entities (e.g. `/data/list?type=object&subtype=blog&metadata[status]=published&query=foo&sort=time_created::asc`)