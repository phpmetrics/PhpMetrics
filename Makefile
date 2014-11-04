build: test
	mkdir -p /tmp/phpmetrics-build
	cp * -R /tmp/phpmetrics-build
	rm -Rf /tmp/phpmetrics-build/vendor /tmp/phpmetrics-build/composer.lock
	cd /tmp/phpmetrics-build && composer.phar install --no-dev --optimize-autoloader --prefer-dist && php build.php
	cp /tmp/phpmetrics-build/build/phpmetrics.phar build/phpmetrics.phar
	rm -Rf /tmp/phpmetrics-build
	./vendor/bin/phpunit -c phpunit.xml.dist --group=binary &&	echo "Done"

test:
	./vendor/bin/phpunit -c phpunit.xml.dist
