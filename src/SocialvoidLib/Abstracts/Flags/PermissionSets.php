<?php

    namespace SocialvoidLib\Abstracts\Flags;

    abstract class PermissionSets
    {
        /**
         * Guest permissions allows the execution of methods without requiring authentication. Read-only mode
         */
        const Guest = 'GUEST';

        /**
         * User permissions allow the execution of user methods and interactivity with the network and other peers
         */
        const User = 'USER';

        /**
         * Proxy permissions are much like user permissions but are restricted to proxy related methods, the server does
         * not treat proxy peers the same as users. Proxy peers should not be allowed to interact with other peers on
         * its own and simply serves the purpose of providing content to the network (much like a bridge for Twitter,
         * Telegram or other service)
         */
        const Proxy = 'PROXY';

        /**
         * Bot permissions are much like user permissions but are restricted to bot related methods, the server does not
         * treat bot peers the same as users. Bot peers should not be allowed to interact with other peers on the network
         * unless it's being invoked by a peer, bots can inherit proxy permissions but with additional access to methods
         * and bot exclusive methods for the purpose of providing some sort of automated interactivity on the network
         */
        const Bot = 'BOT';

        /**
         * An administrator bot inherits bot, moderator and administrator permissions which can have unrestricted
         * access to other methods or means of listening to events that would be useful for the network, such as a bot
         * that posts updates at an interval about server events or acts accordingly to events going on in the server.
         * These are usually back-end related bots.
         */
        const AdministratorBot = 'ADMINISTRATOR_BOT';

        /**
         * Moderator permissions grants the same permissions as user but access to methods that permits the peer to
         * preform moderation tasks on the server such as removing posts, warning peers and so on.
         */
        const Moderator = 'MODERATOR';

        /**
         * Administrator permissions inherits user & moderator permissions but has the ability to grant manage peers,
         * moderators, change network settings and so on.
         */
        const Administrator = 'ADMINISTRATOR';

        /**
         * The highest permission available, inherits user, moderator and administrator permissions but also grants
         * special access to methods that could otherwise be dangerous. Should only be granted to one peer that is
         * only used by the server and not by a human.
         */
        const ServerAdministrator = 'SERVER_ADMINISTRATOR';
    }