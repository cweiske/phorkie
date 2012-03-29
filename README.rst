************************************
Phorkie - PHP and Git based pastebin
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
- edit
- search
- OpenID-Login to get username+email as authorship information
- sidebar: history
- image upload
- rst rendering
- document how to keep disk usage low (block size)
- comments
