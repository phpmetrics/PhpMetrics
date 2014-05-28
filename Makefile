build: test
	php build.php

test:
	./vendor/bin/phpunit -c phpunit.xml.dist
