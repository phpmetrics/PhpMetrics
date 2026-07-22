# Contribute

In order to run unit tests, please install the dev dependencies:

    curl -sS https://getcomposer.org/installer | php
    php composer.phar install

Then, in order to run the test suite:

    ./vendor/bin/phpunit

Thanks for your help.

## Why the code is so old?

### Philosophy

PhpMetrics has several goals:
+ be stable
+ be performant
+ run on the **maximum of PHP versions** (PHP 5.3 to PHP 8.4)
+ be installable everywhere, **without dependency conflicts**

For these reasons, the code of PhpMetrics is intentionally written in "legacy" PHP.

### Dependencies

Not all projects are on the latest version of PHP, Symfony, or Laravel. Yes, there are projects that use Symfony 2. And these projects may also need metrics and quality.

For these reasons, PhpMetrics comes with the minimum of dependencies. Only the dependency on `nikic/php-parser` is accepted.

No Pull Request that modifies the `require` block will be accepted.


## Releasing

Please NEVER tag manually.

### Requirements

You will need to install :

+ [`docker`](https://www.docker.com/)
+ [make](https://www.gnu.org/software/make/)

### Usage

This command updates the version in the sources, commits, then creates and
pushes the git tag:

```bash
make release TAG=<vx.y.z>
# where x is the major version, y is the minor version and z is the patch version
```

The tag MUST start with `v` and follow the `vX.Y.Z` format; an optional
`rcN`, `alphaN` or `betaN` suffix is allowed (e.g. `v3.0.0rc9`). Any other
format is refused, both by `make release` and by the workflow.

Once the tag is pushed, the `Release` GitHub Actions workflow
(`.github/workflows/release.yml`) takes over: it runs the test suite, builds
the artifacts (phar, Debian package, standalone binaries) and creates a
**draft release** on GitHub with everything attached and generated notes.

Last manual step: open the draft on the
[releases page](https://github.com/phpmetrics/PhpMetrics/releases), review
the notes, and publish it.

Good to know:

+ tags with an `rc`/`alpha`/`beta` suffix are automatically flagged as
  pre-release; GitHub handles the "latest" label by itself (the most recent
  published stable release is "latest", a pre-release never is);
+ if the build failed or the assets must be regenerated, re-run the workflow
  from the Actions tab (`Release` workflow, "Run workflow", give the tag);
  it rebuilds and re-attaches the assets to the existing release (which can
  go back to draft state: check the releases page and re-publish if needed);
+ Packagist indexes git tags, not GitHub releases: composer users see the
  version as soon as the tag is pushed, whatever the state of the GitHub
  release. This is also why test tags must never be pushed to this
  repository.
