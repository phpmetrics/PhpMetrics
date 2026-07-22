# Build phar
build-phar: test
	@mkdir -p ${BUILD_DIR}
	@echo Copying sources
	@mkdir -p /tmp/phpmetrics-build
	@cp * -R /tmp/phpmetrics-build
	@rm -Rf /tmp/phpmetrics-build/vendor /tmp/phpmetrics-build/composer.lock /tmp/phpmetrics-build/tooling /tmp/phpmetrics-build/releases /tmp/phpmetrics-build/downloads /tmp/phpmetrics-build/build

	@echo Releasing phar
	@sed -i "s/<VERSION>/$CURRENT_TAG/g" /tmp/phpmetrics-build/artifacts/phar/build.php

	@echo Installing dependencies
	@# Pin the platform so the phar keeps supporting PHP 7.0+ (php-parser v4),
	@# whatever the PHP version of the machine building it. Dev dependencies
	@# are dropped: even with --no-dev, composer must be able to resolve them,
	@# and modern phpunit cannot resolve against platform 7.0.
	@cd /tmp/phpmetrics-build && composer config platform.php 7.0.8
	@cd /tmp/phpmetrics-build && composer remove --dev --no-update --no-interaction phpunit/phpunit
	@cd /tmp/phpmetrics-build && composer install --no-dev --optimize-autoloader --prefer-dist

	@echo Building phar
	@cd /tmp/phpmetrics-build && php -d phar.readonly=0 artifacts/phar/build.php
	@cp /tmp/phpmetrics-build/build/phpmetrics.phar ${BUILD_DIR}/phpmetrics.phar
	@rm -Rf /tmp/phpmetrics-build

