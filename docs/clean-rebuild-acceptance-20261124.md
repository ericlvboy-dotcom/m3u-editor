# Clean M3U Editor rebuild — acceptance checklist

Base: repository master at 7692d8e497a31dc36ca1437726ad9fc8599714a3. Do not cherry-pick previous feature branch changes. Verify master against sparkison upstream before implementing.

- [ ] Source group prefixes: add, edit, replace, remove, bulk remove.
- [ ] Remove Channel Auto Sort, Group Auto Sort, Category Auto Sort, Sort by Channel Number from regular Playlist Output and disable conflicting processing; retain manual ordering. New Processing Sort Alpha Configs default off.
- [ ] Export exactly one group per identical final group title in regular and merged playlists; preserve all channels; test same-source, cross-source and post-sync cases. Inspect Custom Playlist group handling as reference.
- [ ] Show merged playlists in Manage Live Groups, with editable group sort-order values.
- [ ] Add Save Displayed Order for regular and merged playlists: visual A–Z/Z–A sort, explicit save to sequential group positions, subsequent manual overrides, scoped to selected playlist, persistent through sync.
- [ ] Independently format group and channel names: original, uppercase, title case, lowercase. Format group names before coalescing.
- [ ] Verify generated M3U output, not just UI, including on regeneration.
- [ ] Add regression tests and run relevant checks before image publication.
- [ ] Build/publish unique GHCR tag, verify digest and provide Portainer update without deleting volumes.

No channel renumbering or channel deduplication requested. Do not deploy or claim success until tests and image are verified.
