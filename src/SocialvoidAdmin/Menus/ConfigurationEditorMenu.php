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

    class DebuggingTools implements MenuInterface
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
                ->setTitle('Socialvoid Debugging Tools')
                ->addItem('Truncate Servers', function (CliMenu $menu) {
                    $result = $menu->askText()
                        ->setPromptText('Enter "CONFIRM" to truncate all servers')
                        ->ask();

                    if($result->fetch() == "CONFIRM")
                    {
                        $menu->close();
                        print('Truncating master MySQL database...' . PHP_EOL);

                        $SocialvoidLib = SocialvoidAdmin::getSocialvoidLib();
                        $Queries = [
                            "SET FOREIGN_KEY_CHECKS = 0;",
                            "TRUNCATE TABLE follower_data;",
                            "TRUNCATE TABLE follower_states;",
                            "TRUNCATE TABLE telegram_cdn;",
                            "TRUNCATE TABLE user_timelines;",
                            "TRUNCATE TABLE users;",
                            "SET FOREIGN_KEY_CHECKS = 1;"
                        ];

                        foreach($Queries as $query)
                        {
                            print('  > '. $query . PHP_EOL);
                            $QueryResults = $SocialvoidLib->getDatabase()->query($query);
                            if($QueryResults == false)
                            {
                                print($SocialvoidLib->getDatabase()->error . PHP_EOL);
                                sleep(5);
                                $menu->open();
                                return;
                            }
                        }

                        foreach($SocialvoidLib->getSlaveManager()->getMySqlConnections() as $hash_connection => $connection)
                        {
                            print('Truncating slave MySQL database ' . $hash_connection . '...' . PHP_EOL);
                            $Queries = [
                                "SET FOREIGN_KEY_CHECKS = 0;",
                                "TRUNCATE TABLE documents;",
                                "TRUNCATE TABLE posts;",
                                "TRUNCATE TABLE posts_likes;",
                                "TRUNCATE TABLE posts_quotes;",
                                "TRUNCATE TABLE posts_replies;",
                                "TRUNCATE TABLE posts_reposts;",
                                "TRUNCATE TABLE sessions;",
                                "SET FOREIGN_KEY_CHECKS = 1;"
                            ];

                            foreach($Queries as $query)
                            {
                                print('  > '. $query . PHP_EOL);
                                $QueryResults = $connection->getConnection()->query($query);
                                if($QueryResults == false)
                                {
                                    print($connection->getConnection()->error . PHP_EOL);
                                    sleep(5);
                                    $menu->open();
                                    return;
                                }
                            }
                        }

                        print('Truncating master Redis server...' . PHP_EOL);
                        $SocialvoidLib->getBasicRedis()->flushAll();

                        print("Done! Returning in 5 seconds" . PHP_EOL);
                        sleep(5);
                        $menu->open();
                    }
                    else
                    {
                        $menu->confirm('Aborted operation')->display();
                    }
                })
                ->addItem('Test Servers', function (CliMenu $menu) {
                    $menu->close();
                    print('Testing master MySQL database...');

                    $SocialvoidLib = SocialvoidAdmin::getSocialvoidLib();
                    $SocialvoidLib->getDatabase();
                    if($SocialvoidLib->getDatabase()->get_connection_stats() == false)
                    {
                        print("FAILED, " . $SocialvoidLib->getDatabase()->connect_error . PHP_EOL);
                    }
                    else
                    {
                        print("PASSED" . PHP_EOL);
                    }

                    foreach($SocialvoidLib->getSlaveManager()->getMySqlConnections() as $hash_connection => $connection)
                    {
                        print('Testing slave MySQL database ' . $hash_connection . '...');
                        if($connection->getConnection()->get_connection_stats() == false)
                        {
                            print("FAILED, " . $connection->getConnection()->connect_error . PHP_EOL);
                        }
                        else
                        {
                            print("PASSED" . PHP_EOL);
                        }
                    }

                    print("Done! Returning in 5 seconds" . PHP_EOL);
                    sleep(5);
                    $menu->open();
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