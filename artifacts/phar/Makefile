# Build phar
build-phar:
	@mkdir -p ${BUILD_DIR}
	@echo Copying sources
	@mkdir -p /tmp/phpmetrics-build
	@cp * -R /tmp/phpmetrics-build
	@cp .semver /tmp/phpmetrics-build/.semver
	@rm -Rf /tmp/phpmetrics-build/vendor /tmp/phpmetrics-build/composer.lock

	@echo Installing dependencies
	@cd /tmp/phpmetrics-build && composer update --no-dev --optimize-autoloader --prefer-dist

	@echo Building phar
	@cd /tmp/phpmetrics-build && php artifacts/phar/build.php
	@cp /tmp/phpmetrics-build/build/phpmetrics.phar ${BUILD_DIR}/phpmetrics.phar
	@rm -Rf /tmp/phpmetrics-build
