<?php


    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");

    $text = "@test Wow! @netkas @ceo_of_retards this is pretty cool huh? visit https://socialvoid.cc/ wow! #cool #new #amazing101";

    $extractor = new \SocialvoidLib\Classes\PostText\Extractor($text);
    var_dump($extractor->extractHashtags());
    var_dump($extractor->extractMentionedUsernames());
    var_dump($extractor->extractURLs());