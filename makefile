## This makefile is simply shortcurts for mobicoop docker

pink:=$(shell tput setaf 200)
blue:=$(shell tput setaf 27)
green:=$(shell tput setaf 118)
violet:=$(shell tput setaf 057)
reset:=$(shell tput sgr0)

ifeq ($(shell uname),Darwin)
  os=darwin
else
  os=linux
endif

install:
	$(info $(pink)Creating build/cache folders$(reset))
	@mkdir -p build/cache ;\

	$(info $(pink)Creating build/cache folders$(reset))
	@mkdir -p build/cache;\

	$(info $(pink)------------------------------------------------------)
	$(info $(pink)Make ($(os)): Installing monorepo root deps...)
	$(info $(pink)------------------------------------------------------$(reset))

	@docker-compose -f docker-compose-builder-$(os).yml run --rm install
	@make -s install-deps

install-deps:
	$(info $(green)------------------------------------------------------)
	$(info $(green)Make ($(os)): Installing api-client deps...)
	$(info $(green)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm install-all

fixtures:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink)Make ($(os)): Generating fixtures...)
	$(info $(pink)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm fixtures

start:
	$(info Make ($(os)): Starting Mobicoop-platform environment containers.)
	@docker-compose -f docker-compose-$(os).yml up -d

stop:
	$(info Make ($(os)): Stopping Mobicoop-platform environment containers.)
	@docker-compose -f docker-compose-$(os).yml stop

status:
	@docker ps -a | grep mobicoop_platform
	@docker ps -a | grep mobicoop_db

restart:
	$(info Make ($(os)): Restarting Mobicoop-platform environment containers.)
	@make -s stop
	@make -s start

reload:
	$(info Make ($(os)): Restarting Mobicoop-platform environment containers.)
	@make -s stop
	@make -s remove
	@make -s start

remove:
	$(info Make ($(os)): Stopping Mobicoop-platform environment containers.)
	@docker-compose -f docker-compose-$(os).yml rm -f

clean:
	@make -s stop
	@docker-compose -f docker-compose-$(os).yml down -v --rmi all
	$(info $(pink)------------------------------------------------------)
	$(info $(pink)Drop all deps + containers + volumes)
	$(info $(pink)------------------------------------------------------$(reset))
	sudo rm -rf node_modules api/vendor client/vendor client/node_modules

clean-db:
	sudo rm -rf .mariadb-data

migrate:
	$(info $(builder)------------------------------------------------------)
	$(info $(builder)Make ($(os)): Generating fixtures...)
	$(info $(builder)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm fixtures

update:
	@make -s stop
	git pull
	@make -s install
	@make -s start
	sleep 20
	@make -s db-migrate

pull:
	@make -s stop
	git pull
	@make -s start
	sleep 20
	@make -s db-migrate

db-migrate:
	$(info $(builder)------------------------------------------------------)
	$(info $(builder)Make ($(os)): DB Migration...)
	$(info $(builder)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm db-migrate

db-diff:
	$(info $(builder)------------------------------------------------------)
	$(info $(builder)Make ($(os)): DB Diff...)
	$(info $(builder)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm db-diff

db-fixtures-basic:
	$(info $(builder)------------------------------------------------------)
	$(info $(builder)Make ($(os)): DB Basic Fixtures...)
	$(info $(builder)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm db-fixtures-basic

db-fixtures-solidary:
	$(info $(builder)------------------------------------------------------)
	$(info $(builder)Make ($(os)): DB Solidary Fixtures...)
	$(info $(builder)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm db-fixtures-solidary

app-geography-territory-link-batch:
	$(info $(builder)-----------------------------------------------------------)
	$(info $(builder)Make ($(os)): Command app:geography:territory-link-batch...)
	$(info $(builder)-----------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm app-geography-territory-link-batch

app-carpool-proof-batch:
	$(info $(builder)------------------------------------------------)
	$(info $(builder)Make ($(os)): Command app:carpool:proof-batch...)
	$(info $(builder)------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm app-carpool-proof-batch

logs:
	$(info $(green)------------------------------------------------------)
	$(info $(green)Mobicoop Logs)
	$(info $(green)------------------------------------------------------$(reset))
	@docker logs -f mobicoop_platform;\

logs-db:
	$(info $(green)------------------------------------------------------)
	$(info $(green)DB Logs)
	$(info $(green)------------------------------------------------------$(reset))
	@docker logs -f mobicoop_db;

go-platform:
	@docker exec -it mobicoop_platform zsh

go-db:
	@docker exec -it mobicoop_db bash

connect:
	@docker exec -it mobicoop_platform /bin/bash
