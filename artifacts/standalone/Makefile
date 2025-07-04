build-standalone: build-standalone-linux

build-standalone-linux:
	mkdir -p ${BUILD_DIR}
	curl -fsSL -o spc.tgz https://dl.static-php.dev/static-php-cli/spc-bin/nightly/spc-linux-x86_64.tar.gz && tar -zxvf spc.tgz && rm spc.tgz
	./spc download --with-php=8.4 --for-extensions "apcu,phar,curl,dom,fileinfo,filter,intl,mbstring,mysqlnd,openssl,tokenizer,zlib" --prefer-pre-built
	./spc install-pkg upx
	./spc build --build-micro "apcu,phar,curl,dom,fileinfo,filter,intl,mbstring,mysqlnd,openssl,tokenizer,zlib" --with-upx-pack
	./spc micro:combine ${BUILD_DIR}/phpmetrics.phar --output=${BUILD_DIR}/phpmetrics-linux-x86_64

