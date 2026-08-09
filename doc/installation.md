# Installation


## Composer

```bash
composer global require 'phpmetrics/phpmetrics'
```
    
Please note that the `~/.composer/vendor/bin` directory must be in your `$PATH`. For example in your `~/.bash_profile` (or `~/.bashrc`), add :

```bash
export PATH=~/.composer/vendor/bin:$PATH
```
    
## Phar

```bash
curl -fsSL -o phpmetrics.phar https://github.com/phpmetrics/PhpMetrics/releases/download/v2.10.0/phpmetrics.phar
chmod +x phpmetrics.phar && mv phpmetrics.phar /usr/local/bin/phpmetrics
```

## Apt (Debian, Ubuntu...)

```bash
curl -fsSL -o phpmetrics.deb https://github.com/phpmetrics/PhpMetrics/releases/download/v2.10.0/phpmetrics.deb
dpkg -i phpmetrics.deb
``` 

## Homebrew (macOS, Linux)

PhpMetrics is not in homebrew-core, it is published through its own tap:

```bash
brew tap phpmetrics/phpmetrics
brew trust --tap phpmetrics/phpmetrics
brew install phpmetrics
```

Recent versions of Homebrew refuse to load formulae from a tap they don't know,
hence the `brew trust` line. On older versions that command does not exist, and
`brew install` works straight after `brew tap`.

The formula installs the phar and depends on the `php` formula, so Homebrew
pulls PHP in if you don't have it already.

## PhpArch

```bash
yaourt install phpmetrics
```

## Docker

```bash
docker run --rm \
    --user $(id -u):$(id -g) \
    --volume /local/path:/project \
    herloct/phpmetrics [<options>]
```

