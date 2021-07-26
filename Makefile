socialvoidlib_src_dir = src/SocialvoidLib
socivlvoidservice_src_dir = src/SocialvoidService
socivlvoidrpc_src_dir = src/SocialvoidRPC
install_branch = production
build_dir = build
socialvoidlib_name = net.intellivoid.socialvoidlib
socialvoidservice_name = net.intellivoid.socialvoid_service
socialvoidrpc_name = net.intellivoid.socialvoid_rpc
query_workers = SocialvoidQueryService
update_workers = SocialvoidUpdateService
main_worker = $(socivlvoidservice_src_dir)/worker.php
runtime_version = 8.0
docs_runtime_version = 7.4
docs_phar_location = ~/phpdoc.phar

clean:
	rm -rf "$(build_dir)"

update:
	ppm --generate-package="$(socialvoidlib_src_dir)"
	ppm --generate-package="$(socivlvoidservice_src_dir)"
	ppm --generate-package="$(socivlvoidrpc_src_dir)"
	#php$(docs_runtime_version) $(docs_phar_location)

build:
	mkdir build
	ppm --no-intro --cerror --lwarning --verbose --compile="$(socialvoidlib_src_dir)" --directory="$(build_dir)"
	ppm --no-intro --cerror --lwarning --verbose --compile="$(socivlvoidservice_src_dir)" --directory="$(build_dir)"
	ppm --no-intro --cerror --lwarning --verbose --compile="$(socivlvoidrpc_src_dir)" --directory="$(build_dir)"

install:
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidlib_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidservice_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidrpc_name).ppm" --branch="$(install_branch)"

install_fast:
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidlib_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidservice_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidrpc_name).ppm" --branch="$(install_branch)"

start_service:
	ppm --main="$(socialvoidservice_name)" --version="latest" --runtime-version="$(runtime_version)"

start_rpc:
	ppm --main="$(socialvoidrpc_name)" --version="latest" --runtime-version="$(runtime_version)"