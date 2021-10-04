<?php

    require("ppm");
    ppm_import("net.intellivoid.socialvoidlib");


    $text = "<b>Foo</b><i>bar</i> <b>Jo<i>hn</i></b> @admin #netkas #cool #new #amazing https://google.com <a href=\"https://google.com/\">Click Here</a>";

    var_dump(\SocialvoidLib\Classes\Utilities::extractEntities($text, [
        \SocialvoidLib\Abstracts\Options\ParseOptions::Markdown,
        \SocialvoidLib\Abstracts\Options\ParseOptions::HTML,
        \SocialvoidLib\Abstracts\Options\ParseOptions::URLs,
        \SocialvoidLib\Abstracts\Options\ParseOptions::Mentions,
        \SocialvoidLib\Abstracts\Options\ParseOptions::Hashtags
    ]));
