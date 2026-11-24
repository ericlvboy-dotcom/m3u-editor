# Playlist and EPG redesign — implementation contract

Development branch only. Do not deploy or alter the existing Docker stack.

## Source playlists
- Preserve unrelated existing settings and channel editing capabilities.
- Store a literal per-source group prefix; do not add separators or reapply it to processed names.
- Format only the original group-name portion: Keep Original, Title Case, UPPERCASE, Title Case with " - ", UPPERCASE with " - ", Title Case with " | ", UPPERCASE with " | ".
- Source group order: A–Z, Z–A, provider order. Export order must match UI selection.
- Keep channel ordering independent of group ordering. Inspect existing channel sorting and numbering first; reuse working behavior, fix conflicts only.
- Refresh from original source data and persist explicit manual overrides.

## Groups & Channels
- Preserve the existing section, controls, menus, and editing/sorting functionality as-is; do not redesign or remove its controls.
- Integrate with the new export-order engine without changing the visible Groups & Channels workflow.
- Viewing the page or saving unrelated fields must never silently change ordering.
- Explicitly saved sorting or manual ordering in Groups & Channels becomes the active order for the affected scope, until a newer explicit sorting action supersedes it.
- Preserve manual renames, channel edits, and other unrelated overrides regardless of later sorting changes.

## Merged playlists
- Build directly from selected sources, never by reimporting merged outputs.
- Merge groups whose final displayed names match exactly; retain all channels unless explicitly configured otherwise.
- Apply merged formatting to original group-name portions without altering literal source prefixes.
- Sort final groups independently (A–Z, Z–A, provider order) after merging and export in that order.
- Preserve deterministic channel ordering across source merges.
- Automatically rebuild affected output on source refresh, settings changes, or saved manual edits; avoid cycles and duplicate processing.

## Ordering precedence across sections
- Last **explicitly saved sorting action** wins for the affected playlist/group scope: Groups & Channels or Merged Playlist. Do not use timestamps of unrelated settings saves.
- Applying Merged Playlist A–Z/Z–A/provider sort after Groups & Channels ordering makes that merged group order active; a subsequent explicit Groups & Channels reorder supersedes it.
- Preserve independent channel sorting vs group sorting; a group sort must not erase channel order, and vice versa.
- Apply the chosen order in actual M3U export and numeric positions, not merely UI display. Keep manual edits and original source data intact.

## EPG
- Retain existing individual EPG ingestion and EPG merge engine.
- Add a direct EPG assignment selector to merged playlist settings, listing regular and merged EPGs.
- Distinguish attaching an EPG URL from channel-to-EPG mapping; prefer TVG ID matching, preserve valid manual matches, and flag unmatched channels.
- No need to assign merged EPG to each source. Prevent circular or duplicate mapping.

## Verification gates
1. Audit actual source code and existing schema; identify all legacy group/channel sorting and numbering interactions.
2. Write regression tests for prefixes, all seven formats, ordering, overrides, refresh idempotence, identical-name merging, channel order, and EPG assignment/mapping.
3. Verify generated M3U/XMLTV outputs, not only UI ordering.
4. Run quality/security checks and Docker build on development branch.
5. Test configuration compatibility and rollback; do not deploy until separately authorized.
