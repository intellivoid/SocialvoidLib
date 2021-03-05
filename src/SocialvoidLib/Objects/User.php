<?php /** @noinspection PhpMissingFieldTypeInspection */

/** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\Flags\UserFlags;
    use SocialvoidLib\Abstracts\StatusStates\UserStatus;
    use SocialvoidLib\Abstracts\UserAuthenticationMethod;
    use SocialvoidLib\Classes\Security\Hashing;
    use SocialvoidLib\Objects\User\CoaUserEntity;
    use SocialvoidLib\Objects\User\UserAuthenticationProperties;
    use SocialvoidLib\Objects\User\UserProperties;

    /**
     * Class User
     * @package SocialvoidLib\Objects
     */
    class User
    {
        /**
         * The Unique Internal Database ID for this user
         *
         * @var int
         */
        public $ID;

        /**
         * The Public ID for this user
         *
         * @var string
         */
        public $PublicID;

        /**
         * The username of the user
         *
         * @var string
         */
        public $Username;

        /**
         * The name of the network that this user is from
         *
         * @var string
         */
        public $NetworkName;

        /**
         * The current status of the user which indicates what
         * activities they can preform on the network
         *
         * @var UserStatus
         */
        public $Status;

        /**
         * The Unix Timestamp for when this user's status is
         * changed back to "Active"
         *
         * @var int
         */
        public $StatusChangeTimestamp;

        /**
         * Serializable set of properties attached to this user
         *
         * @var UserProperties
         */
        public $Properties;

        /**
         * An array of flags associated with this user
         *
         * @var UserFlags[]
         */
        public $Flags;

        /**
         * The authentication method used to authenticate to this
         * account using the designated method that is supported
         *
         * @var UserAuthenticationMethod
         */
        public $AuthenticationMethod;

        /**
         * The authentication properties used by the user
         *
         * @var UserAuthenticationProperties
         */
        public $AuthenticationProperties;

        /**
         * The COA User Entity data collected from the COA provider
         *
         * @var CoaUserEntity
         */
        public $CoaUserEntity;
    }