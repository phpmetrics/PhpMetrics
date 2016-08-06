include artifacts/Makefile

# Run unit tests
test:
	./vendor/bin/phpunit -c phpunit.xml.dist

# Publish new release. Usage:
#   make tag VERSION=(major|minor|patch)
# You need to install https://github.com/flazz/semver/ before
tag:
	@semver inc $(VERSION)
	@echo "New release: `semver tag`"
	@echo Releasing sources
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/`semver tag`/g" bin/phpmetrics
	@echo `semver tag` > src/Hal/Report/Html/template/version.php
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/`semver tag`/g" artifacts/bintray.json
	@sed -i -r "s/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/`date +%Y-%m-%d`/g" artifacts/bintray.json


# Tag git with last release
release: 
	git add .semver build/phpmetrics.phar bin/phpmetrics src/Hal/Report/Html/template/version.php artifacts/bintray.json .github/ISSUE_TEMPLATE.md
	git commit -m "releasing `semver tag`"
	git tag `semver tag`
	git push -u origin master
	git push origin `semver tag`
