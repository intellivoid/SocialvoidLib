<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $cursor = new \SocialvoidLib\Objects\Cursor(25, 100);
    var_dump("LIMIT " . $cursor->ContentLimit . ", OFFSET " . $cursor->getOffset());
