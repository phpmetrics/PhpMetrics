# Latest tag reachable from HEAD (git tag alone sorts alphabetically: v3.0.0rc7 > v2.10.0)
CURRENT_TAG=$(shell git describe --tags --abbrev=0)
BUILD_DIR=releases

include artifacts/phar/Makefile
include artifacts/debian/Makefile
include artifacts/standalone/Makefile

prepare-build:
	@# Disable the deletion of all releases packages as task build-debian is disabled.
	@#rm -Rf ${BUILD_DIR}
	@#mkdir ${BUILD_DIR}
	@# Only remove the phar that must be replaced by the new release.
	@rm -f ${BUILD_DIR}/phpmetrics.phar

build: prepare-build build-phar build-deb build-standalone
	./vendor/bin/phpunit -c phpunit.xml.dist --group=binary &&	echo "Done"

clear:
	rm -Rf *.buildinfo
	rm -Rf bin/build
