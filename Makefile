install_branch = production
build_dir = build
runtime_version = 8.0
docs_runtime_version = 7.4
docs_phar_location = ~/phpdoc.phar

socialvoidlib_src_dir = src/SocialvoidLib
socialvoidlib_name = net.intellivoid.socialvoidlib
socivlvoidservice_src_dir = src/SocialvoidService
socialvoidservice_name = net.intellivoid.socialvoid_service
socivlvoidrpc_src_dir = src/SocialvoidRPC
socialvoidrpc_name = net.intellivoid.socialvoid_rpc
socivlvoidadmin_src_dir = src/SocialvoidAdmin
socialvoidadmin_name = net.intellivoid.socialvoid_admin
socialvoid_src_dir = src/Socialvoid
socialvoid_name = net.intellivoid.socialvoid

clean:
	rm -rf "$(build_dir)"

update:
	ppm --generate-package="$(socialvoidlib_src_dir)"
	ppm --generate-package="$(socivlvoidservice_src_dir)"
	ppm --generate-package="$(socivlvoidrpc_src_dir)"
	ppm --generate-package="$(socivlvoidadmin_src_dir)"
	ppm --generate-package="$(socialvoid_src_dir)"
	php$(docs_runtime_version) $(docs_phar_location)

build:
	mkdir build
	ppm --no-intro --cerror --lwarning --compile="$(socivlvoidservice_src_dir)" --directory="$(build_dir)"
	ppm --no-intro --cerror --lwarning --compile="$(socivlvoidrpc_src_dir)" --directory="$(build_dir)"
	ppm --no-intro --cerror --lwarning --compile="$(socivlvoidadmin_src_dir)" --directory="$(build_dir)"
	ppm --no-intro --cerror --lwarning --compile="$(socialvoidlib_src_dir)" --directory="$(build_dir)"
	ppm --no-intro --cerror --lwarning --compile="$(socialvoid_src_dir)" --directory="$(build_dir)"

install:
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidlib_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidservice_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidrpc_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidadmin_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoid_name).ppm" --branch="$(install_branch)"

install_fast:
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidservice_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidrpc_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidadmin_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidlib_name).ppm" --branch="$(install_branch)"
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoid_name).ppm" --branch="$(install_branch)"

start_service:
	ppm --main="$(socialvoidservice_name)" --version="latest" --runtime-version="$(runtime_version)"

start_rpc:
	ppm --main="$(socialvoidrpc_name)" --version="latest" --runtime-version="$(runtime_version)"

start:
	ppm --main="$(socialvoidadmin_name)" --version="latest" --runtime-version="${runtime_version}"