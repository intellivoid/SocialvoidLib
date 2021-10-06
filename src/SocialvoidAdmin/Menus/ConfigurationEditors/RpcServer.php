<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidAdmin\Menus\ConfigurationEditors;

    use PhpSchool\CliMenu\Builder\CliMenuBuilder;
    use PhpSchool\CliMenu\CliMenu;
    use PhpSchool\CliMenu\Exception\InvalidTerminalException;
    use SocialvoidAdmin\Interfaces\MenuInterface;
    use SocialvoidAdmin\Menus\ConfigurationEditorMenu;
    use SocialvoidAdmin\Menus\MainMenu;
    use SocialvoidAdmin\SocialvoidAdmin;

    class RpcServer implements MenuInterface
    {
        /**
         * @var CliMenu
         */
        private $menu;

        public function __construct()
        {
            $RpcServerConfiguration = SocialvoidAdmin::getSocialvoidLib()->getRpcServerConfiguration();

            $builder = new CliMenuBuilder();
            $builder->setWidth($builder->getTerminal()->getWidth());
            $builder->setBackgroundColour('magenta');
            $builder->setTitle('RPC Server Configuration');

            $supported_values = ['string', 'integer', 'boolean', 'double'];

            foreach($RpcServerConfiguration as $config_name => $value)
            {
                if(in_array(gettype($value), $supported_values) == false)
                    continue;

                $builder->addItem($config_name . ': ' . $value, function (CliMenu $menu) use ($config_name, $value) {
                    $value_type = gettype($value);
                    $result = $menu->askText()
                        ->setPromptText('Enter new configuration value')
                        ->setPlaceholderText($value)
                        ->ask();

                    if($result->fetch() == $value || $result->fetch() == 'CANCEL')
                    {
                        $menu->confirm('No changes has been made.')->display();
                    }
                    else
                    {
                        switch($value_type)
                        {
                            case 'integer':
                                SocialvoidAdmin::getSocialvoidLib()->getAcm()->updateConfigurationValue(
                                    'RpcServer', $config_name, (int)$result->fetch()
                                );
                                break;

                            case 'boolean':
                                SocialvoidAdmin::getSocialvoidLib()->getAcm()->updateConfigurationValue(
                                    'RpcServer', $config_name, (boolean)$result->fetch()
                                );
                                break;

                            case 'double':
                                SocialvoidAdmin::getSocialvoidLib()->getAcm()->updateConfigurationValue(
                                    'RpcServer', $config_name, (double)$result->fetch()
                                );
                                break;

                            case 'string':
                            default:
                                /** @noinspection PhpCastIsUnnecessaryInspection */
                                SocialvoidAdmin::getSocialvoidLib()->getAcm()->updateConfigurationValue(
                                    'RpcServer', $config_name, (string)$result->fetch()
                                );
                        }

                        SocialvoidAdmin::getSocialvoidLib()->reloadConfiguration();
                        $menu->confirm('Changes applied successfully')->display();
                    }

                    $new_menu = new RpcServer();
                    $new_menu->open();
                });
            }

            $builder->addLineBreak();
            $builder->addItem('Go Back', function (CliMenu $menu) {
                $config_menu = new ConfigurationEditorMenu();
                $config_menu->open();
            });

            $builder->disableDefaultItems();
            $this->menu = $builder->build();
        }


        /**
         * @throws InvalidTerminalException
         */
        public function open(): void
        {
            $this->menu->open();
        }
}