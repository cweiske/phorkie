************************************
phorkie - PHP and Git based pastebin
************************************

URLs
====

``/``
  Index page. Shows form for new paste
``/[0-9]+``
  Display page for paste
``/[0-9]/edit``
  Edit the paste
``/[0-9]+/raw/(.+)``
  Display raw file contents
``/[0-9]/delete``
  Delete the paste
``/search(/.+)?``
  Search for term
``/list(/[0-9])?``
  List all pastes


Internal directory layout
=========================
::

  repos/
    1/ - git repository for paste #1
      .git/
        description - Description for the repository
    2/ - git repository for paste #2


Search
======
Use ``ack-grep``


Install geshi
=============
::

  $ pear channel-discover mediawiki.googlecode.com/svn
  $ pear install mediawiki/geshi


TODO
====
- search
- OpenID-Login to get username+email as authorship information
- sidebar: history
- image upload
- document how to keep disk usage low (block size)
- comments
- when 2 people edit, merge changes
- diff changes
- configurable highlights


Features
========
- every paste is a git repository
- rST rendering
- paste editing
- multiple files
