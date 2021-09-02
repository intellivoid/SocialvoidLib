<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");


    $text = "**bold** *italic* `code` ~~strike~~ @admin #netkas #cool #new #amazing https://google.com [Click Here](https://google.com)";

    var_dump(\SocialvoidLib\Classes\Utilities::extractTextEntities($text, \SocialvoidLib\Abstracts\Modes\Standard\ParseMode::Markdown));
    var_dump(\SocialvoidLib\Classes\Utilities::extractTextWithoutEntities($text, \SocialvoidLib\Abstracts\Modes\Standard\ParseMode::Markdown));