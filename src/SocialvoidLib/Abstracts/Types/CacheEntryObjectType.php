<?php


    namespace SocialvoidLib\Abstracts\Types;

    /**
     * Class CacheEntryObjectType
     * @package SocialvoidLib\Abstracts\Types
     */
    abstract class CacheEntryObjectType
    {
        const User = "user";

        const Post = "post";

        const Session = "session";

        const Document = "document";

        const TelegramCdnObject = "telegram_cdn_object";
    }