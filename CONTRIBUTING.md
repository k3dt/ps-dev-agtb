# Contributing

We're the world’s fastest-growing customer relationship management company because we make CRM simple.
We also have a lot of fun in the process. Come join us for the ride!

Testing SugarCRM with all flavors and configurations on each possible environment that we support is hard. We want to keep it as easy as possible for you to contribute with changes that will make it work on your environment. In order to keep consistency there are a few guidelines that we need contributors to follow.

## Reporting a Bug

**If you think you've found a security issue, please use the [special procedure](#reporting-a-security-issue) instead.**

Before submitting a bug confirm that it doesn't exists already on the official [bug tracker][SugarCRM bug tracker].

If you found an already reported issue, even if it's closed, please add your comments on it.

If you are sure it is a new bug, please report it by following the rules below whenever possible:

> review this with bug tracker
* Use the title field to clearly describe the issue;
* Describe the steps needed to reproduce the bug with short code examples (providing a unit test that illustrates the bug would be even better);
* Give as much details as possible about your environment (OS, PHP version, SugarCRM version, enabled extensions, existing custom modules or custom code, etc.);
* (optional) Attach a [patch](#submitting-a-patch).

## Submitting a Patch

Patches are the best way to provide a bug fix or to propose enhancements to SugarCRM.

### Step 1: Setup your Environment

#### Install the Software Stack

Before working with SugarCRM, setup your environment with the following software:

* Git;
* PHP version 5.4+;
* Composer.

#### Configure Git

Set up your user information with your real name and a working email address:

```bash
$ git config --global user.name "Your Name"
$ git config --global user.email you@example.com
```

> If you are new to Git, we highly recommend you to read the excellent and free [ProGit][ProGit] book.

<!---->

> We currently ignore some IDEs on our `.gitignore` file, but if your IDE creates configuration files inside project's directory, you can use global `.gitignore` file (for all projects) or `.git/info/exclude` file (per project) to ignore them. See [Github's Documentation][GitHub Doc Ignoring Files].

<!---->

> Windows users: when installing Git, the installer will ask what to do with line endings and suggests to replace all LF by CRLF. This is the wrong setting if you wish to contribute to SugarCRM! Selecting the as-is method is your best choice, as Git will convert your line feeds to the ones in the repository. If you have already installed Git, you can check the value of this setting by typing:
>
> ```bash
> $ git config core.autocrlf
> ```
>
> This will return either `"false"`, `"input" or "true"`, `"true" and "false"` being the wrong values. Set it to another value by typing:
>
> ```bash
> $ git config --global core.autocrlf input
> ```
>
> Replace `--global` by `--local` if you want to set it only for the active repository.

#### Installing Composer

Composer is a tool for dependency management in PHP. It allows you to declare the dependent libraries your project needs and it will install them in your project for you.

SugarCRM requires composer to manage its dev dependencies and provide better support on versioning across external libraries, while allowing easier maintenance when keeping those libraries up to date.

To install composer, please follow the official information provided in the [composer page][Composer install]. It has all the information available to install composer on your system.

#### Get the SugarCRM Source Code

Get the SugarCRM source code:

* Create a [GitHub][GitHub signup] account and sign in;
* Fork the [SugarCRM Mango repository][SugarCRM Mango repo] (click on the "Fork" button);
* After the "hardcore forking action" has completed, clone your fork locally (this will create a `Mango` directory):

```bash
$ git clone git@github.com:USERNAME/Mango.git
```

* Add the upstream repository as `remote`:

```bash
$ cd Mango
$ git remote add upstream git@github.com:sugarcrm/Mango.git
```

* Grab the sidecar submodule.

```bash
$ git submodule init
$ git submodule update
```

See [GitHub using pull requests][GitHub using pull requests] for more information.

#### Check that the current Tests pass

