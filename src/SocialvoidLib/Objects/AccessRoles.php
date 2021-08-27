<?php

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

    namespace SocialvoidLib\Objects;

    use SocialvoidLib\Abstracts\Types\Security\AccessRole;
    use SocialvoidLib\Exceptions\Internal\EntityWithoutAccessException;

    /**
     * Class AccessRoles
     * @package SocialvoidLib\Objects\Document
     */
    class AccessRoles
    {
        /**
         * The Access Roles data managed by the object
         *
         * @var array`
         */
        public $Data;

        /**
         * Adds a new access role
         *
         * @param string $entity_type
         * @param int|string $entity_identifier
         * @param string|AccessRole $access_role
         */
        public function addAccess(string $entity_type, $entity_identifier, string $access_role): void
        {
            $this->Data[$entity_type . ":" . $entity_identifier] = $access_role;
        }

        /**
         * Revokes an access role
         *
         * @param string $entity_type
         * @param $entity_identifier
         */
        public function revokeAccess(string $entity_type, $entity_identifier): void
        {
            if($this->hasAccess($entity_type, $entity_identifier))
                unset($this->Data[$entity_type . ":" . $entity_identifier]);
        }

        /**
         * Determines if the access role exists
         *
         * @param string $entity_type
         * @param $entity_identifier
         * @return bool
         */
        public function hasAccess(string $entity_type, $entity_identifier): bool
        {
            return isset($this->Data[$entity_type . ":" . $entity_identifier]);
        }

        /**
         * Determines if the access role exists
         *
         * @param string $entity_type
         * @param $entity_identifier
         * @return string
         * @throws EntityWithoutAccessException
         */
        public function getAccessType(string $entity_type, $entity_identifier): string
        {
            if($this->hasAccess($entity_type, $entity_identifier) == false)
                throw new EntityWithoutAccessException(
                    $entity_type, $entity_identifier, $this->Data,
                    "The specified entity identifier does not have an access role");

            return $this->Data[$entity_type . ":" . $entity_identifier];
        }

        /**
         * Returns an array representation of the object structure
         *
         * @return array
         */
        public function toArray(): array
        {
            if($this->Data == null)
                return [];
            return $this->Data;
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return AccessRoles
         */
        public static function fromArray(array $data): AccessRoles
        {
            $AccessRoleObject = new AccessRoles();
            $AccessRoleObject->Data = $data;

            if($AccessRoleObject->Data == [])
                $AccessRoleObject->Data = [];

            return $AccessRoleObject;
        }

    }