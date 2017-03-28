# horde-git-tools
POC/WIP toolchain for dealing with the new split horde repos.

Thoughts/todo:

- ~~Implement a Autoloader or is this overkill?~~Using composer for pulling in dependencies and for autoloading

- Developer checkout (~~are we switching to use ONLY github now?~~we are switching to use ONLY github now)

- Recursive update from Git.

- Improve the way the status action reports? Only report when a repo has
  uncommited changes or is not in-sync with remote?

- Install vs Linking. Create install action that will perform a full pear
  install, or optionally install specific packages only.
