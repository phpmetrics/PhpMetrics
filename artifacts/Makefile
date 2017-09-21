CURRENT_TAG=$(shell git tag |tail -n 2 |head -n 1)
PREVIOUS_TAG=$(shell git tag |tail -n 1)
BUILD_DIR=releases

include artifacts/phar/Makefile
include artifacts/debian/Makefile

prepare-build:
	@# Disable the deletion of all releases packages as task build-debian is disabled.
	@#rm -Rf ${BUILD_DIR}
	@#mkdir ${BUILD_DIR}
	@# Only remove the phar that must be replaced by the new release.
	@rm -f ${BUILD_DIR}/phpmetrics.phar

build: prepare-build build-phar #build-deb

