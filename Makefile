REPLACE=`semver tag`

# Build phar
build: test
	@echo Copying sources
	@mkdir -p /tmp/phpmetrics-build
	@cp * -R /tmp/phpmetrics-build
	@rm -Rf /tmp/phpmetrics-build/vendor /tmp/phpmetrics-build/composer.lock
	
	@echo Releasing phar
	@sed -i "s/<VERSION>/`semver tag`/g" /tmp/phpmetrics-build/build.php

	@echo Installing dependencies
	@cd /tmp/phpmetrics-build && composer install --no-dev --optimize-autoloader --prefer-dist

	@echo Building phar
	@cd /tmp/phpmetrics-build && php build.php
	@cp /tmp/phpmetrics-build/build/phpmetrics.phar build/phpmetrics.phar
	@rm -Rf /tmp/phpmetrics-build
	
	@echo Testing phar
	./vendor/bin/phpunit -c phpunit.xml.dist --group=binary &&	echo "Done"

	@echo Releasing sources
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/`semver tag`/g" bin/phpmetrics


# Run unit tests
test:
	./vendor/bin/phpunit -c phpunit.xml.dist


# Publish new release. Usage:
#   make tag VERSION=(major|minor|patch)
# You need to install https://github.com/flazz/semver/ before
tag:
	@semver inc $(VERSION)
	@echo "New release: `semver tag`"


# Tag git with last release
release: build
	git add .semver build/phpmetrics.phar
	git commit -m "releasing `semver tag`"
	git tag `semver tag`
	git push -u origin master
	git push origin `semver tag`
