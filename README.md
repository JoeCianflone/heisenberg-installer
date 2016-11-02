## Heisenberg Toolkit Installer

Install all your dependencies simply with the Heisenberg install.

The best way to install this would be to do it globally with Composer:

```
composer global require joecianflone/heisenberg-toolkit-installer
```

Once that has installed you have access to the `walt` command. This will give you the ability to install and/or update the Heisenberg toolkit. *Note: the commands are written in PHP, hence Composer and it's using Symfony and the Laravel Console parser*

###Install

The basic structure of this command:

```Bash
$ walt install <src="src"> <dest="assets"> --dev --force --deps
```

Out-of-the-box `walt` will install all your source code (the code you should be working with) into the `src` directory. It will all compile to the `assets` folder, but since that's compiled unless you've run the `--deps` option you won't see that folder right away.

*Please note that order of `src` and `dest` is important.*


###Options

The installer will give you 3 options so lets go over them quickly.

#### `--dev`

`walt` pulls the latest code from the master branch of the Heisenberg repository. For most people, this option will be fine as it's stable and tested. If you'd like to try out the bleeding edge, you can add the `--dev` option and that will pull down the code in the developer branch.

The develop branch is essentially the beta branch. It will compile, it will work, but it might be buggy and we might just delete a feature you're using because it's just not a stable or set API before release.

Use this branch with caution.

#### `--deps`

When you install a fresh copy of Heisenberg you've really just pulled down the latest code, but you haven't yet compiled it all. If you tried to run `gulp` right now, it probably won't work because you don't have any of your NPM dependencies on your machine. The `--deps` option will run the following commands for you:

+ `yarn install`
+ `gulp`

That way your system is compiled and ready to go for you. This will add some time to the install process though because NPM and bower can take some time.

#### `--force`

By default `walt` will no longer overwrite files. If you already have a `.gitignore` file sitting in your folder when you pull down Heisenberg it will do a diff on all files and return you a list of files that need you to manually merge. If you don't care about whatever happens to be in that folder, you can run this `--force` option. This will overwrite any files Heisenberg is trying to install. Use this option if you know what you're doing or else you're gonna be in a world of hurt.

###Cleanup

The basic structure of this command:

```Bash
$ walt cleanup
```

In most circumstances you'll never need to run this command, but on the off-chance that a Heisenberg install fails or something else goes wrong, you can use the cleanup command to delete any leftover cruft.

###Update

The basic structure of this command:

```Bash
$ walt update --dev --force --deps
```

Similar to the `install` command, but in this case it will check to see if you need to update any files. By default it will diff your files and it will use the `src` and `dist` folders already set in the `.heisenberg` file that gets installed.
