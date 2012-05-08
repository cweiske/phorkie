************************************
phorkie - PHP and Git based pastebin
************************************
Self-hosted pastebin software written in PHP.
Pastes are editable, may have multiple files and are stored in git repositories.

Homepage: http://sourceforge.net/p/phorkie/

.. contents:: Table of Contents

========
Features
========
- every paste is a git repository

  - repositories can be cloned
  - clone url can be displayed
- paste editing

  - add new files
  - delete existing files
  - replace file with upload
- multiple files in one paste
- syntax highlighting with GeSHi
- rST rendering
- image upload + display
- external tool support

  - xmllint
  - php syntax check
- history in the sidebar

  - old files can be downloaded easily
- search across pastes: description, file names and file content

  - options: quoting, logical and, or, not, partial words


============
Dependencies
============
phorkie stands on the shoulders of giants.

::

  $ pear install versioncontrol_git-alpha
  $ pear install services_libravatar-alpha
  $ pear install http_request2
  $ pear install pager
  $ pear install https://github.com/downloads/cweiske/Date_HumanDiff/Date_HumanDiff-0.1.0.tgz

  $ pear channel-discover pear.twig-project.org
  $ pear install twig/Twig

  $ pear channel-discover mediawiki.googlecode.com/svn
  $ pear install mediawiki/geshi

Note that this version of GeSHi is a bit outdated, but it's the fastest
way to install it.


======
Search
======

phorkie makes use of an Elasticsearch__ installation if you have one.

It is used to provide search capabilities and the list of recent pastes.

__ http://www.elasticsearch.org/

Setup
=====
Edit ``config.php``, setting the ``elasticsearch`` property to the HTTP URL
of the index, e.g. ::

  http://localhost:9200/phorkie/

Run the index script to import all existing pastes into the index::

  php scripts/index.php

That's all. Open phorkie in your browser, and you'll notice the search box
in the top menu.


=====
HowTo
=====

Make git repositories clonable
==============================
To make git repositories clonable, you need to install ``git-daemon``
(``git-daemon-run`` package on Debian/Ubuntu).

Now make the repositories available by symlinking the paste repository
directory (``$GLOBALS['phorkie']['cfg']['repos']`` setting) into
``/var/cache/git``, e.g.::

  $ ln -s /home/user/www/paste/repos/git /var/cache/git/paste

Edit your ``config.php`` and set the ``$GLOBALS['phorkie']['cfg']['git']['public']``
setting to ``git://$yourhostname/git/paste/``.
The rest will be appended automatically.


You're on your own to setup writable repositories.


=================
Technical details
=================

TODO
====
- OpenID-Login to get username+email as authorship information
- filters (``xmllint --format``, ``rapper``)
- document how to keep disk usage low (block size)
- comments
- when 2 people edit, merge changes
- diff changes
- configurable highlights
- Atom feed for new pastes
- Atom feed for paste changes


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
``/[0-9]/tool/[a-zA-Z]+/(.+)``
  Run a tool on the given file
``/[0-9]/rev/[a-z0-9]+``
  Show specific revision of the paste
``/[0-9]/delete``
  Delete the paste
``/[0-9]/fork``
  Create a fork of the paste
``/search?q=..(&page=[0-9]+)?``
  Search for term, with optional page
``/list(/[0-9])?``
  List all pastes, with optional page


Internal directory layout
=========================
::

  repos/
    work/
      1/ - work directory for paste #1
      2/ - work directory for paste #2
    git/
      1.git/ - git repository for paste #1
        description - Description for the repository
      2.git/ - git repository for paste #2
