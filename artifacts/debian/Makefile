RELEASE_VERSION=$(shell echo ${CURRENT_TAG}|sed -r s/v//)

# Build deb
#build-deb: build-phar
build-deb:
	@echo Copying phar
	@mkdir -p artifacts/debian/bin
	@cp ${BUILD_DIR}/phpmetrics.phar artifacts/debian/bin/phpmetrics

	@echo "Building .deb (${RELEASE_VERSION})"
	cd artifacts && dpkg-buildpackage -us -uc
	@echo moving deb
	mv phpmetrics*.deb ${BUILD_DIR}/phpmetrics.deb
	mv phpmetrics*.dsc ${BUILD_DIR}/phpmetrics.dsc
	mv phpmetrics*.tar.gz ${BUILD_DIR}/phpmetrics.tar.gz
	mv phpmetrics*.changes ${BUILD_DIR}/phpmetrics.changes
	rm -Rf artifacts/debian/phpmetrics artifacts/debian/tmp artifacts/debian/bin artifacts/debian/*.log

changelog-deb:
	@echo "phpmetrics ($(RELEASE_VERSION)) unstable; urgency=low" >/tmp/changelog
	@echo >> artifacts/debian/changelog
	@git log $(CURRENT_TAG)...HEAD --pretty=format:'   * %s ' --reverse >> /tmp/changelog
	@echo >> /tmp/changelog
	@echo >> /tmp/changelog
	@echo  "  -- Jean-François Lépine <lepinejeanfrancois@yahoo.fr>  $(shell date --rfc-2822)" >> /tmp/changelog
	@echo >> /tmp/changelog
	@# prepend to changelog
	@cat /tmp/changelog|cat - artifacts/debian/changelog > /tmp/out && mv /tmp/out artifacts/debian/changelog
	@echo >> artifacts/debian/changelog