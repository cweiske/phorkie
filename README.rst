************************************
phorkie - PHP and Git based pastebin
************************************
Self-hosted pastebin software written in PHP.
Pastes are editable, may have multiple files and are stored in git repositories.

Homepage: http://sourceforge.net/p/phorkie/


========
Features
========
- every paste is a git repository
- paste editing
- multiple files in one paste
- syntax highlighting with GeSHi
- rST rendering


============
Dependencies
============
phorkie stands on the shoulders of giants.


Install GeSHi
=============
::

  $ pear channel-discover mediawiki.googlecode.com/svn
  $ pear install mediawiki/geshi

Note that this version of GeSHi is a bit outdated, but it's the fastest
way to install it.


Install Twig
============
::

  $ pear channel-discover pear.twig-project.org
  $ pear install twig/Twig


=================
Technical details
=================

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




