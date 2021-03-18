clean:
	rm -rf build

update:
	ppm --generate-package="src/SocialvoidLib"
	ppm --generate-package="src/SocialvoidService"

build:
	mkdir build
	ppm --no-intro --compile="src/SocialvoidLib" --directory="build"
	ppm --no-intro --compile="src/SocialvoidService" --directory="build"

install:
	ppm --no-prompt --fix-conflict --install="build/net.intellivoid.socialvoidlib.ppm" --branch="production"
	ppm --no-prompt --fix-conflict --install="build/net.intellivoid.socialvoid_service.ppm" --branch="production"

install_fast:
	ppm --no-prompt --skip-dependencies --fix-conflict --install="build/net.intellivoid.socialvoidlib.ppm" --branch="production"
	ppm --no-prompt --skip-dependencies --fix-conflict --install="build/net.intellivoid.socialvoid_service.ppm" --branch="production"

start_service:
	ppm --main="net.intellivoid.socialvoid_service" --version="latest"

stop_service:
	pkill -f 'main=net.intellivoid.socialvoid_service'

stop_workers:
	pkill -f 'worker-name=SocialvoidQueryService'
	pkill -f 'worker-name=SocialvoidUpdateService'

stop_cache:
	pkill -f 'worker-name=SocialvoidCache'

debug_service:
	# Starts the bot, kills all the workers and focuses on one worker in STDOUT
	# Run with -i to ignore possible errors.
	make stop_service
	screen -dm bash -c 'ppm --main="net.intellivoid.socialvoid_service" --version="latest"'
	sleep 3
	make stop_workers
	php src/SocialvoidService/worker.php