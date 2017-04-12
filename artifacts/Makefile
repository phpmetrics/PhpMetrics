CURRENT_TAG=$(shell git tag |tail -n 2 |head -n 1)
PREVIOUS_TAG=$(shell git tag |tail -n 1)
BUILD_DIR=releases

include artifacts/phar/Makefile
include artifacts/debian/Makefile

prepare-build:
	@rm -Rf ${BUILD_DIR}
	@mkdir ${BUILD_DIR}

build: prepare-build build-phar #build-deb
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/${CURRENT_TAG}/g" .github/ISSUE_TEMPLATE.md

