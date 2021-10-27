<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    print(json_encode(\SocialvoidLib\NetworkSession::getProtocolDefinitions()->toArray(), JSON_UNESCAPED_SLASHES));