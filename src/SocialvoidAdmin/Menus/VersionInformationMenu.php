<?php

    namespace SocialvoidAdmin\Menus;

    use PhpSchool\CliMenu\Builder\CliMenuBuilder;
    use PhpSchool\CliMenu\CliMenu;
    use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
    use ppm\ppm;

    class VersionInformationMenu implements \SocialvoidAdmin\Interfaces\MenuInterface
    {
        /**
         * @var CliMenu
         */
        private CliMenu $menu;

        public function __construct()
        {
            $imported_packages = ppm::getImportedPackages();
            $results = (string)null;

            foreach($imported_packages as $package_name => $version)
                $results .=  "$package_name==$version" . PHP_EOL;

            /** @noinspection DuplicatedCode */
            $this->menu = ($builder = new CliMenuBuilder)
                ->setWidth($builder->getTerminal()->getWidth())
                ->setBackgroundColour('magenta')
                ->setTitle('Socialvoid Component Versions')
                ->addAsciiArt($results, AsciiArtItem::POSITION_LEFT)
                ->addLineBreak(' ', 2)
                ->addItem('Go Back', function (CliMenu $menu) {
                    $main_menu = new MainMenu();
                    $main_menu->open();
                })
                ->disableDefaultItems()
                ->build();
        }

        /**
         * @inheritDoc
         */
        public function open(): void
        {
            $this->menu->open();
        }
    }