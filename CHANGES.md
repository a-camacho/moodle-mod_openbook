# moodle-mod_openbook

## Changes

### v5.2-r4

* 2026-06-10 - Improvement: Show user identity fields on the overrides page and in the user-selection dropdown, resolves #89.
* 2026-06-10 - Bugfix: Fix log report crash on openbook approval events (Undefined array key 'approval').

### v5.2-r3

* 2026-05-28 - Security: Hardening of file access control and download permission checks.
* 2026-05-28 - Bugfix: Correct context id and clean up overrides in openbook_delete_instance.
* 2026-05-28 - Bugfix: Privacy provider now exports the per-instance preferences actually stored.
* 2026-05-28 - Improvement: Remove dead and unused legacy code, and fix copy-pasted file headers.

### v5.2-r2

* 2026-04-28 - Bugfix: Fix 'No file submission' filter under PostgreSQL

### v5.2-r1

* 2026-04-01 - Bugfix: Exclude teacher files from student file counts, resolves #86
* 2026-04-01 - Bugfix: Fix upload form alignment, file type display and teacher restrictions, resolves #84.
* 2026-03-31 - Improvement: Removed PDFjs version from folder name and url, resolves #81.
* 2026-03-31 - Improvement: Hide empty sections in secure window and fix visual consistency, resolves #80.
* 2026-03-31 - Improvement: Upgrade PDF.js from 5.4.394 to 5.6.205, resolves #72.
* 2026-03-31 - Improvement: Use override duedate for time remaining calculation, resolves #76.
* 2026-02-21 - Improvement: Make lib/tests/db/plugin_checks_test.php pass, resolves #73.
* 2026-02-14 - Improvement: Make icons on allfilespage last column consistent with legend.

### v5.1-r2

* 2025-02-05 - Improvement: Activity chooser 5.1 activity information enhancements, resolves #67.
* 2025-12-10 - Bugfix: Duplicated, overlaying headings on the overview page, resolves #65.
* 2025-11-30 - Improvement: The opening and closing dates should not be pre-set in the form.
* 2025-11-27 - Improvement: Remove unused language strings, resolves #59.
