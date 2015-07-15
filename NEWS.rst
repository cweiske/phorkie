Version 0.6.1 - 2015-07-15
--------------------------
* Fix crash when renaming file
* Fix jumping to file anchor after file rename


Version 0.6.0 - 2015-07-08
--------------------------
* Add simple cache for rendered files
* Add "add file" button to display page
* Add jumping to file after saving from single file edit mode
* Adjust "additional options" layout and position
* Fix autologin reload button; it reloads the current page now
* Remove OpenID provider buttons


Version 0.5.0 - 2015-01-29
--------------------------
* Implement paste embedding via JavaScript
* Implement paste embedding via oEmbed
* Implement single file editing
* Implement automatic login
* Implement Elasticsearch 1.3 compatibility
* Work around PHP bug #68347 to parse ini files correctly
* Move clone URLs to sidebar
* Move additional button into text field


Version 0.4.0 - 2014-07-15
--------------------------
* Fix bug #27: Render .json files
* Fix bug #31: Forked pastes cannot be remote forked
* Fix bug #33: Do not index login page
* Fix bug #41: AGPL link broken
* Fix bug #43: github gist cloning does not work
* Fix bug #44: Anchors of files with spaces in their name
* Fix renaming of binary files
* Implement request #6: Atom feed for new and updated pastes
* Implement request #7: Track and display remote forks
* Implement request #21: Send linkbacks when forking remote pastes
* Implement request #32: Distribute phorkie as .phar file with all dependencies
* Implement request #34: Store last OpenID in cookie and pre-fill login form
* Implement request #35: Store author of a paste
* Implement request #37: Show user icon in navbar and profile page
* Implement request #42: Single click remote forking with web+fork: url handler
* Add autoconfiguration for public git clone urls over http
* Add baseurl setting to make phorkie run in a subdirectory of a domain
* Add setupcheck page that checks dependencies and other things
* Add support for file names with directories in pastes
* Add support for UTF-8 characters in file names
* Add support for web hooks
* Add support for forking HTTP and HTTPS git URLs
* Extract gist titles
* Make it possible to install dependencies via composer


Version 0.3.1 - 2012-09-27
--------------------------
* Check for OpenID package in SetupCheck
* Fix bug #24: setupcheck: verify geshi installation
* Fix bug #25: setupcheck: verify markdown


Version 0.3.0 - 2012-09-27
--------------------------
* Fix bug #10: error when nothing submitted [cweiske]
* Fix bug #11: do not index edit, delete and tool pages [cweiske]
* Fix bug #20: elasticsearch error on deletion [cweiske]
* Fix bug #22: Edited pastes not in "recently created" [cweiske]
* Fix bug #23: "work dir not found" on failed remote fork [cweiske]
* Implement request #5: Add OpenID authentication [jnovack]
* Implement request #9: autodetect file type [cweiske]
* Implement request #12: DOAP and rel="vcs-git" support [cweiske]
* Implement request #13: remote forking support [cweiske]
* Hide additional file fields by default, button to toggle their visibility
  [cweiske]
* Markdown support [jnovack]
* Added nginx rewrite rules to README [skl]


Version 0.2.0 - 2012-05-25
--------------------------
* Elasticsearch support
* Use title instead of paste ID
* Pager for result listings
* Implement request #3: Show new pastes in sidebar of "new paste" page


Version 0.1.0 - 2012-04-19
--------------------------
Initial version
