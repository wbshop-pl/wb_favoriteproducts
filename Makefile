build-module-zip: build-composer build-zip

build-zip:
	rm -rf wb_favoriteproducts.zip
	cp -Ra $(PWD) /tmp/wb_favoriteproducts
	rm -rf /tmp/wb_favoriteproducts/config_*.xml
	rm -rf /tmp/wb_favoriteproducts/_theme_dev/node_modules
	rm -rf /tmp/wb_favoriteproducts/.github
	rm -rf /tmp/wb_favoriteproducts/.gitignore
	rm -rf /tmp/wb_favoriteproducts/.php-cs-fixer.cache
	rm -rf /tmp/wb_favoriteproducts/.php-cs-fixer.dist.php
	rm -rf /tmp/wb_favoriteproducts/.git
	mv -v /tmp/wb_favoriteproducts $(PWD)/wb_favoriteproducts
	zip -r wb_favoriteproducts.zip wb_favoriteproducts
	rm -rf $(PWD)/wb_favoriteproducts

build-composer:
	composer install --no-dev -o

