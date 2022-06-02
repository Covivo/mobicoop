pink:=$(shell tput setaf 200)
blue:=$(shell tput setaf 27)
green:=$(shell tput setaf 118)
violet:=$(shell tput setaf 057)
reset:=$(shell tput sgr0)

install:
	$(info $(green)------------------------------------------------------)
	$(info $(green) Installing components...)
	$(info $(green)------------------------------------------------------$(reset))
	@make -s build
	@make -s install-client
	@make -s start
	@make -s init-api
	@make -s init-client
	@make -s install-api

build:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink) Building docker services...)
	$(info $(pink)------------------------------------------------------$(reset))
	@docker-compose build

install-api:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink) Installing API dependencies...)
	$(info $(pink)------------------------------------------------------$(reset))
	@docker exec -it mobicoop_platform_api_php /bin/zsh -c "cd tools/php-cs-fixer; composer install; cd ../php-mess-detector; composer install"

install-client:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink) Installing client dependencies...)
	$(info $(pink)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder.yml run --rm install
	@docker-compose -f docker-compose-builder.yml run --rm install-all

start:
	$(info $(green)------------------------------------------------------)
	$(info $(green) Start components...)
	$(info $(green)------------------------------------------------------$(reset))
	@docker-compose up -d

init-api:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) Init API...)
	$(info $(violet)------------------------------------------------------$(reset))
	@docker exec mobicoop_platform_api_php docker/api/init-api.sh

init-client:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) Init Client...)
	$(info $(violet)------------------------------------------------------$(reset))
	@docker exec mobicoop_platform_client client/docker/init-client.sh

stop:
	$(info $(blue)------------------------------------------------------)
	$(info $(blue) Stop components...)
	$(info $(blue)------------------------------------------------------$(reset))
	@docker-compose down

db-fixtures-basic:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) DB Basic Fixtures...)
	$(info $(violet)------------------------------------------------------$(reset))
	@docker exec -it mobicoop_platform_api_php /bin/zsh -c "php bin/console doctrine:fixtures:load -n -v --append --group=basic"

db-fixtures-solidary:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) DB Solidary Fixtures...)
	$(info $(violet)------------------------------------------------------$(reset))
	@docker exec -it mobicoop_platform_api_php /bin/zsh -c "php bin/console doctrine:fixtures:load -n -v --append --group=solidary"

clean-api: clean-api-phpcs clean-api-phpmd

clean-api-phpcs:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) Fix my php code !)
	$(info $(violet)------------------------------------------------------$(RESET))
	@docker exec -it mobicoop_platform_api_php /srv/api/tools/php-cs-fixer/vendor/bin/php-cs-fixer fix src --rules=@PhpCsFixer

clean-api-phpmd:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) Analyze my php code !)
	$(info $(violet)------------------------------------------------------$(RESET))
	@docker exec -it mobicoop_platform_api_php /srv/api/tools/php-mess-detector/vendor/bin/phpmd /srv/api/src/ ansi /srv/api/tools/php-mess-detector/mobicoop.xml

logs:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) Logs... !)
	$(info $(violet)------------------------------------------------------$(RESET))
	@docker-compose logs -f

go-api:
	$(info $(green)------------------------------------------------------)
	$(info $(green) Go into API...)
	$(info $(green)------------------------------------------------------$(reset))
	@docker exec -ti mobicoop_platform_api_php /bin/zsh
