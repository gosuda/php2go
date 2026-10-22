# Feature and SQL Inventory Format Draft v0

This is a tool-neutral draft format for human review, not an executable schema. Field names are illustrative identifiers; serialize them in whatever form the target project's tools read. Keep facts confirmed by execution distinct from inferences from source.

This document owns the shape of the Feature Inventory and SQL Inventory. [Database policy](../../sql-extract/references/database-policy.md) owns the rules behind each access plan, and the [workspace convention](../../migration-orchestrator/references/workspace-convention.md) owns identifier patterns and file locations.

## Feature Inventory

Register every observable entry point or background job in the denominator, even when its source is missing or execution is currently blocked. Record those gaps under `unexamined_areas` instead of dropping the feature.

```yaml
inventory_version: 0
source_revision: example-php-revision
runtime_environment:
  php_version: "8.1.27"
  sapi: php-fpm
  extensions: [mysqli, mbstring, gd, session]
  web_server: nginx
  database_engine: "MySQL 8.0.36"
  charset: utf8mb4
  collation: utf8mb4_general_ci
  sql_mode: NO_ENGINE_SUBSTITUTION
  timezone: Asia/Seoul
features:
  - feature_id: board.read
    confidence: confirmed_by_execution   # confirmed_by_execution | inferred_from_source
    entry_points:
      - type: http
        path: /bbs/board.php
        methods: [GET]
        rewrite_rule: "^/board/([a-zA-Z0-9_]+)/page/([0-9]+)$ -> /bbs/board.php?bo_table=$1&page=$2"
    source_locations:
      bootstrap: [common.php]
      primary: [bbs/board.php, bbs/list.php]
      templates: [skin/board/basic/list.skin.php]
    inputs:
      query_params: [bo_table, wr_id, page, sfl, stx, sst, sod]
      post_params: []
      request_precedence: "GP (POST overrides GET in $_REQUEST)"
      cookies: [PHPSESSID]
      headers: [User-Agent, Referer]
    state_boundaries:
      db_reads: [g5_board, g5_write_notice, g5_member]
      db_writes: [g5_write_notice]       # hit counter update when wr_id is present
      session_reads: [ss_mb_id, ss_view_notice_123]
      session_writes: [ss_view_notice_123]
      file_reads: ["data/file/notice/*"]
      file_writes: []
      external_effects: []
    auth_boundary:
      anonymous_allowed: true
      permission_check: "member.mb_level >= board.bo_list_level"
    semantic_risks:                      # catalog: php-edge-cases topic that explains the risk
      - location: "bbs/board.php:88"
        category: loose_comparison
        catalog: types
        detail: "empty($page) || $page < 1; page='0' or page='1abc' coerces via (int)$page"
      - location: "bbs/list.php:45"
        category: dynamic_table_identifier
        detail: "Queries ${g5['write_prefix']}${bo_table}; needs a trusted identifier registry"
    next_steps:
      contract: ready                    # ready | blocked: <reason>
      fixtures_needed: [anonymous page 2, private post present]
      adapters_needed: []
unexamined_areas:
  - path: "adm/shop_admin/*"
    reason: "Encrypted module not loaded in the test runtime"
```

## SQL Inventory

`php-analyze` records every discovered SQL call site and dynamic construction branch. `sql-extract` later fills `access_plan` for each site under the database policy. A site without an `access_plan` stays visible as unmapped.

```yaml
sql_inventory_version: 0
source_revision: example-php-revision
wrappers:
  - symbol: sql_query
    location: lib/common.lib.php:1520
    driver_call: mysqli_query
  - symbol: sql_fetch
    location: lib/common.lib.php:1545
    driver_call: mysqli_fetch_assoc
call_sites:
  - site_id: "bbs/board.php:54:default"
    feature_id: board.read
    wrapper: sql_fetch
    raw_pattern: "SELECT * FROM {$g5['board_table']} WHERE bo_table = '$bo_table'"
    dynamic_elements:
      table_identifier: "fixed by configuration (g5_board)"
      where_branches: [default]
      order_by_branches: []
    transaction_scope: autocommit
    access_plan:
      status: MAPPED                     # MAPPED | UNMAPPED | LIMITED (see limitation)
      query_source: static_file          # static_file | dynamic_function
      query_file: db/queries/board.sql
      go_function: store.GetBoardMeta
      call_shape: QueryRowContext
      identifier_registry: null
  - site_id: "bbs/list.php:112:no_search"
    feature_id: board.read
    wrapper: sql_query
    raw_pattern: "SELECT wr_id, wr_subject FROM {$write_table} WHERE wr_is_comment = 0 ORDER BY wr_num, wr_reply LIMIT $from_record, $page_rows"
    dynamic_elements:
      table_identifier: "per-board registry (g5_write_notice, g5_write_free)"
      where_branches: [no_search, search_subject, search_content]
      order_by_branches: [default_wr_num, custom_sst_asc, custom_sst_desc]
    transaction_scope: autocommit
    access_plan:
      status: MAPPED
      query_source: dynamic_function
      go_function: store.ListPosts
      call_shape: QueryContext
      fragment_sources: "fixed program fragments, validated sort allowlist"
      identifier_registry: "server-managed board registry"
```

`LIMITED` means the actual driver or database cannot reproduce the site as written; record the cause in a `limitation` field. It is distinct from the Evidence Protocol's `UNSUPPORTED` verdict, which describes a comparison tool's missing capability.
