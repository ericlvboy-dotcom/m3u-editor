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
- Preserve manual channel/group editing and manual channel positioning.
- Viewing this page or saving unrelated fields must never silently override source sorting.
- Remove only redundant/conflicting automatic group prefix/format/sort/merge controls.
- Define explicit manual override precedence and support clearing overrides.

## Merged playlists
- Build directly from selected sources, never by reimporting merged outputs.
- Merge groups whose final displayed names match exactly; retain all channels unless explicitly configured otherwise.
- Apply merged formatting to original group-name portions without altering literal source prefixes.
- Sort final groups independently (A–Z, Z–A, provider order) after merging and export in that order.
- Preserve deterministic channel ordering across source merges.
- Automatically rebuild affected output on source refresh, settings changes, or saved manual edits; avoid cycles and duplicate processing.

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
