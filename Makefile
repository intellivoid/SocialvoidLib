install_branch = production
build_dir = build
runtime_version = 8.0

socialvoidlib_src_dir = src/SocialvoidLib
socialvoidlib_name = net.intellivoid.socialvoidlib
socialvoidservice_src_dir = src/SocialvoidService
socialvoidservice_name = net.intellivoid.socialvoid_service
socivlvoidrpc_src_dir = src/SocialvoidRPC
socialvoidrpc_name = net.intellivoid.socialvoid_rpc
socivlvoidadmin_src_dir = src/SocialvoidAdmin
socialvoidadmin_name = net.intellivoid.socialvoid_admin
socialvoid_src_dir = src/Socialvoid
socialvoid_name = net.intellivoid.socialvoid

#######################
# SocialvoidLib
#######################
socialvoidlib:
	make update_socialvoidlib
	ppm --no-intro --cerror --lwarning --compile="$(socialvoidlib_src_dir)" --directory="$(build_dir)"
update_socialvoidlib:
	ppm --generate-package="$(socialvoidlib_src_dir)"
install_socialvoidlib:
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidlib_name).ppm" --branch="$(install_branch)"
install_fast_socialvoidlib:
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidlib_name).ppm" --branch="$(install_branch)"

#######################
# SocialvoidService
#######################
socialvoid_service:
	make update_socialvoid_service
	ppm --no-intro --cerror --lwarning --compile="$(socialvoidservice_src_dir)" --directory="$(build_dir)"
update_socialvoid_service:
	ppm --generate-package="$(socialvoidservice_src_dir)"
install_socialvoid_service:
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidservice_name).ppm" --branch="$(install_branch)"
install_fast_socialvoid_service:
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidservice_name).ppm" --branch="$(install_branch)"

#######################
# SocialvoidRPC
#######################
socialvoid_rpc:
	ppm --no-intro --cerror --lwarning --compile="$(socivlvoidrpc_src_dir)" --directory="$(build_dir)"
update_socialvoid_rpc:
	ppm --generate-package="$(socivlvoidrpc_src_dir)"
install_socialvoid_rpc:
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidrpc_name).ppm" --branch="$(install_branch)"
install_fast_socialvoid_rpc:
	ppm --no-prompt  --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidrpc_name).ppm" --branch="$(install_branch)"


#######################
# Socialvoid-Admin
#######################
socialvoid_admin:
	ppm --no-intro --cerror --lwarning --compile="$(socivlvoidadmin_src_dir)" --directory="$(build_dir)"
update_socialvoid_admin:
	ppm --generate-package="$(socivlvoidadmin_src_dir)"
install_socialvoid_admin:
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoidadmin_name).ppm" --branch="$(install_branch)"
install_fast_socialvoidadmin:
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoidadmin_name).ppm" --branch="$(install_branch)"


#######################
# Socialvoid-Main
#######################
socialvoid:
	ppm --no-intro --cerror --lwarning --compile="$(socialvoid_src_dir)" --directory="$(build_dir)"
update_socialvoid:
	ppm --generate-package="$(socialvoid_src_dir)"
install_socialvoid:
	ppm --no-prompt --fix-conflict --install="$(build_dir)/$(socialvoid_name).ppm" --branch="$(install_branch)"
install_fast_socialvoid:
	ppm --no-prompt --skip-dependencies --fix-conflict --install="$(build_dir)/$(socialvoid_name).ppm" --branch="$(install_branch)"

#######################
# General make actions
#######################
clean:
	rm -rf "$(build_dir)"
update:
	make update_socialvoidlib update_socialvoid_service update_socialvoid_rpc update_socialvoid_admin update_socialvoid
update_p:
	make update_socialvoidlib & \
	make update_socialvoid_service & \
	make update_socialvoid_rpc & \
	make update_socialvoid_admin & \
	make update_socialvoid & \
	wait;
build:
	mkdir build
	make socialvoidlib socialvoid_service socialvoid_rpc socialvoid_admin socialvoid
build_p:
	mkdir build
	make socialvoidlib & \
	make socialvoid_service & \
	make socialvoid_rpc & \
	make socialvoid_admin & \
	make socialvoid & \
	wait;
install:
	make install_socialvoidlib install_socialvoid_service install_socialvoid_rpc install_socialvoid_admin install_socialvoid
install_fast:
	make install_fast_socialvoidlib install_fast_socialvoid_service install_fast_socialvoid_rpc install_fast_socialvoidadmin install_fast_socialvoid

start_service:
	ppm --main="$(socialvoidservice_name)" --version="latest" --runtime-version="$(runtime_version)"
start_rpc:
	ppm --main="$(socialvoidrpc_name)" --version="latest" --runtime-version="$(runtime_version)"
start:
	ppm --main="$(socialvoidadmin_name)" --version="latest" --runtime-version="${runtime_version}"