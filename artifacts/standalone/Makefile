build-standalone: build-standalone-all

build-standalone-linux:
	mkdir -p ${BUILD_DIR}
	curl -fsSL -o spc.tgz https://dl.static-php.dev/static-php-cli/spc-bin/nightly/spc-linux-x86_64.tar.gz && tar -zxvf spc.tgz && rm spc.tgz
	./spc doctor --auto-fix
	./spc download --with-php=8.4 --for-extensions "apcu,phar,curl,dom,fileinfo,filter,intl,mbstring,mysqlnd,openssl,tokenizer,zlib" --prefer-pre-built
	./spc install-pkg upx
	./spc build --build-micro "apcu,phar,curl,dom,fileinfo,filter,intl,mbstring,mysqlnd,openssl,tokenizer,zlib" --with-upx-pack
	./spc micro:combine ${BUILD_DIR}/phpmetrics.phar --output=${BUILD_DIR}/phpmetrics-linux-x86_64

phpacker:
	curl -fsSL -o phpacker.zip https://github.com/phpacker/phpacker/releases/download/0.6.3/linux-x64.zip
	unzip phpacker.zip -d phpacker
	rm phpacker.zip
	chmod +x phpacker/linux-x64
	mv phpacker/linux-x64 /tmp/phpacker
	rm -rf phpacker
	mv /tmp/phpacker ./phpacker

build-standalone-all: phpacker
	./phpacker build all --php=8.4 --src=${BUILD_DIR}/phpmetrics.phar --dest=${BUILD_DIR}
	mv ${BUILD_DIR}/linux/linux-arm ${BUILD_DIR}/phpmetrics-linux-arm
	mv ${BUILD_DIR}/linux/linux-x64 ${BUILD_DIR}/phpmetrics-linux-x86_64
	mv ${BUILD_DIR}/windows/windows-x64.exe ${BUILD_DIR}/phpmetrics-windows-x86_64.exe
	mv ${BUILD_DIR}/mac/mac-x64 ${BUILD_DIR}/phpmetrics-macos-x86_64
	mv ${BUILD_DIR}/mac/mac-arm ${BUILD_DIR}/phpmetrics-macos-arm64
	rm -rf ${BUILD_DIR}/linux
	rm -rf ${BUILD_DIR}/windows
	rm -rf ${BUILD_DIR}/macos
	rm -rf ${BUILD_DIR}/phpacker
	rm -rf phpacker
