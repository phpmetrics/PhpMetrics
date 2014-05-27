build:
	jekyll build

deploy: build
	cap deploy
