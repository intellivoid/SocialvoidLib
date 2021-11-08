<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    namespace SocialvoidLib\Abstracts\Flags;

    /**
     * Class PostFlags
     * @package SocialvoidLib\Abstracts\Flags
     */
    abstract class PostFlags
    {
        /**
         * This indicates if the post was deleted
         */
        const Deleted = 'DELETED';

        /**
         * Indicates if the post is currently liked by the user
         */
        const Liked = 'LIKED';

        /**
         * Indicates if the post is currently reposted by the user
         */
        const Reposted = 'REPOSTED';

        /**
         * Indicates if this post is an advertisement
         */
        const Advertisement = 'ADVERTISEMENT';

        /**
         * Indicates if this post is sponsored
         */
        const Sponsored = 'SPONSORED';

        /**
         * Indicates if the post was infringing on copyright and was claimed by a DMCA report
         */
        const DmcaViolation = 'DMCA_VIOLATION';

        /**
         * Indicates if the post was infringing on the server's terms of service or rules
         */
        const TermsViolation = 'TERMS_VIOLATION';

        /**
         * Indicates that the post contains content that is considered not safe for work
         */
        const NsfwContent = 'NSFW';

        /**
         * Indicates that the post contains content is considered not safe for life
         */
        const NsflContent = 'NSFL';
    }