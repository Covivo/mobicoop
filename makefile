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
	@make -s start-containers
	@make -s install-api
	@make -s install-client
	@make -s init-api
	@make -s start-client

build:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink) Building docker services...)
	$(info $(pink)------------------------------------------------------$(reset))
	@docker-compose build

start-containers:
	$(info $(green)------------------------------------------------------)
	$(info $(green) Start containers...)
	$(info $(green)------------------------------------------------------$(reset))
	@docker-compose up -d

install-api:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink) Installing API dependencies...)
	$(info $(pink)------------------------------------------------------$(reset))
	@docker exec -it mobicoop_platform_api_php /bin/zsh -c "cd tools/php-cs-fixer; composer install; cd ../php-mess-detector; composer install"

install-client:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink) Installing client dependencies...)
	$(info $(pink)------------------------------------------------------$(reset))
	@docker exec -it mobicoop_platform_client_php /bin/zsh -c "npm install; composer install"

init-api:
	$(info $(violet)------------------------------------------------------)
	$(info $(violet) Init API...)
	$(info $(violet)------------------------------------------------------$(reset))
	@docker exec mobicoop_platform_api_php docker/api/init-api.sh

start-client:
	@docker exec -d mobicoop_platform_client_php /bin/zsh -c "npm run compile-and-watch-vue"

start: start-containers start-client

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

go-client:
	$(info $(green)------------------------------------------------------)
	$(info $(green) Go into Client...)
	$(info $(green)------------------------------------------------------$(reset))
	@docker exec -ti mobicoop_platform_client_php /bin/zsh
