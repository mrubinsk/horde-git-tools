Horde-Git-Tools
========================================

POC/WIP toolchain for dealing with the upcoming split repository structure of
[Horde](https://github.com/horde/horde),

Installation
------------

Uses composer for dependency management.

```sh
git clone https://github.com/mrubinsk/horde-git-tools.git horde-git-tools
cd horde-git-tools
# If using PHP 7.x, you will need to use the --ignore-platform-reqs option
composer.phar install
```
Usage
-----

Current usage. Subject to change until code is finalized and some of these
actions still have @todo.

```sh
# Review bin/conf.php.dist and set values accordingly.
cp bin/conf.php.dist bin/conf.php
cd horde-git-tools/bin

# Options can also be given on command line. See usage for information.
php horde-git-tools help

# Clones all repositories locally to the configured git_base directory.
php horde-git-tools git clone

# Links (or copies) to a web accessible directory (replacement for old
# install_dev script).
php horde-git-tools dev install

# List available repositories on remote. Providing the --debug flag will
# output full response from GitHub.
php horde-git-tools --debug git list

# The above will look for a .horde.yml file in the repository root to be
# considered. To ignore this check, add the --ignore-yml flag.
php horde-git-tools --ignore-yml git list

# Attempt to checkout a specific branch on all repositories.
php horde-git-tools git checkout FRAMEWORK_5_2

# Attempt to git pull --rebase all repositories.
# Still need to add options like ability to ensure repo is on a specific
# branch before pulling, option to automatically stash/pop if repository is
# not clean etc...
php horde-git-tools git pull

# Report on status of each repository.
# Still need to tweak and add options, better display etc...
php horde-git-tools git status
```

Still todo
----------

-  Create install action that will perform a full pear install of the webmail or
   groupware bundle (or optionally a specified list of applications).

- Subset of Component functionality. I.e., releases, changes, package.xml
  maintenance etc... Need to decide if we want to bundle the Components
  application or pull the needed functionality out of it and refactor into
  this app. Pros for bundle: Less work. Cons: Would probably need to perform
  some refactoring on it anyway to reflect layout of new repo, would be bundling
  a bunch of CLI code that we won't use since this app already has the
  CLI infrastructure present. Really not sure which way I'm leaning yet...thoughts?

