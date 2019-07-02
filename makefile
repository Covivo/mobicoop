## This makefile is simply shortcurts for mobicoop docker 


pink:=$(shell tput setaf 200)
blue:=$(shell tput setaf 27)
green:=$(shell tput setaf 118)
reset:=$(shell tput sgr0) 

install:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink)Make: Installing monorepo root deps...)
	$(info $(pink)------------------------------------------------------$(reset))
	docker-compose -f docker-compose.builder.yml run --rm install
	@make -s install-deps
	@make -s build-admin

install-deps:
	$(info $(green)------------------------------------------------------)
	$(info $(green)Installing api-client-admin deps...)
	$(info $(green)------------------------------------------------------$(reset))
	docker-compose -f docker-compose.builder.yml run --rm install-all

build-admin:
	$(info $(blue)------------------------------------------------------)
	$(info $(blue)Building admin...)
	$(info $(blue)------------------------------------------------------$(reset))
	docker-compose -f docker-compose.builder.yml run --rm build-admin

fixtures:
	$(info $(pink)------------------------------------------------------)
	$(info $(pink)Generating fixtures...)
	$(info $(pink)------------------------------------------------------$(reset))
	docker-compose -f docker-compose.builder.yml run --rm fixtures

start:
	$(info Make: Starting Mobicoop-plateform environment containers.)
	docker-compose up -d --always-recreate-deps --force-recreate  
 
stop:
	$(info Make: Stopping Mobicoop-plateform environment containers.)
	@docker-compose stop
 
restart:
	$(info Make: Restarting Mobicoop-plateform environment containers.)
	@make -s stop
	@make -s start

remove:
	$(info Make: Stopping Mobicoop-plateform environment containers.)
	@docker-compose down -v
 
clean:
	@make -s stop
	@make -s remove
	rm -rf node_modules api/vendor client/vendor client/node_modules admin/node_modules
	@docker system prune --volumes --force

logs: 
	docker logs -f --tail=30 mobicoop_platform | sed -e 's/^/[-- containerA1 --]/' &
	docker logs -f --tail=30 db | sed -e 's/^/[-- containerM2 --]/' & 