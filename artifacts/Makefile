CURRENT_TAG=$(shell git tag |tail -n 2 |head -n 1)
PREVIOUS_TAG=$(shell git tag |tail -n 1)

include artifacts/phar/Makefile
include artifacts/debian/Makefile

prepare-build:
	@rm -Rf build
	@mkdir build

build: prepare-build build-phar build-deb
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/`semver tag`/g" .github/ISSUE_TEMPLATE.md

