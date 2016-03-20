# Build phar
build-phar: test
	@echo Copying sources
	@mkdir -p /tmp/phpmetrics-build
	@cp * -R /tmp/phpmetrics-build
	@rm -Rf /tmp/phpmetrics-build/vendor /tmp/phpmetrics-build/composer.lock

	@echo Releasing phar
	@sed -i "s/<VERSION>/`semver tag`/g" /tmp/phpmetrics-build/artifacts/phar/build.php

	@echo Installing dependencies
	@cd /tmp/phpmetrics-build && composer install --no-dev --optimize-autoloader --prefer-dist

	@echo Building phar
	@cd /tmp/phpmetrics-build && php artifacts/phar/build.php
	@cp /tmp/phpmetrics-build/build/phpmetrics.phar build/phpmetrics.phar
	@rm -Rf /tmp/phpmetrics-build

	@echo Testing phar
	./vendor/bin/phpunit -c phpunit.xml.dist --group=binary &&	echo "Done"
