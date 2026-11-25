# Clean rebuild: duplicate-group root-cause investigation (2026-11-24)

## Verified upstream code path
- `routes/web.php` sends `/{uuid}/playlist.m3u` to `App\Http\Controllers\PlaylistGenerateController`.
- `PlaylistGenerateController::__invoke` resolves regular, merged and custom playlist types.
- In the streamed M3U output, a normal channel uses `channels.group` unless its linked group has `merged_group_name`; custom playlists instead use a custom tag name (`custom_group_name`).
- `getChannelQuery` joins `groups` and `parent_groups`, selecting `parent_groups.name as merged_group_name` and `COALESCE(parent_groups.sort_order, groups.sort_order)`. Standard ordering uses parent-group order, child-group order, then channel order.
- Custom playlists retrieve tag-based names and tag ordering. This is **not** evidence of a generic same-name group coalescing routine: custom tag grouping and standard group parent links are different mechanisms.

## Important implication
Merely sorting rows A–Z or changing the `group-title` string does not prove that Xtream/API category IDs and M3U output consistently coalesce duplicate group records. We must check the category endpoints and all output modes, not just the visual table.

## Next implementation steps
1. Trace merged-playlist group construction and API category output, then identify whether duplicates come from separate IDs, inconsistent effective names, or both.
2. Add regression fixtures with two sources each containing `USA Sports`, and a single source containing two identically named groups. Assert exactly one exported category and preservation of all channels in M3U and Xtream/API output.
3. Implement a shared effective-name grouping strategy for regular and merged playlists, preserving custom playlist behavior and explicit manual sort order.
4. Run tests before image publication. Do not change Portainer volumes or deploy an unverified image.

Status: source investigation only; no feature fix claimed.
