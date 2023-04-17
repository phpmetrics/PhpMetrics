.PHONY: release

export HOST_PWD ?=$(shell pwd)

include artifacts/Makefile
include qa/Makefile

# Used for tag releasing
# Don't use directly, use `make release` instead
tag:
	@if [ '' != "$(VERSION)" ]; then semver inc $(VERSION); fi
	@if [ '' != "$(SPECIAL)" ]; then semver special "$(SPECIAL)"; fi
	@echo "New release: `semver tag`"
	@echo Releasing sources
	@sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+[^\"]*)/`semver tag`/g" artifacts/bintray.json
	@sed -i -r "s/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/`date +%Y-%m-%d`/g" artifacts/bintray.json

# Tag git with last release
# Don't use directly, use `make release` instead
new_git_version: tag build
	git add .semver artifacts/bintray.json releases/*
	git commit -m "releasing `semver tag`"
	git tag `semver tag` -m "releasing `semver tag`"
	git push -u origin master
	git push origin `semver tag`

docker:
	docker build -t phpmetrics/releasing ./docker/releasing

# Publish new release. Usage:
#   make release VERSION=(major|minor|patch) SPECIAL=…
release: qa docker
	docker run -it --rm --mount type=bind,source=$$SSH_AUTH_SOCK,target=/ssh-agent --env SSH_AUTH_SOCK=/ssh-agent -v ~/.gitconfig:/etc/gitconfig -v /var/run/docker.sock:/var/run/docker.sock -v ${HOST_PWD}:/app -w /app --env HOST_PWD=${HOST_PWD} phpmetrics/releasing make new_git_version VERSION=$(VERSION) SPECIAL=$(SPECIAL)
