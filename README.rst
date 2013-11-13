************************************
phorkie - PHP and Git based pastebin
************************************
Self-hosted pastebin software written in PHP.
Pastes are editable, may have multiple files and are stored in git repositories.

Project page: http://sourceforge.net/p/phorkie/

.. contents:: Table of Contents

========
Features
========
- every paste is a git repository

  - repositories can be cloned
  - clone url can be displayed
  - remote pastes can be forked (rel="vcs-git" and gist.github.com)
- paste editing

  - add new files
  - delete existing files
  - replace file with upload
- multiple files in one paste
- syntax highlighting with GeSHi
- rST and Markdown rendering
- image upload + display
- OpenID authentication
- external tool support

  - xmllint
  - php syntax check
- history in the sidebar

  - old files can be downloaded easily
- search across pastes: description, file names and file content

  - options: quoting, logical and, or, not, partial words
- webhook support - get notified when pastes are created, edited or deleted


============
Installation
============
1. Unzip the phorkie release file::

   $ tar xjvf phorkie-0.3.0.tar.bz2

2. Create the git directories::

   $ mkdir -p repos/git repos/work
   $ chmod og+w repos/git repos/work

3. Install dependencies_

4. Copy ``data/config.php.dist`` to ``data/config.php`` and adjust it
   to your needs::

   $ cp data/config.php.dist data/config.php
   $ $EDITOR data/config.php

   Look at ``config.default.php`` for values that you may adjust.

5. Set your web server's document root to ``/path/to/phorkie/www/``
   Alternatively, you can add a symlink to the ``www`` folder into your
   web server's existing document root tree (being careful to keep
   main phorkie folder outside the document root for security purposes)
   and ensure you set the ``baseurl`` config option appropriately. You
   must also set the ``RewriteBase`` in the ``.htaccess`` file or adjust
   the nginx configuration accordingly.

6. Open phorkie in your web browser


Dependencies
============
phorkie stands on the shoulders of giants.

It requires the following programs to be installed
on your machine:

- Git v1.7.5 or later
- PHP v5.3.0 or later
- PEAR v1.9.2 or later

::

  $ pear install versioncontrol_git-alpha
  $ pear install services_libravatar-alpha
  $ pear install http_request2
  $ pear install pager
  $ pear install date_humandiff-alpha
  $ pear install openid-alpha

  $ pear channel-discover pear.twig-project.org
  $ pear install twig/Twig

  $ pear channel-discover mediawiki.googlecode.com/svn
  $ pear install mediawiki/geshi

  $ pear channel-discover zustellzentrum.cweiske.de
  $ pear install zz/mime_type_plaindetect-alpha

  $ pear channel-discover pear.michelf.ca
  $ pear install michelf/Markdown

Note that this version of GeSHi is a bit outdated, but it's the fastest
way to install it.
If you install it manually be sure to update the
path from ``data/config.default.php``.


======
Search
======

phorkie makes use of an Elasticsearch__ installation, if you have one.

It is used to provide search capabilities and the list of recent pastes.

__ http://www.elasticsearch.org/


Setup
=====
Edit ``config.php``, setting the ``elasticsearch`` property to the HTTP URL
of the index, e.g. ::

  http://localhost:9200/phorkie/

You must use a search namespace with Elasticsearch such as ``phorkie/``.
Run the index script to import all existing pastes into the index::

  php scripts/index.php

That's all. Open phorkie in your browser, and you'll notice the search box
in the top menu.


Reset
=====
In case something really went wrong and you need to reset the search
index, run the following command::

  $ curl -XDELETE http://localhost:9200/phorkie/
  {"ok":true,"acknowledged"}

Phorkie will automatically re-index everything when ``setupcheck`` is enabled
in the configuration file.

You may also manually run the reindexing script with::

  $ php scripts/index.php


=====
HowTo
=====

Make git repositories clonable
==============================
To make git repositories clonable, you need to install ``git-daemon``
(``git-daemon-run`` package on Debian/Ubuntu).

Make the repositories available by symlinking the paste repository
directory (``$GLOBALS['phorkie']['cfg']['repos']`` setting) into
``/var/cache/git``, e.g.::

  $ ln -s /home/user/www/paste/repos/git /var/cache/git/paste

