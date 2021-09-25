<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidAdmin\Menus;

    use PhpSchool\CliMenu\Builder\CliMenuBuilder;
    use PhpSchool\CliMenu\CliMenu;
    use PhpSchool\CliMenu\Exception\InvalidTerminalException;
    use PhpSchool\CliMenu\MenuItem\AsciiArtItem;
    use SocialvoidAdmin\Interfaces\MenuInterface;
    use SocialvoidAdmin\SocialvoidAdmin;
    use SocialvoidLib\Abstracts\StatusStates\HealthStatusCode;

    class HealthStatusMenu implements MenuInterface
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
                ->setTitle('Socialvoid Health Status')
                ->addAsciiArt(
                    $this->getHealthStatus('main') . PHP_EOL .
                    $this->getHealthStatus('rpc_server') . PHP_EOL .
                    $this->getHealthStatus('service') . PHP_EOL,
                    AsciiArtItem::POSITION_LEFT)
                ->addLineBreak(' ', 2)
                ->addItem('Refresh', function (CliMenu $menu) {
                    $health_status_menu = new HealthStatusMenu();
                    $health_status_menu->open();
                })
                ->addItem('Go Back', function (CliMenu $menu) {
                    $main_menu = new MainMenu();
                    $main_menu->open();
                })
                ->disableDefaultItems()
                ->build();
        }

        /**
         * Returns the heath status
         *
         * @param string $module_name
         * @return string
         */
        private function getHealthStatus(string $module_name): string
        {
            $health_status = SocialvoidAdmin::getSocialvoidLib()->getHealthMonitoring()->getHealthStatus($module_name);
            switch($health_status)
            {
                case HealthStatusCode::Starting:
                    return '* [' . $module_name . '] Starting';

                case HealthStatusCode::Ok:
                    return '✓ [' . $module_name . '] OK';

                case HealthStatusCode::Failing:
                    return '! [' . $module_name . '] Running with errors, see logs';

                case HealthStatusCode::Fatal:
                    return '✗ [' . $module_name . '] Failure';

                case HealthStatusCode::Terminated:
                    return '✗ [' . $module_name . '] Terminated';

                default:
                    return '! [' . $module_name . '] Unknown';
            }
        }

        /**
         * @throws InvalidTerminalException
         */
        public function open(): void
        {
            $this->menu->open();
        }
    }