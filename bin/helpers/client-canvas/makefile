

blueDark:=$(shell tput setaf 21)
blue:=$(shell tput setaf 33)

ifeq ($(shell uname),Darwin)
  os=darwin
else
  os=linux
endif

install:
	$(info $(blue)Creating build/cache folders$(reset))
	@mkdir -p build/cache ;\

	$(info $(blue)Creating build/cache folders$(reset))
	@mkdir -p build/cache;\

	$(info $(blue)------------------------------------------------------)
	$(info $(blue)$$NAME$$ ($(os)): Installing node deps...)
	$(info $(blue)------------------------------------------------------$(reset))

	@docker-compose -f docker-compose-builder-$(os).yml run --rm install
	@make -s install-vendor

install-vendor:

	$(info $(blue)------------------------------------------------------)
	$(info $(blue)$$NAME$$ ($(os)): Installing php deps...)
	$(info $(blue)------------------------------------------------------$(reset))

	@docker-compose -f docker-compose-builder-$(os).yml run --rm install-vendor


fixtures:
	$(info $(blue)------------------------------------------------------)
	$(info $(blue)$$NAME$$ ($(os)): Generating fixtures...)
	$(info $(blue)------------------------------------------------------$(reset))
	@docker-compose -f docker-compose-builder-$(os).yml run --rm fixtures

start:
	$(info $$NAME$$ ($(os)): Starting $$NAME$$ environment containers.)
	@docker-compose -f docker-compose-$(os).yml up -d
 
stop:
	$(info $$NAME$$ ($(os)): Stopping $$NAME$$ environment containers.)
	@docker-compose -f docker-compose-$(os).yml stop 

status:
	@docker ps -a | grep $$NAME$$_platform
	@docker ps -a | grep $$NAME$$_db
 
restart:
	$(info $$NAME$$ ($(os)): Restarting $$NAME$$ environment containers.)
	@make -s stop
	@make -s start

reload:
	$(info Make ($(os)): Restarting $$NAME$$ environment containers.)
	@make -s stop
	@make -s remove
	@make -s start

remove:
	$(info $$NAME$$ ($(os)): Stopping $$NAME$$ environment containers.)
	@docker-compose -f docker-compose-$(os).yml down -v 
 
clean:
	@make -s stop
	@make -s remove
	$(info $(blue)------------------------------------------------------)
	$(info $(blue)Drop all deps + containers + volumes)
	$(info $(blue)------------------------------------------------------$(reset))
	sudo rm -rf node_modules vendor

logs: 
	$(info $(blueDark)------------------------------------------------------)
	$(info $(blueDark)$$NAME$$+DB Logs)
	$(info $(blueDark)------------------------------------------------------$(reset))
	@docker logs -f $$NAME$$

go-platform:
	@docker exec -it $$NAME$$ zsh