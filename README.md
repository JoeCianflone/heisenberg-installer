## Heisenberg Toolkit Installer

Install all your dependencies simply with the Heisenberg install.  

The best way to install this would be to do it globally with Composer:

```
composer global require joecianflone/heisenberg-toolkit-installer
```

Once that has installed you have access to the `walt` command.  Eventually there may be other things this can do but at the moment it only does one thing...installs Heisenberg.

```
walt install
```

`walt` works out of your current directory when it installs heisenberg and by default it drops all the raw Sass and JS files into a `src` directory.  You can change that directory though:

```
walt install --src="foo/bar"
```

By default `walt` compiles all of its assets into an `assets` directory that can again be changed:

```
walt install --dest="public/foo/bar"
```

This will also go through and make some changes to the base `gulpfile.js` and make sure all your directories match up.  


