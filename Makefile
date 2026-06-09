# Local build of an installable PrestaShop ZIP (CI does the same in release.yml).
build-module-zip: build-composer build-zip

build-zip:
	rm -rf wb_favoriteproducts.zip /tmp/wb_favoriteproducts
	cp -Ra $(PWD) /tmp/wb_favoriteproducts
	rm -rf /tmp/wb_favoriteproducts/config_*.xml \
		/tmp/wb_favoriteproducts/.github \
		/tmp/wb_favoriteproducts/docs \
		/tmp/wb_favoriteproducts/.gitignore \
		/tmp/wb_favoriteproducts/.php-cs-fixer.cache \
		/tmp/wb_favoriteproducts/.php-cs-fixer.dist.php \
		/tmp/wb_favoriteproducts/Makefile \
		/tmp/wb_favoriteproducts/.git
	mv -v /tmp/wb_favoriteproducts $(PWD)/wb_favoriteproducts
	zip -r wb_favoriteproducts.zip wb_favoriteproducts
	rm -rf $(PWD)/wb_favoriteproducts

build-composer:
	composer install --no-dev -o
