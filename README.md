![SocialvoidLib](assets/banner.jpg)

[![PPM Compile Socialvoid](https://github.com/intellivoid/SocialvoidLib/actions/workflows/net.intellivoid.socialvoid.ppm.yml/badge.svg)](https://github.com/intellivoid/SocialvoidLib/actions/workflows/net.intellivoid.socialvoid.ppm.yml)
[![PPM Release Socialvoid](https://github.com/intellivoid/SocialvoidLib/actions/workflows/net.intellivoid.socialvoid.ppm_release.yml/badge.svg)](https://github.com/intellivoid/SocialvoidLib/actions/workflows/net.intellivoid.socialvoid.ppm_release.yml)


###  Building and installing
```shell
make clean update build; sudo make install
```

### Importing it
```php
<?php
    require("ppm");
    ppm_import("net.intellivoid.socialvoid");
```