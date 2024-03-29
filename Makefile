DATE=`date +%Y-%m-%d`
DATETIME = `date +%Y-%m-%d_%H-%M-%S`

define highlight
	@echo "\033[1;32m$1\033[0m"
endef

.PHONY: test
test:
	@./vendor/bin/phpunit

.PHONY: composer_reload
composer_reload:
	@composer clear-cache
	@composer update -vv
	@composer dump-autoload

.PHONY: composer_develop
composer_develop:
	@composer install -vv --prefer-dist --optimize-autoloader

.PHONY: composer_public
composer_public:
	@composer install -vv --no-dev --prefer-dist --optimize-autoloader

.PHONY: check
check:
	$(call highlight,#### ---- composer diag ---- ####)
	@composer diag

.PHONY: insights
insights:
	@./vendor/bin/phpinsights analyse ./src
