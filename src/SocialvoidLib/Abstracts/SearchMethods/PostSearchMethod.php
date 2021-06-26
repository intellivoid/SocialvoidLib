<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Abstracts\SearchMethods;

    /**
     * Class PostSearchMethod
     * @package SocialvoidLib\Abstracts\SearchMethods
     */
    abstract class PostSearchMethod
    {
        /**
         * @see PostSearchMethod::ByPublicId
         * @issue https://github.com/intellivoid/SocialvoidLib/issues/1
         * @deprecated Incremental
         */
        const ById = "id";

        const ByPublicId = "public_id";
    }