<?php


    namespace SocialvoidLib\Managers;


    use SocialvoidLib\SocialvoidLib;

    /**
     * Class DocumentsManager
     * @package SocialvoidLib\Managers
     */
    class DocumentsManager
    {
        /**
         * @var SocialvoidLib
         */
        private SocialvoidLib $socialvoidLib;

        /**
         * DocumentsManager constructor.
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }


    }