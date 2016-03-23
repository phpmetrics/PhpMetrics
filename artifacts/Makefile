include artifacts/phar/Makefile

build: build-phar
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/`semver tag`/g" .github/ISSUE_TEMPLATE.md