Edit your ``config.php`` and set the ``$GLOBALS['phorkie']['cfg']['git']['public']``
setting to ``git://$yourhostname/git/paste/``.
The rest will be appended automatically.


You're on your own to setup writable repositories.


Protect your site with OpenID
=============================
You have the option of enabling OpenID authentication to help secure your
pastes on phorkie.
Set the ``$GLOBALS['phorkie']['auth']`` values in the
``data/config.php`` file as desired.

There are two different types of security you can apply.
First, you can restrict to one of three ``securityLevels``:

- completely open (``0``)
- protection of write-enabled functions such as add, edit, etc. (``1``)
- full site protection (``2``)

Additionally, you can restrict your site to ``listedUsersOnly``.
You will need to add the individual OpenID urls to the
``$GLOBALS['phorkie']['auth']['users']`` variable.


Get information about paste editors
===================================
Phorkie stores the user's OpenID or IP address (when not logged in) when
a paste is edited.
It is possible to get this information for each single commit::

    // IP / OpenID for the latest commit
    $ git notes --ref=identity show
    127.0.0.1

    // show IP / OpenID for a given commit
    $ git notes --ref=identity show 29f82a
    http://cweiske.de/


Notifications via webhooks
==========================
Depending on how you use phorkie, it might be nice to notify some other service
when pastes are added or updated.
Phorkie contains a simply mechanism to post data to a given URL which
you can then use as needed.

The data are json-encoded POSTed to the URLs contained in the
``$GLOBALS['phorkie']['cfg']['webhooks']`` setting array, with
a MIME type of ``application/vnd.phorkie.webhook+json``::

  {
      'event': 'create',
      'author': {
          'name':'Anonymous',
          'email': 'anonymous@phorkie',
      },
      'repository': {
          'name': 'webhooktest',
          'url': 'http://example.org/33',
          'description': 'webhooktest',
          'owner': {
              'name': 'Anonymous',
              'email': 'anonymous@phorkie',
          }
      }
  }

The event may be ``create``, ``edit`` or ``delete``.


=================
Technical details
=================

TODO
====
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
  Index page.
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
``/[0-9]/doap``
  Show DOAP document for paste
``/[0-9]/fork``
  Create a fork of the paste
``/search?q=..(&page=[0-9]+)?``
  Search for term, with optional page
``/list(/[0-9])?``
  List all pastes, with optional page
``/new``
  Shows form for new paste
``/login``
  Login page for protecting site
``/user``
  Edit logged-in user information


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

nginx rewrites
==============
If you use nginx, place the following lines into your ``server`` block:

::

  if (!-e $request_uri) {
    rewrite ^/([0-9]+)$ /display.php?id=$1;
    rewrite ^/([0-9]+)/delete$ /delete.php?id=$1;
    rewrite ^/([0-9]+)/delete/confirm$ /delete.php?id=$1&confirm=1;
    rewrite ^/([0-9]+)/doap$ /doap.php?id=$1;
    rewrite ^/([0-9]+)/edit$ /edit.php?id=$1;
    rewrite ^/([0-9]+)/fork$ /fork.php?id=$1;
    rewrite ^/([0-9]+)/raw/(.+)$ /raw.php?id=$1&file=$2;
    rewrite ^/([0-9]+)/rev/(.+)$ /revision.php?id=$1&rev=$2;
    rewrite ^/([0-9]+)/rev-raw/(.+)$ /raw.php?id=$1&rev=$2&file=$3;
    rewrite ^/([0-9]+)/tool/([^/]+)/(.+)$ /tool.php?id=$1&tool=$2&file=$3;

    rewrite ^/new$ /new.php;
    rewrite ^/feed/new$ /feed-new.php;
    rewrite ^/feed/updated$ /feed-updated.php;
    rewrite ^/list$ /list.php;
    rewrite ^/list/([0-9]+)$ /list.php?page=$1;

    rewrite ^/search$ /search.php;
    rewrite ^/search/([0-9]+)$ /search.php?page=$1;

    rewrite ^/login$ /login.php;
    rewrite ^/user$ /user.php;
  }