Now that SugarCRM is installed, check that all unit tests pass on your environment as explained in the dedicated [tests section](#running-sugarcrm-tests).
* Make sure you have added the necessary tests for your changes.
* Run _all_ the tests to ensure nothing else was accidentally broken.

#### Build the codebase

See https://github.com/sugarcrm/Mango/wiki/SugarCRM-Build-System for more details

### Step 2: Work on your Patch

#### Choose the right Branch

Before working on a patch, you must determine on which branch you need to work. The branch should be based on the `master` branch if you want to add a new feature. But if you want to fix a bug, use the oldest but still maintained version of SugarCRM where the bug was found (like `7_1_x`).

> All bug fixes merged into maintenance branches are also merged into more recent branches on a regular basis. For instance, if you submit a patch for the `7_1_x` branch, the patch will also be applied by SugarCRM team on the `master` branch.

#### Create a Topic Branch

Each time you want to work on a patch for a bug or on an enhancement, create a topic branch:

```bash
$ git checkout -b BRANCH_NAME master
```

Or, if you want to provide a bug fix for the `7_1_x` branch, first track the remote `7_1_x` branch locally:

```bash
$ git checkout -t origin/7_1_x
```

Then create a new branch of the `7_1_x` branch to work on the bug fix:

```bash
$ git checkout -b BRANCH_NAME 7_1_x
```

> Use a descriptive name for your branch (`bug_XXX` where `XXX` is the bug number is a good convention for bug fixes, `SC-XXX` where `XXX` is the issue number in JIRA is a good convention for new features).

The above checkout commands automatically switch the code to the newly created branch (check the branch you are working on with `git branch`).

#### Work on your Patch

Work on the code as much as you want and commit as much as you want; but keep in mind the following:

* Follow the coding [standards](#coding-standards);
* Check for unnecessary whitespace with `git diff --check` before committing. (use `git diff --check` to check for trailing spaces -- also read the tip below);
* You can use <a href="https://github.com/mozilla/moz-git-tools">moz-git-tools'</a> git-fix-whitespace tool to automatically remove most such whitespace.
* Add unit tests to prove that the bug is fixed or that the new feature actually works;
* Try hard to not break backward compatibility (if you must do so, try to provide a compatibility layer to support the old way) -- patches that break backward compatibility have less chance to be merged;
* Do atomic and logically separate commits (use the power of `git rebase` to have a clean and logical history);
* Squash irrelevant commits that are just about fixing coding standards or fixing typos in your own code;
* Never fix coding standards in some existing code as it makes the code review more difficult;
* Write good commit messages (see the tip below).
* Make commits of logical units.
* Make sure your commit messages are in the proper format.

> A good commit message is composed of a summary (the first line) that contains the ticket number, optionally followed by a blank line and a more detailed description.  Use a verb in the infinitive on present form (`fix …`, `add …`, `update …`, `restore …`, …) to start the summary and **don't** add a period at the end. More details about what was changed/fixed should be places after the summary line.

##### Example Commit Messages

*Example 1*
> SC-XXXX: Fix Account Record View
>
> Fix an issue on the account record view where the xyz widget was not displaying correctly.

*Example 2*
> Account Record View (fixes SC-XXXX)
>
> Fix an issue on the account record view where the xyz widget was not displaying correctly.

#### Prepare your Patch for Submission

When your patch is not about a bug fix (when you add a new feature or change an existing one for instance), it must also include the JIRA issue number.

### Step 3: Submit your Patch

Whenever you feel that your patch is ready for submission, follow the steps bellow.

#### Rebase your Patch

Before submitting your patch, update your branch (needed if it takes a while to finish your changes):

```bash
$ git checkout BRANCH_NAME
$ git fetch upstream
$ git rebase upstream/master
```

> Replace `master` with `7_1_x` if you are working on a bug fix
> Note: After this if you do not see latest commits in your branch, then you may need to execute following:
>       git pull --rebase upstream 7_1_x

When doing the `rebase` command, you might have to fix merge conflicts. `git status` will show you the *unmerged* files. Resolve all the conflicts, then continue the rebase:

```bash
$ git add ... # add resolved files
$ git rebase --continue
```

Check that all tests still pass and push your branch remotely:

```bash
$ git push origin BRANCH_NAME
```

#### Make a Pull Request

You can now make a pull request on the `sugarcrm/Mango` Github repository.

> Take care to point your pull request towards `Mango:7_1_x` if you want the core team to pull a bug fix based on the `7_1_x` branch.

To ease the core team work, always include the modified modules in your pull request message, like in:

```text
[Core] fix something
[Accounts] [Contacts] add something
```

> Please use the title with "[WIP]" if the submission is not yet completed or the tests are incomplete or not yet passing.

The pull request description must include the following check list to ensure that contributions may be reviewed without needless feedback loops and that your contributions can be included into SugarCRM as quickly as possible:

```text
| Q             | A
| ------------- | ---
| Bug fix?      | [yes|no]
| New feature?  | [yes|no]
| BC breaks?    | [yes|no]
| Deprecations? | [yes|no]
| Tests pass?   | [yes|no]
| Fixed bugs    | [comma separated list of bugs fixed by this PR]
| Doc PR        | sugarcrm/devdocs#123

### TODO
List of pending items

```

An example submission could now look as follows:

```text
| Q             | A
| ------------- | ---
| Bug fix?      | no
| New feature?  | no
| BC breaks?    | no
| Deprecations? | no
| Tests pass?   | yes
| Fixed bugs    | #51284, #51324
| Doc PR        | sugarcrm/devdocs#123

### TODO
- [x] Find a way to setup the config automatically
- [ ] Provide better extensibility
```

In the pull request description, give as much details as possible about your changes (don't hesitate to give code examples to illustrate your points). If your pull request is about adding a new feature or modifying an existing one, explain the rationale for the changes. The pull request description helps the code review and it serves as a reference when the code is merged (the pull request description and all its associated comments are part of the merge commit message).

In addition to this "code" pull request, you must also link to the documentation wiki to update the documentation when appropriate.

#### Rework your Patch

Based on the feedback of the pull request, you might need to rework your patch. Before re-submitting the patch, rebase with `upstream/master`, don't merge; and force the push to the origin:

```bash
$ git rebase -f upstream/master
$ git push -f origin BRANCH_NAME
```

> when doing a `push --force`, always specify the branch name explicitly to avoid messing other branches in the repo (`--force` tells git that you really want to mess with things **so do it carefully**).

Often, team leads will ask you to "squash" or review your commits. This means you will convert many commits to less commits. To do this, use the rebase command:

```bash
$ git rebase --interactive --autosquash upstream/master
$ git push -f origin BRANCH_NAME
```

After you type this command, an editor will popup showing a list of commits:

```text
pick 1a31be6 first commit
pick 7fc64b4 second commit
pick 7d33018 third commit
```

To squash all commits into the first one, remove the word "pick" before the second and the last commit, and replace it by the word "squash". Read more about reabase interactive on [github help articles][GitHub interactive rebase]. When you save, git will start rebasing, and if successful, will ask you to edit the commit message, which by default is a listing of the commit messages of all the commits. When you finish, execute the push command.

## Reporting a Security Issue

If you found a security issue in SugarCRM, please don't use the bug tracker. All security issues must be sent to **secure [at] sugarcrm.com** instead. Emails sent to this address are forwarded to the SugarCRM core-team private mailing-list.

For each report, we first try to confirm the vulnerability. When it is confirmed, the core-team works on a solution following these steps:

1. Send an acknowledgement to the reporter;
2. Work on a patch;
3. Write a post describing the vulnerability, the possible exploits, and how to patch/upgrade affected applications;
4. Apply the patch to all maintained versions of SugarCRM;
5. Publish the post on the official SugarCRM blog.

**While we are working on a patch, please do not reveal the issue publicly.**

## Running SugarCRM Tests

Before submitting a [patch](#submitting-a-patch) for inclusion, you need to run the SugarCRM test suite to check that you didn't break anything.

### Client side test suite

The SugarCRM client side test suite is composed of three different tools: [Karma test runner], [Jasmine] and [Gulp the Streaming build system].

#### How to install

First you need to have [NodeJS] running on your local machine.

##### Using Sugar's npm registry

SugarCRM caches its npm dependencies to improve availability. To use the new repo, do the following:

```bash
$ npm cache clear
$ npm config set registry http://npm-cache.qa.sugarcrm.net:8181/content/groups/npm/
```

We also have a custom download proxy for one of our core testing dependencies, PhantomJS.
To use it, please run:

```bash
$ npm config set phantomjs_cdnurl http://sugar-ci.s3.amazonaws.com/npm-downloads
```

#### Installing dependencies

Next, you need to build the several flavors of Sugar (PRO, ENT) and install all the dependent packages for each:

```bash
$ cd <sugarcrm>
$ npm install
```

```bash
$ cd <sugarcrm/sidecar>
$ npm install
```

#### Tasks

After the installation process finishes you're ready to check the impact of your modifications, by running the tasks below:

##### karma

This task is pre-configured with CI settings and is ideal to use before submitting your pull requests, thus detecting any major problems before they happen.

```bash
$ cd <sugarcrm>
$ node_modules/gulp/bin/gulp.js karma [--browsers <b1,...>] [--team <name>]
```

##### karma --dev

Targeted at daily development, notifies you about the impact that your pull requests might have on the end result of the tests.

By having the auto watch flag enabled you only need to run the task once before starting new developments. It will then listen for file changes and act accordingly by automatically triggering a new test run.

```bash
$ cd <sugarcrm>
$ node_modules/gulp/bin/gulp.js karma --dev [--browsers <b1,...>] [--team <name>]
```

##### karma --manual
 
With this option, you can trigger the tests by launching any browser and visiting the URL where karma web server is listening (by default it is listening on 0.0.0.0:9876 HTTP). This is useful to test browsers like IE while running in virtual machines. Note that this parameter is not compatible with `--dev`, `--browsers`, and `--sauce`.
 
 ```bash
 $ cd <sugarcrm>
$ node_modules/gulp/bin/gulp.js karma --manual
```

##### karma --ci

This option generates additional XML report after the tests have finished running. The file `test-results.xml` is created in path and it contains information for friendly display on CI.

```bash
$ cd <sugarcrm>
$ node_modules/gulp/bin/gulp.js karma --ci [--browsers <b1,...>] [--path <path>]
```
 
##### karma --coverage
 
 Targeted at the final stage of development. Besides notifying you about the impact that your pull requests might have on the end result of the tests, it also displays the current code coverage.
 
 ```bash
 $ cd <sugarcrm>
 $ node_modules/gulp/bin/gulp.js karma --coverage [--browsers <b1,...>] [--path <path>]
 ```
 
This task uses _coverage_ and _dots_ as reporters and generates two different types of reports, text based (printed on terminal) and _HTML_.

If the `--path` option isn't supplied, the _HTML_ based report is generated into a temporary folder. Its path is printed to the terminal while the task is executed.

##### karma --sauce

This task is pre-configured for running Karma tests on IE 11 on Sauce Labs. It is configured to be suitable for CI as well.
You must have the environment variables `SAUCE_USERNAME` and `SAUCE_ACCESS_KEY` predefined.

```bash
$ cd <sugarcrm>
$ SAUCE_USERNAME=<user> SAUCE_ACCESS_KEY=<access_key> node_modules/gulp/bin/gulp.js karma --sauce [--browsers <b1,...>]
```

**Note that `SAUCE_ACCESS_KEY` is NOT the password, it's a token.**

This task does not support the browsers specified by `--browsers`; instead, the options are `sl_safari`, `sl_firefox`,
and `sl_ie` mapping to IE 11 (on Windows 7), Safari 9 (on OS X 10.11) and Firefox 44 (on Linux) respectively.
IE 11 (on Windows 7) is currently the default when no `--browsers` option is provided.

#### Common Options

The following options are available to all Karma tasks:

By specifying `--browsers` you are able to tell against which browsers this task should run against.
If no browser is specified, `Chrome` is used by default for the `dev` task and `ChromeHeadless` is used by default for
almost everything else. Currently supported browsers are: `Chrome`, `Firefox`, `FirefoxHeadless`, `Safari`, and
`ChromeHeadless`, except for the `sauce` task, which has other options.

By specifying `--team` you're reducing the scope of each test run by only running tests relevant to the given team.
Currently supported teams are: `typhoon`, `hacks`, `crystal`, `sfa`, `mar`, `macaroon`, `integrations`, `burritos`, and `lang`.

#### Exclusive tests

If you want to reduce the scope of each test run, you can use exclusive tests.

Exclusive tests are source based, which means that you have to edit the source code to do it and specifically state which suites/specs should run:

* `ddescribe` states that only specs within these test suites will run.
* `iit` states that only these specific specs will run.

Beware that `iit` has precedence over `ddescribe`, so it’s important to understand that this is level independent. It doesn't matter what “hierarchy” of ddescribes you might have; if an `iit` is defined that is the spec that is going to run.

### PHPUnit

#### PHPUnit Unit Tests

This gulp task runs the PHPUnit unit tests (i.e. those located under `testsunit/`).

 ```bash
$ cd <sugarcrm>
$ node_modules/gulp/bin/gulp.js test:unit:php
```

You should run the tests on a built version of Sugar, but *do not install it*. The tests must be able to run
without installation.

Paths to output files are explained in the section [Workspace Location](#workspace-location).

##### Running individual tests

You can run an individual test by specifying --file:

```bash
$ node_modules/gulp/bin/gulp.js test:unit:php --file <path>
```

##### Workspace Location

The location where output files are placed is determined in the following way:

* If the `--path <path>` flag is passed, use that location
* Otherwise, use the location specified by the WORKSPACE environment variable
* If WORKSPACE is not defined, default to the system temp directory

##### CI Mode

The `--ci` flag generates additional reports after the tests have finished running:

* testdox.txt: TestDox output
* test-output/tap.txt: Test Anything Protocol output
* junit/phpunit.xml: JUnit output (suitable for Jenkins)

```bash
$ cd <sugarcrm>
$ node_modules/gulp/bin/gulp.js test:unit:php --ci [--path <path>]
```

##### Coverage
 
Using the `--coverage` flag, you can generate an HTML code coverage report.
 
First, ensure you have [XDebug] enabled on PHP. Then run the following commands:
 
```bash
$ cd <sugarcrm>
$ node_modules/gulp/bin/gulp.js test:unit:php --coverage [--path <path>]
```
 
 The location where the files are placed is explained [here](#workspace-location).
 
#### Legacy PHPUnit Tests

To run the legacy PHPUnit test suite, install the several flavors of Sugar (PRO, ENT) and run install on each one.
Then run the test suite from the `tests` root directory of the installed instance with the following commands:

```bash
$ cd <sugarcrm>/tests
$ php ../vendor/bin/phpunit
```

The output should display `OK`. If not, you need to figure out what's going on and if the tests are broken because of
your modifications.

> If you want to test a single component type its path after the `phpunit` command, e.g.:
>
> ```bash
> $ cd <sugarcrm>/tests
> $ php ../vendor/bin/phpunit include/SugarOAuth2StorageTest.php
> ```
>
> Run the test suite before applying your modifications to check that they run fine on your configuration.

##### Code Coverage

If you add a new feature, you also need to check the code coverage by using the `coverage-html` option.

First, ensure you have [XDebug] enabled on PHP. Then run the following commands:

```bash
$ cd <sugarcrm>/tests
$ php ../vendor/bin/phpunit --coverage-html=cov/
```

Check the code coverage by opening the generated `cov/index.html` page in a browser.

## Translation Changes
If you make changes involving translatable strings, there is a special procedure to follow. See [Making Translation Changes][Making Translation Changes] for details.

[SugarCRM Mango repo]: https://github.com/sugarcrm/Mango
[SugarCRM bug tracker]: http://www.sugarcrm.com/support/bugs.html
[Making Translation Changes]: https://github.com/sugarcrm/translations

[ProGit]: http://git-scm.com/book

[GitHub signup]: https://github.com/signup/free
[GitHub Doc Ignoring Files]: https://help.github.com/articles/ignoring-files
[GitHub using pull requests]: https://help.github.com/articles/using-pull-requests
[GitHub interactive rebase]: https://help.github.com/articles/interactive-rebase

[Composer install]: https://getcomposer.org/doc/00-intro.md

[Gulp the Streaming build system]: http://gulpjs.com/
[Jasmine]: http://jasmine.github.io/
[Karma test runner]: http://karma-runner.github.io/
[NodeJS]: http://nodejs.org/
[XDebug]: https://xdebug.org/
