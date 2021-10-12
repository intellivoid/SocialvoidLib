<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $cursor = new \SocialvoidLib\Objects\Cursor(100, 3);
    var_dump("LIMIT " . $cursor->ContentLimit . ", OFFSET " . $cursor->getOffset());
