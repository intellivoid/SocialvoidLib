<?php

    namespace SocialvoidLib\Network;

    use SocialvoidLib\Abstracts\StatusStates\CaptchaState;
    use SocialvoidLib\Abstracts\Types\Standard\CaptchaType;
    use SocialvoidLib\Classes\Captcha\PhraseBuilder;
    use SocialvoidLib\Classes\CaptchaBuilder;
    use SocialvoidLib\Exceptions\GenericInternal\DatabaseException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaAlreadyAnsweredException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaAlreadyUsedException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaAnswerNotApplicableException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaBlockedException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaExpiredException;
    use SocialvoidLib\Exceptions\Standard\Security\CaptchaNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Security\IncompleteCaptchaException;
    use SocialvoidLib\Exceptions\Standard\Security\IncorrectCaptchaAnswerException;
    use SocialvoidLib\NetworkSession;

    class Captcha
    {
        /**
         * @var NetworkSession
         */
        private NetworkSession $networkSession;

        /**
         * Timeline constructor.
         * @param NetworkSession $networkSession
         */
        public function __construct(NetworkSession $networkSession)
        {
            $this->networkSession = $networkSession;
        }

        /**
         * Creates a new Captcha
         *
         * @param string $type
         * @param string $ip_address
         * @param int $ttl
         * @return \SocialvoidLib\Objects\Standard\Captcha
         * @throws DatabaseException
         * @throws CaptchaNotFoundException
         */
        public function createCaptcha(string $ip_address, int $ttl, string $type=CaptchaType::ImageTextScrambleChallenge): \SocialvoidLib\Objects\Standard\Captcha
        {
            switch($type)
            {
                case CaptchaType::TextMathChallenge:
                case CaptchaType::ImageTextScrambleMathChallenge:
                    $value = mt_rand(1, 9) . '+' . mt_rand(1, 9);
                    $answer = ((int)explode('+', $value)[0] + (int)explode('+', $value)[1]);
                    break;

                case CaptchaType::TextQuestionChallenge:
                    $value = 'What is the color orange?';
                    $answer = 'orange';
                    break;

                case CaptchaType::ImageTextScrambleChallenge:
                    $phraseBuilder = new PhraseBuilder();
                    $value = PhraseBuilder::doNiceize($phraseBuilder->build());
                    $answer = $value;
                    break;

                default:
                    $value = 'What is the color green?';
                    $answer = 'orange';
                    break;
            }

            $captcha_id = $this->networkSession->getSocialvoidLib()->getCaptchaManager()->createCaptcha($type, $value, $answer, $ttl, $ip_address, false);
            return $this->getCaptcha($captcha_id);
        }

        /**
         * Gets the captcha object with its value
         *
         * @param string $captcha_id
         * @return \SocialvoidLib\Objects\Standard\Captcha
         * @throws DatabaseException
         * @throws CaptchaNotFoundException
         */
        public function getCaptcha(string $captcha_id): \SocialvoidLib\Objects\Standard\Captcha
        {
            $internal_captcha_object = $this->networkSession->getSocialvoidLib()->getCaptchaManager()->getCaptcha($captcha_id);
            $captcha_object = \SocialvoidLib\Objects\Standard\Captcha::fromCaptchaInternal($internal_captcha_object);

            switch($captcha_object->Type)
            {
                case CaptchaType::ImageTextScrambleChallenge:
                case CaptchaType::ImageTextScrambleMathChallenge:
                    $captcha_builder = new CaptchaBuilder();
                    $captcha_builder->setPhrase($captcha_object->Value);
                    $captcha_builder->build(250, 100);
                    $captcha_object->Value = $captcha_builder->inline();
                    break;
            }

            return $captcha_object;
        }

        /**
         * Answers an existing captcha instance
         *
         * @param string $captcha_id
         * @param string $answer
         * @return bool
         * @throws CaptchaAlreadyAnsweredException
         * @throws CaptchaAnswerNotApplicableException
         * @throws CaptchaBlockedException
         * @throws CaptchaExpiredException
         * @throws CaptchaNotFoundException
         * @throws DatabaseException
         * @throws IncorrectCaptchaAnswerException
         */
        public function answerCaptcha(string $captcha_id, string $answer): bool
        {
            $internal_captcha_object = $this->networkSession->getSocialvoidLib()->getCaptchaManager()->getCaptcha($captcha_id);
            $internal_captcha_object->sync();

            switch($internal_captcha_object->State)
            {
                case CaptchaState::Expired:
                    throw new CaptchaExpiredException();

                case CaptchaState::Success:
                    throw new CaptchaAlreadyAnsweredException();

                case CaptchaState::Blocked:
                    throw new CaptchaBlockedException();

                case CaptchaState::AwaitingAction:
                    throw new CaptchaAnswerNotApplicableException();
            }

            if(strtolower(trim($answer)) !== strtolower(trim($internal_captcha_object->Answer)))
            {
                $internal_captcha_object->State = CaptchaState::Blocked;
                $this->networkSession->getSocialvoidLib()->getCaptchaManager()->updateCaptcha($internal_captcha_object);
                throw new IncorrectCaptchaAnswerException();
            }

            $internal_captcha_object->State = CaptchaState::Success;
            $this->networkSession->getSocialvoidLib()->getCaptchaManager()->updateCaptcha($internal_captcha_object);

            return true;
        }

        /**
         * Uses the given captcha and sets it as used
         *
         * @param string $captcha_id
         * @return bool
         * @throws CaptchaAlreadyUsedException
         * @throws CaptchaBlockedException
         * @throws CaptchaExpiredException
         * @throws CaptchaNotFoundException
         * @throws DatabaseException
         * @throws IncompleteCaptchaException
         */
        public function useCaptcha(string $captcha_id): bool
        {
            $internal_captcha_object = $this->networkSession->getSocialvoidLib()->getCaptchaManager()->getCaptcha($captcha_id);
            $internal_captcha_object->sync();

            switch($internal_captcha_object->State)
            {
                case CaptchaState::Expired:
                    throw new CaptchaExpiredException();

                case CaptchaState::Blocked:
                    throw new CaptchaBlockedException();

                case CaptchaState::AwaitingAction:
                case CaptchaState::AwaitingAnswer:
                    throw new IncompleteCaptchaException();

                case CaptchaState::Used:
                    throw new CaptchaAlreadyUsedException();
            }

            $internal_captcha_object->State = CaptchaState::Used;
            $this->networkSession->getSocialvoidLib()->getCaptchaManager()->updateCaptcha($internal_captcha_object);
            return true;
        }

    }