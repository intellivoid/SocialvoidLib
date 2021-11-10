<?php

    namespace SocialvoidLib\Abstracts\Flags;

    abstract class PermissionSets
    {
        /**
         * Indicates that the current session currently contains "public" permissions which allows
         * the execution of methods without requiring authentication
         */
        const Public = 'PUBLIC';

        /**
         * Indicates that the current session allows the client to modify the account settings of
         * the current authenticated entity
         */
        const ModifyAccountSettings = 'MODIFY_ACCOUNT_SETTINGS';

        /**
         * Indicates that the current session allows the client to modify the security settings of
         * the current authenticated entity
         */
        const ModifySecuritySettings = 'MODIFY_SECURITY_SETTINGS';

        /**
         * Indicates that the current session allows both "public" permissions and user permissions
         * that allows the execution of user methods
         */
        const User = 'USER';

        /**
         * Indicates that the current session has limited actions on the network and can only preform
         * actions of a proxy account
         */
        const Proxy = 'PROXY';

        /**
         * Indicates that the current session has limited actions and or special access to special functions
         * that can only be preformed by a bot
         */
        const Bot = 'BOT';

        /**
         * Indicates that the current session allows the authenticated entity to preform moderation
         * actions on the network
         */
        const Moderator = 'MODERATOR';

        /**
         * Indicates that the current session allows the authenticated entity to preform administrator
         * actions on the network (Dangerous)
         */
        const Administrator = 'ADMINISTRATOR';
    }