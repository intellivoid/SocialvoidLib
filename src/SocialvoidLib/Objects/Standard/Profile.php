<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Interfaces\StandardObjectInterface;
    use SocialvoidLib\Objects\Standard\ObjectDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;
    use SocialvoidLib\Objects\Standard\TypeDefinition;
    use SocialvoidLib\Objects\Peer;

    class Profile implements StandardObjectInterface
    {
        /**
         * The user's first name
         *
         * @var string
         */
        public $FirstName;

        /**
         * The user's last name
         *
         * @var string|null
         */
        public $LastName;

        /**
         * The user's display name (First name & Last name combined)
         *
         * @var string
         */
        public $Name;

        /**
         * The biography of the user
         *
         * @var string|null
         */
        public $Biography;

        /**
         * The location of the user
         *
         * @var string|null
         */
        public $Location;

        /**
         * The URL of the user
         *
         * @var string|null
         */
        public $URL;

        /**
         * The amount of followers that the user has
         *
         * @var int
         */
        public $FollowersCount = 0;

        /**
         * The amount of users that this user is following
         *
         * @var int
         */
        public $FollowingCount = 0;

        /**
         * An array of display picture sizes for this user
         *
         * @var DisplayPictureSize[]
         */
        public $DisplayPictureSizes;

        public function __construct()
        {
            $this->DisplayPictureSizes = [];
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function toArray(): array
        {
            $displayPicturesSizes = [];
            foreach($this->DisplayPictureSizes as $datum)
                $displayPicturesSizes[] = $datum->toArray();

            return [
                'first_name' => $this->FirstName,
                'last_name' => $this->LastName,
                'name' => $this->Name,
                'biography' => $this->Biography,
                'location' => $this->Location,
                'url' => $this->URL,
                'followers_count' => (int)$this->FollowersCount,
                'following_count' => (int)$this->FollowingCount,
                'display_picture_sizes' =>  $displayPicturesSizes
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return Profile
         */
        public static function fromArray(array $data): Profile
        {
            $ProfileObject = new Profile();

            if(isset($data['first_name']))
                $ProfileObject->FirstName = $data['first_name'];

            if(isset($data['last_name']))
                $ProfileObject->LastName = $data['last_name'];

            if(isset($data['name']))
                $ProfileObject->Name = $data['name'];

            if(isset($data['biography']))
                $ProfileObject->Biography = $data['biography'];

            if(isset($data['location']))
                $ProfileObject->Location = $data['location'];

            if(isset($data['url']))
                $ProfileObject->URL = $data['url'];

            if(isset($data['followers_count']))
                $ProfileObject->FollowersCount = (int)$data['followers_count'];

            if(isset($data['following_count']))
                $ProfileObject->FollowingCount = (int)$data['following_count'];

            if(isset($data['display_picture_sizes']))
            {
                foreach($data['display_picture_sizes'] as $display_picture_size)
                    $ProfileObject->DisplayPictureSizes = DisplayPictureSize::fromArray($display_picture_size);
            }

            return $ProfileObject;
        }

        /**
         * Constructs a profile object from a user object
         *
         * @param Peer $user
         * @return Profile
         */
        public static function fromUser(Peer $user): Profile
        {
            $ProfileObject = new Profile();

            $ProfileObject->FirstName = $user->Profile->FirstName;
            $ProfileObject->LastName = $user->Profile->LastName;

            if($user->Profile->LastName == null)
            {
                $ProfileObject->Name = $user->Profile->FirstName;
            }
            else
            {
                $ProfileObject->Name = $user->Profile->FirstName . ' ' . $user->Profile->LastName;
            }

            $ProfileObject->Biography = $user->Profile->Biography;
            $ProfileObject->Location = $user->Profile->Location;
            $ProfileObject->URL = $user->Profile->URL;

            foreach($user->DisplayPictureDocument->Files as $item)
            {
                $display_size = new DisplayPictureSize();
                $display_size->Document = Document::fromDocument($user->DisplayPictureDocument, $item->Hash);
                $split_size = explode('x', strtolower($item->ID));
                $display_size->Width = (int)$split_size[0];
                $display_size->Height = (int)$split_size[1];
                $ProfileObject->DisplayPictureSizes[] = $display_size;
            }

            return $ProfileObject;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'Profile';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'The profile object provides a profile display for a peer entity, this is mainly used to represent a "Profile" display of a peer.';
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ObjectDefinition
        {
            return new ObjectDefinition(self::getName(), self::getDescription(), self::getParameters());
        }

        /**
         * @inheritDoc
         */
        public static function getParameters(): array
        {
            return [
                new ParameterDefinition('first_name', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The first name of the entity'),

                new ParameterDefinition('last_name', [
                    new TypeDefinition(BuiltinTypes::String, false),
                    new TypeDefinition(BuiltinTypes::Null, false)
                ], true, 'The last name of the entity'),

                new ParameterDefinition('name', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The full display name of the entity'),

                new ParameterDefinition('biography', [
                    new TypeDefinition(BuiltinTypes::String, false),
                    new TypeDefinition(BuiltinTypes::Null, false)
                ], true, 'A biography or description of the entity'),

                new ParameterDefinition('location', [
                    new TypeDefinition(BuiltinTypes::String, false),
                    new TypeDefinition(BuiltinTypes::Null, false)
                ], true, 'The location of the entity'),

                new ParameterDefinition('url', [
                    new TypeDefinition(BuiltinTypes::String, false),
                    new TypeDefinition(BuiltinTypes::Null, false)
                ], true, 'The URL of the entity (Can be a website or a blog, etc)'),

                new ParameterDefinition('followers_count', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The amount of followers that this entity has'),

                new ParameterDefinition('following_count', [
                    new TypeDefinition(BuiltinTypes::Integer, false)
                ], true, 'The amount of peers that this entity is following'),

                new ParameterDefinition('display_picture_sizes', [
                    new TypeDefinition('DisplayPictureSize', true)
                ], true, 'An array of display picture size objects that represents the entity\'s display picture'),

            ];
        }
    }