# Elgg Data Adapters

![Elgg 2.3](https://img.shields.io/badge/Elgg-2.3-orange.svg?style=flat-square)

## Features

 * Provides extendable adapters for exporting entities into serializable format
 * Provides endpoints for retrieving entity information in JSON format
 * Make it easy to add new endpoints by simply adding resource views to `/resources/data/` in `json` viewtype
 * Integrates with hypeLists and allows searching, sorting and filtering via URL query parameters (e.g. `/data/members?query=Name&sort=alpha::asc`)
 * Convenience global endpoint for listing and searching through entities (e.g. `/data/list?types=object&subtypes=blog&metadata[status]=published&query=foo&sort=time_created::asc`)
 * Adds some endpoints for commonly used data
    * `/data/entity`
    * `/data/list`
    * `/data/members`
    * `/data/user/friends`
    * `/data/user/friends_of`
    
    
## Notes

### Endpoint accessibility

Note that `/data` endpoints can only be accessed using `elgg/Ajax` module. Endpoints are protected, and will only be accessible from the page that was generated for current session. That means your data will be safe from mining.

### Entity data

You can use `adapter:entity`,`$entity_type:$entity_subtype` or more generic `adapter:entity`,`$entity_type` hook to add more export data for an entity.