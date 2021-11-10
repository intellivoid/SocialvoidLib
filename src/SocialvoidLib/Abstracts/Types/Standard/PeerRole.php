<?php

    namespace SocialvoidLib\Abstracts\Types\Standard;

    abstract class PeerRole
    {
        /**
         * Indicates that the peer has no special permissions and can only
         * act upon authentication dependencies and peer type
         */
        const Peer = 'PEER';

        /**
         * Indicates that the peer has access to moderator methods
         */
        const Moderator = 'MODERATOR';

        /**
         * Indicates that the peer has access to administrator methods (dangerous)
         */
        const Administrator = 'ADMINISTRATOR';
    }