<?php

    namespace SocialvoidAdmin;

    use PhpSchool\CliMenu\Exception\InvalidTerminalException;
    use SocialvoidAdmin\Menus\MainMenu;
    use SocialvoidLib\SocialvoidLib;

    class SocialvoidAdmin
    {
        /**
         * @var MainMenu
         */
        private MainMenu $MainMenu;

        /**
         * @var SocialvoidLib
         */
        private static $SocialvoidLib;

        public function __construct()
        {
            $this->MainMenu = new MainMenu();
            self::$SocialvoidLib = new SocialvoidLib();
        }

        /**
         * @return SocialvoidLib
         */
        public static function getSocialvoidLib(): SocialvoidLib
        {
            return self::$SocialvoidLib;
        }

        /**
         * Begins the application by opening the main menu
         *
         * @throws InvalidTerminalException
         */
        public function startApplication()
        {
            $this->MainMenu->open();
        }
    }