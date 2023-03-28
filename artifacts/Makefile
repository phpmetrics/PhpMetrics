CURRENT_TAG=$(shell git tag |tail -n 2 |head -n 1)
PREVIOUS_TAG=$(shell git tag |tail -n 1)
BUILD_DIR=releases

prepare-build:
	@# Only remove the phar that must be replaced by the new release.
	@rm -f ${BUILD_DIR}/phpmetrics.phar

build: prepare-build
