.PHONY: docker build check-tag

include artifacts/Makefile

# Ensure the tag starts with "v" and follows the vX.Y.Z format (optional rcN/alphaN/betaN suffix)
check-tag:
	@echo "$(TAG)" | grep -Eq '^v[0-9]+\.[0-9]+\.[0-9]+([.-]?(alpha|beta|rc)[0-9]+)?$$' \
		|| { echo "Error: invalid TAG '$(TAG)' (expected format: vX.Y.Z, e.g. v2.10.0)"; exit 1; }

# Run unit tests
test:
	./vendor/bin/phpunit -c phpunit.xml.dist --exclude-group binary

# Compatibility check
compatibility:
	(docker run --rm -v `pwd`:/www --workdir=/www  php:5.6-cli find src -iname "*.php" -exec php -l {} \; |grep -v "Php7NodeTraverser.php" | grep -v "No syntax errors detected") && echo OK

# Used for tag releasing
# Don't use directly, use `make release` instead
tag: check-tag
	echo "New release: $(TAG)"
	echo Releasing sources
	sed -i -r "s/(v[0-9]+\.[0-9]+\.[0-9]+)/$(TAG)/g" \
		.github/ISSUE_TEMPLATE/Bug_report.md \
		.github/ISSUE_TEMPLATE/Feature_request.md \
		src/functions.php \
		artifacts/debian/control \
		artifacts/bintray.json \
		doc/installation.md
	sed -i -r "s/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/`date +%Y-%m-%d`/g" artifacts/bintray.json
	make changelog-deb


# Tag git with last release
new_git_version: build tag
	git add .github/ISSUE_TEMPLATE/Bug_report.md .github/ISSUE_TEMPLATE/Feature_request.md src/functions.php doc/installation.md artifacts/* releases/*
	git commit -m "releasing `semver tag`"
	git tag $(TAG) -m "releasing $(TAG)"
	git push -u origin master
	git push origin $(TAG)

docker:
	docker build -t phpmetrics/releasing ./artifacts/releasing

# Publish new release. Usage:
#   make tag TAG=x.y.z
release: check-tag docker
	docker run -it --rm --mount type=bind,source=$$SSH_AUTH_SOCK,target=/ssh-agent --env SSH_AUTH_SOCK=/ssh-agent -v ~/.gitconfig:/etc/gitconfig -v $(PWD):/app -w /app phpmetrics/releasing make new_git_version TAG=$(TAG)
