<?php

    namespace SocialvoidLib\Managers;

    use msqg\QueryBuilder;
    use SocialvoidLib\Abstracts\StatusStates\CaptchaState;
    use SocialvoidLib\Abstracts\Types\Standard\CaptchaType;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\GenericInternal\InvalidSlaveHashException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaNotFoundException;
    use SocialvoidLib\Objects\Captcha;
    use SocialvoidLib\SocialvoidLib;
    use Symfony\Component\Uid\Uuid;
    use ZiProto\ZiProto;

    class CaptchaManager
    {
        private SocialvoidLib $socialvoidLib;

        /**
         * @param SocialvoidLib $socialvoidLib
         */
        public function __construct(SocialvoidLib $socialvoidLib)
        {
            $this->socialvoidLib = $socialvoidLib;
        }

        /**
         * Creates a new captcha record in the database
         *
         * @param string $type
         * @param string $value
         * @param string $answer
         * @param int $ttl
         * @param string|null $ip_address
         * @param bool $ip_tied
         * @return string
         * @throws DatabaseException
         * @noinspection PhpBooleanCanBeSimplifiedInspection
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        public function createCaptcha(string $type, string $value, string $answer, int $ttl, ?string $ip_address, bool $ip_tied=true): string
        {
            $id = Uuid::v1()->toRfc4122();

            switch($type)
            {
                case CaptchaType::ExternalWebChallenge:
                    $captcha_State = CaptchaState::AwaitingAction;
                    break;

                default:
                    $captcha_State = CaptchaState::AwaitingAnswer;
                    break;
            }

            $Query = QueryBuilder::insert_into('captcha', [
                'id' => $this->socialvoidLib->getDatabase()->real_escape_string($id),
                'type' => $this->socialvoidLib->getDatabase()->real_escape_string($type),
                'value' => $this->socialvoidLib->getDatabase()->real_escape_string($value),
                'answer' => $this->socialvoidLib->getDatabase()->real_escape_string($answer),
                'state' => $this->socialvoidLib->getDatabase()->real_escape_string($captcha_State),
                'used' => (int)false,
                'data' => $this->socialvoidLib->getDatabase()->real_escape_string(ZiProto::encode([])),
                'ip_address' => $this->socialvoidLib->getDatabase()->real_escape_string($ip_address),
                'ip_tied' => (bool)$ip_tied,
                'expiry_timestamp' => (int)(time() + $ttl),
                'created_timestamp' => time()
            ]);

            $SelectedServer = $this->socialvoidLib->getSlaveManager()->getRandomMySqlServer(true);
            $QueryResults = $SelectedServer->getConnection()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException("There was an error while trying to create a captcha",
                    $Query, $SelectedServer->getConnection()->error, $SelectedServer->getConnection()
                );
            }

            return $SelectedServer->MysqlServerPointer->HashPointer . '-' . $id;
        }

        /**
         * Retrieves an existing captcha from the database
         *
         * @param string $captcha_id
         * @return Captcha
         * @throws CaptchaNotFoundException
         * @throws DatabaseException
         */
        public function getCaptcha(string $captcha_id): Captcha
        {
            $Query = QueryBuilder::select('captcha', [
                'id',
                'type',
                'value',
                'answer',
                'state',
                'used',
                'data',
                'ip_address',
                'ip_tied',
                'expiry_timestamp',
                'created_timestamp'
            ], 'id', $this->socialvoidLib->getDatabase()->real_escape_string(Utilities::removeSlaveHash($captcha_id)));

            $slaveHash = Utilities::getSlaveHash($captcha_id);
            if($slaveHash == null)
                throw new CaptchaNotFoundException('The requested captcha was not found (-4)');
            try
            {
                $SelectedServer = $this->socialvoidLib->getSlaveManager()->getMySqlServer($slaveHash);
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new CaptchaNotFoundException('The requested captcha was not found (-5)');
            }

            $QueryResults = $SelectedServer->getConnection()->query($Query);

            if($QueryResults)
            {
                $Row = $QueryResults->fetch_array(MYSQLI_ASSOC);

                if ($Row == False)
                {
                    throw new CaptchaNotFoundException('The requested captcha was not found');
                }
                else
                {
                    $Row['data'] = ZiProto::decode($Row['data']);
                    $Captcha = Captcha::fromArray($Row);
                    $Captcha->ID = $slaveHash . '-' . $Captcha->ID;
                    return $Captcha;
                }
            }
            else
            {
                throw new DatabaseException(
                    $SelectedServer->getConnection()->error,
                    $Query, $SelectedServer->getConnection()->error, $SelectedServer->getConnection()
                );
            }
        }

        /**
         * Updates an existing captcha record in the database
         *
         * @param Captcha $captcha
         * @throws CaptchaNotFoundException
         * @throws DatabaseException
         */
        public function updateCaptcha(Captcha $captcha)
        {
            $Query = QueryBuilder::update('captcha', [
                'state' => $this->socialvoidLib->getDatabase()->real_escape_string($captcha->State),
                'used' => (int)$captcha->Used,
            ], 'id', $this->socialvoidLib->getDatabase()->real_escape_string(Utilities::removeSlaveHash($captcha->ID)));

            $slaveHash = Utilities::getSlaveHash($captcha->ID);
            if($slaveHash == null)
                throw new CaptchaNotFoundException('The requested captcha was not found (-4)');
            try
            {
                $SelectedServer = $this->socialvoidLib->getSlaveManager()->getMySqlServer($slaveHash);
            }
            catch (InvalidSlaveHashException $e)
            {
                throw new CaptchaNotFoundException('The requested captcha was not found (-5)');
            }

            $QueryResults = $SelectedServer->getConnection()->query($Query);

            if($QueryResults == false)
            {
                throw new DatabaseException(
                    'There was an error while trying to update the captcha',
                    $Query, $SelectedServer->getConnection()->error, $SelectedServer->getConnection()
                );
            }

        }
    }