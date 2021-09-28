<?php

    namespace SocialvoidAdmin\Menus;

    use PhpSchool\CliMenu\Builder\CliMenuBuilder;
    use PhpSchool\CliMenu\CliMenu;
    use PhpSchool\CliMenu\Exception\InvalidTerminalException;
    use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
    use SocialvoidAdmin\Interfaces\MenuInterface;

    class MainMenu implements MenuInterface
    {
        /**
         * @var CliMenu
         */
        private $menu;

        public function __construct()
        {
            $banner_art_path = __DIR__ . DIRECTORY_SEPARATOR . 'banner.txt';
            $this->menu = ($builder = new CliMenuBuilder)
                ->setWidth($builder->getTerminal()->getWidth())
                ->setBackgroundColour('magenta')
                ->addAsciiArt(file_get_contents($banner_art_path), AsciiArtItem::POSITION_CENTER)
                ->addAsciiArt('Copyright (C) 2017-' . date('Y') . ' Intellivoid Technologies, All Rights Reserved', AsciiArtItem::POSITION_CENTER)
                ->addLineBreak(' ', 2)
                ->addItem('Version Information', function (CliMenu $menu) {
                    $version_information_menu = new VersionInformationMenu();
                    $version_information_menu->open();
                })
                ->addItem('Health Status', function (CliMenu $menu) {
                    $health_status_menu = new HealthStatusMenu();
                    $health_status_menu->open();
                })
                ->build();
        }

        /**
         * @throws InvalidTerminalException
         */
        public function open(): void
        {
            $this->menu->open();
        }
    }