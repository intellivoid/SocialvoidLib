clean:
	rm -rf build

build:
	mkdir build
	ppm --no-intro --compile="src/SocialvoidLib" --directory="build"

build_fast:
	mkdir build
	ppm --skip-dependencies --no-intro --compile="src/SocialvoidLib" --directory="build"

install:
	ppm --no-prompt --fix-conflict --install="build/net.intellivoid.socialvoidlib.ppm" --branch="production"