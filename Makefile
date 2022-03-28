.PHONY: docker build

include artifacts/Makefile
include qa/Makefile

CURRENT_TAG=$(shell git tag | tail -n 2 | head -n 1)

.PHONY: build
build: build-phar
	@#TODO: docker build --pull -t phpmetrics/phpmetrics:${CURRENT_TAG}
	@docker build --pull -t phpmetrics/phpmetrics:3.0.0 .

# Build phar
.PHONY: build-phar
build-phar: qa
	@# Remove the phar that must be replaced by the new release.
	@rm -f releases/phpmetrics.phar
	@mkdir -p releases
	@echo Copying sources
	@mkdir -p /tmp/phpmetrics-build
	@cp * -R /tmp/phpmetrics-build
	@rm -Rf /tmp/phpmetrics-build/vendor /tmp/phpmetrics-build/composer.lock

# Used for tag releasing
# Don't use directly, use `make release` instead
tag:
	@semver inc $(VERSION)
	@echo "New release: `semver tag`"
	@echo Releasing sources
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/`semver tag`/g" \
		.github/ISSUE_TEMPLATE/Bug_report.md \
		.github/ISSUE_TEMPLATE/Feature_request.md \
		src/functions.php \
		artifacts/debian/control \
		artifacts/bintray.json \
		doc/installation.md
	@sed -i -r "s/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/`date +%Y-%m-%d`/g" artifacts/bintray.json
	@make changelog-deb
	@echo Installing dependencies
	@cd /tmp/phpmetrics-build && docker run --rm -it -u$(shell id -u):0 -v $$PWD:/app -w /app --entrypoint=composer pharbuilder_phpmetrics update --no-dev --optimize-autoloader --prefer-dist

# Tag git with last release
new_git_version: build tag
	git add .semver .github/ISSUE_TEMPLATE/Bug_report.md .github/ISSUE_TEMPLATE/Feature_request.md src/functions.php doc/installation.md artifacts/* releases/*
	git commit -m "releasing `semver tag`"
	git tag `semver tag` -m "releasing `semver tag`"
	git push -u origin master
	git push origin `semver tag`

docker:
	docker build -t phpmetrics/releasing ./docker/releasing

# Publish new release. Usage:
#   make tag VERSION=(major|minor|patch)
release: docker
	docker run -it --rm --mount type=bind,source=$$SSH_AUTH_SOCK,target=/ssh-agent --env SSH_AUTH_SOCK=/ssh-agent -v ~/.gitconfig:/etc/gitconfig -v $(PWD):/app -w /app phpmetrics/releasing make new_git_version VERSION=$(VERSION)
