Horde-Git-Tools
========================================

POC/WIP toolchain for dealing with the upcoming split repository structure of
[Horde](https://github.com/horde/horde),

Installation
------------

Uses composer for dependency management.

```sh
git clone --depth 1 https://github.com/horde/horde.git horde-git
git clone https://github.com/mrubinsk/horde-git-tools.git horde-git-tools
cd horde-git-tools
composer.phar install
```
Usage
-----

Current usage. Subject to change until code is finalized. Also see the
--help text.

```sh
# Review bin/conf.php.dist and set values accordingly.
cp config/conf.php.dist config/conf.php
cd horde-git-tools/bin

# Options can also be given on command line. See usage for information.
horde-git-tools --help

# Clones all repositories locally to the configured git_base directory.
horde-git-tools git clone

# Links (or copies) to a web accessible directory (replacement for old
# install_dev script).
horde-git-tools dev install

# List available repositories on remote.
# Providing the --verbose flag will output full response from GitHub.
horde-git-tools --verbose git list

# The above will look for a .horde.yml file in the repository root to be
# considered. To ignore this check, add the --ignore-yml flag. Mostly for
# testing/development purposes.
horde-git-tools --ignore-yml git list

# Attempt to checkout a specific branch on all repositories.
horde-git-tools git checkout FRAMEWORK_5_2

# Attempt to git pull --rebase all repositories.
# Still need to add options like ability to ensure repo is on a specific
# branch before pulling, option to automatically stash/pop if repository is
# not clean etc...
horde-git-tools git pull

# Attempt to perform arbitrary git command on all repositories.
horde-git-tools git run "reset HEAD"

# Do the same, but only for imp and ansel.
horde-git-tools git run --repositories=imp,ansel "reset HEAD"

# Report on status of each repository.
# Still need to tweak and add options, better display etc...
horde-git-tools git status

# Perform a "component" action
horde-git-tools component /path/to/repository update
horde-git-tools component /path/to/repository changed '[mjr] Some change'
horde-git-tools component /path/to/repository release
```

Still todo
----------

-  Create install action that will perform a full pear install of the webmail or
   groupware bundle (or optionally a specified list of applications).


