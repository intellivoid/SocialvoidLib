<?php
    /*
     * Copyright (c) 2017-2021. Intellivoid Technologies
     *
     * All rights reserved, SocialvoidLib was written by Zi Xing Narrakas <netkas@intellivoid.net> licensed by
     * Intellivoid Technologies, no part of this source code is open source. SocialvoidLib is a closed-source
     * solution for the Socialvoid Community Standard, if you wish to redistribute this source code you
     * must have a written permission from Intellivoid Technologies to do so.
     */

    /** @noinspection PhpMissingFieldTypeInspection */
    /** @noinspection PhpUnused */

namespace SocialvoidLib\Objects\ActiveSession;

    use SocialvoidLib\Abstracts\Flags\PermissionSets;

    /**
     * Class SessionData
     * @package SocialvoidLib\Objects\ActiveSession
     */
    class SessionData
    {
        /**
         * Permissions set to the current session
         *
         * @var array|string[]|PermissionSets[]
         */
        public $PermissionSets;

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'permission_sets' => $this->PermissionSets
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionData
         */
        public static function fromArray(array $data): SessionData
        {
            $SessionDataObject = new SessionData();

            if(isset($data['permission_sets']))
                $SessionDataObject->PermissionSets = $data['permission_sets'];

            return $SessionDataObject;
        }
    }