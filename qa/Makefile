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

# Install composer dependencies only if composer.json is newer than vendors.
# See: https://www.reddit.com/r/PHP/comments/7sltmh/makefile_for_lazy_developers/dt5rba0/
.PHONY: vendor
vendor: composer.json composer.lock build-qa-tools
	@docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project phpmetrics_tools /bin/sh -c "composer validate && composer install"

.PHONY: vendor-update
vendor-update: build-qa-tools
	@docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project phpmetrics_tools /bin/sh -c "composer update"

# Run unit tests
.PHONY: test
test: build-qa-tools
	@docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project phpmetrics_tools phpunit -c ./qa/phpunit.xml.dist

# PHP Infection (testing unit tests with code mutations)
.PHONY: infection
infection: test
	@docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project --memory=1g phpmetrics_tools infection --configuration=./qa/infection.json.dist --coverage=reports/phpunit --show-mutations --threads=$(shell nproc)

# Codesniffer check
.PHONY: phpcs
phpcs: build-qa-tools
	@-docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project -v $$PWD/qa/phpcs.xml.dist:/opt/phpcs/ruleset.xml phpmetrics_tools phpcs src/ tests/ --standard=/opt/phpcs/ruleset.xml --extensions=php -n

# Codesniffer fix
.PHONY: phpcbf
phpcbf: build-qa-tools
	@docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project -v $$PWD/qa/phpcs.xml.dist:/opt/phpcs/ruleset.xml phpmetrics_tools phpcbf src/ tests/ --standard=/opt/phpcs/ruleset.xml --extensions=php -n

# Psalm (vimeo)
.PHONY: psalm
psalm: build-qa-tools
	@-docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project phpmetrics_tools psalm -c /opt/project/qa/psalm.xml.dist --threads=$(shell nproc)

# PHPStan
.PHONY: phpstan
phpstan: build-qa-tools
	@-docker run --rm -it -u$(shell id -u):0 -v $$PWD:/opt/project phpmetrics_tools phpstan analyse --configuration=qa/phpstan.neon.dist

# Qodana
.PHONY: qodana
qodana:
	@mkdir -p qa/reports/qodana
	@chmod u+w qa/reports/qodana
	@-docker run --rm -it -u$(shell id -u):0 -v $$PWD:/data/project \
	    $(ls qa/qodana/plugins | xargs -I {} echo "--volume $$PWD/qa/qodana/plugins/{}:/opt/idea/plugins/{}" | tr '\n' ' ') \
	    $(ls qa/qodana/options | xargs -I {} echo "--volume $$PWD/qa/qodana/options/{}:/root/.config/idea/options/{}" | tr '\n' ' ') \
	    -v $$PWD/qa/qodana/profile.xml.dist:/data/profile.xml \
	    -v $$PWD/qa/qodana/qodana.yaml.dist:/data/project/qodana.yaml \
	    -v $$PWD/qa/reports/qodana:/data/results \
	    --env _JAVA_OPTIONS=-Xmx2048m \
	    --pull always \
	    jetbrains/qodana-php \
	    --source-directory src \
	    --save-report
	@rm -rf $$PWD/qodana.yaml

# All QA
.PHONY: qa
qa: vendor test phpcs psalm phpstan qodana
