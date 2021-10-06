<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidAdmin\Menus;

    use PhpSchool\CliMenu\Builder\CliMenuBuilder;
    use PhpSchool\CliMenu\CliMenu;
    use PhpSchool\CliMenu\Exception\InvalidTerminalException;
    use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
    use SocialvoidAdmin\Interfaces\MenuInterface;
    use SocialvoidAdmin\Menus\ConfigurationEditors\MasterMySqlDatabase;
    use SocialvoidAdmin\Menus\ConfigurationEditors\RpcServer;
    use SocialvoidAdmin\SocialvoidAdmin;
    use SocialvoidLib\Abstracts\StatusStates\HealthStatusCode;

    class ConfigurationEditorMenu implements MenuInterface
    {
        /**
         * @var CliMenu
         */
        private $menu;

        public function __construct()
        {
            $this->menu = ($builder = new CliMenuBuilder)
                ->setWidth($builder->getTerminal()->getWidth())
                ->setBackgroundColour('magenta')
                ->setTitle('Configuration Editor')
                ->addItem('Master MySQL Database', function (CliMenu $menu) {
                    $new_menu = new MasterMySqlDatabase();
                    $new_menu->open();
                })
                ->addItem('RPC Server', function (CliMenu $menu) {
                    $new_menu = new RpcServer();
                    $new_menu->open();
                })
                ->addItem('Go Back', function (CliMenu $menu) {
                    $main_menu = new MainMenu();
                    $main_menu->open();
                })
                ->disableDefaultItems()
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