.PHONY: rebuild-qa-tools
rebuild-qa-tools:
	@docker rmi -f phpmetrics_tools >/dev/null 2>&1 ||:
	@make build-qa-tools

.PHONY: build-qa-tools
build-qa-tools:
	@if [ $$(docker image ls phpmetrics_tools | grep -ce "phpmetrics_tools") -ne 1 ]; then\
        echo "Installing PhpMetrics tools…";\
        docker build --pull -t phpmetrics_tools -f ./qa/Dockerfile . ;\
        echo "Done";\
    fi

prepare-qa-cache:
	@mkdir -p qa/cache

# Install composer dependencies only if composer.json is newer than vendors.
# See: https://www.reddit.com/r/PHP/comments/7sltmh/makefile_for_lazy_developers/dt5rba0/
.PHONY: vendor
vendor: composer.json composer.lock build-qa-tools
	@docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project phpmetrics_tools /bin/sh -c "composer validate && composer install"

.PHONY: vendor-update
vendor-update: build-qa-tools
	@docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project phpmetrics_tools /bin/sh -c "composer update"

# Run unit tests
.PHONY: phpunit
phpunit: build-qa-tools prepare-qa-cache
	@docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project phpmetrics_tools phpunit -c ./qa/phpunit.xml.dist --cache-result $(SRC)
.PHONY: test #Alias.
test: phpunit

# PHP Infection (testing unit tests with code mutations)
.PHONY: infection
infection: phpunit
	@-docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project --memory=1g phpmetrics_tools infection --configuration=./qa/infection.json.dist --coverage=reports/phpunit --skip-initial-tests --show-mutations --threads=$(shell nproc)

# Codesniffer check
.PHONY: phpcs
phpcs: build-qa-tools prepare-qa-cache
	@-docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project -v ${HOST_PWD}/qa/phpcs.xml.dist:/opt/phpcs/ruleset.xml phpmetrics_tools phpcs src/ tests/ --standard=/opt/phpcs/ruleset.xml --extensions=php -n

# Codesniffer fix
.PHONY: phpcbf
phpcbf: build-qa-tools prepare-qa-cache
	@docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project -v ${HOST_PWD}/qa/phpcs.xml.dist:/opt/phpcs/ruleset.xml phpmetrics_tools phpcbf src/ tests/ --standard=/opt/phpcs/ruleset.xml --extensions=php -n

# Psalm (vimeo)
.PHONY: psalm
psalm: build-qa-tools prepare-qa-cache
	@-docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project phpmetrics_tools psalm -c /opt/project/qa/psalm.xml.dist --threads=$(shell nproc)

# PHPStan
.PHONY: phpstan
phpstan: build-qa-tools prepare-qa-cache
	@-docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/opt/project phpmetrics_tools phpstan analyse --configuration=qa/phpstan.neon.dist --xdebug # --generate-baseline qa/phpstan-baseline.neon

# Qodana
.PHONY: qodana
qodana:
	@mkdir -p qa/cache/qodana
	@mkdir -p qa/reports/qodana
	@chmod u+w qa/reports/qodana
	@-docker run --rm -it -u$(shell id -u):0 -v ${HOST_PWD}:/data/project \
	    $(ls qa/qodana/plugins | xargs -I {} echo "--volume ${HOST_PWD}/qa/qodana/plugins/{}:/opt/idea/plugins/{}" | tr '\n' ' ') \
	    $(ls qa/qodana/options | xargs -I {} echo "--volume ${HOST_PWD}/qa/qodana/options/{}:/root/.config/idea/options/{}" | tr '\n' ' ') \
	    -v ${HOST_PWD}/qa/qodana/profile.xml.dist:/data/profile.xml \
	    -v ${HOST_PWD}/qa/qodana/qodana.yaml.dist:/data/project/qodana.yaml \
	    -v ${HOST_PWD}/qa/reports/qodana:/data/results \
	    -v ${HOST_PWD}/qa/cache/qodana:/data/cache \
	    --env _JAVA_OPTIONS=-Xmx2048m \
	    --pull always \
	    jetbrains/qodana-php \
	    --source-directory src \
	    --baseline /data/project/qa/qodana.sarif.json \
	    --cache-dir=/data/cache \
	    --save-report
	@rm -rf ${HOST_PWD}/qodana.yaml

# All QA
.PHONY: qa
qa: vendor infection phpcs psalm phpstan qodana
