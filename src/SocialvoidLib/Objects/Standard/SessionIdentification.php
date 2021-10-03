<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\RegexPatterns;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Classes\Validate;
    use SocialvoidLib\Exceptions\Standard\Authentication\BadSessionChallengeAnswerException;
    use SocialvoidLib\Exceptions\Standard\Authentication\SessionNotFoundException;
    use SocialvoidLib\Exceptions\Standard\Validation\InvalidClientPublicHashException;
    use tsa\Classes\Crypto;
    use tsa\Exceptions\InvalidSecretException;

    /**
     * Class SessionIdentification
     * @package SocialvoidLib\Objects\Standard
     */
    class SessionIdentification
    {
        /**
         * The ID of the session
         *
         * @var string
         */
        public $SessionID;

        /**
         * The Public ID of the client's hash
         *
         * @var string
         */
        public $ClientPublicHash;

        /**
         * The answer of the challenge hash
         *
         * @var string
         */
        public $ChallengeAnswer;

        /**
         * Validates the session challenge answer
         *
         * @param string $client_private_hash
         * @param string $challenge
         * @param int $discrepancy
         * @param null $currentTimeSlice
         * @return bool
         * @throws BadSessionChallengeAnswerException
         */
        public function validateAnswer(string $client_private_hash, string $challenge, int $discrepancy=1, $currentTimeSlice=null): bool
        {
            if(gettype($client_private_hash) !== "string")
                throw new BadSessionChallengeAnswerException("The client private hash is invalid (-s1)");
            if(strlen($client_private_hash) !== 64)
                throw new BadSessionChallengeAnswerException("The client private hash is invalid (-s2)");
            if(Validate::hash($client_private_hash) == false)
                throw new BadSessionChallengeAnswerException("The client private hash is invalid (-s3)");
            if($this->ClientPublicHash == $client_private_hash)
                throw new BadSessionChallengeAnswerException("The client private hash is invalid (-s4)");

            if ($currentTimeSlice === null)
            {
                $currentTimeSlice = floor(time() / 30);
            }

            for ($i = -$discrepancy; $i <= $discrepancy; ++$i)
            {
                try
                {
                    $calculatedTotp = Crypto::getCode($challenge, $currentTimeSlice + $i);
                }
                catch (InvalidSecretException $e)
                {
                    throw new BadSessionChallengeAnswerException("The server cannot validate the session challenge", $e);
                }

                $calculatedAnswer = hash("sha1", $calculatedTotp . $client_private_hash);

                if(strlen($calculatedAnswer) == strlen($this->ChallengeAnswer))
                {
                    if($calculatedAnswer == $this->ChallengeAnswer)
                        return true;
                }
            }

            throw new BadSessionChallengeAnswerException("The challenge answer is incorrect");
        }

        /**
         * Validates the session identification object
         *
         * @return bool
         * @throws BadSessionChallengeAnswerException
         * @throws InvalidClientPublicHashException
         * @throws SessionNotFoundException
         */
        public function validate(): bool
        {
            if(gettype($this->SessionID) !== "string")
                throw new SessionNotFoundException("The requested session was not found in the network");
            if(strlen(Utilities::removeSlaveHash($this->SessionID)) !== 45)
                throw new SessionNotFoundException("The requested session was not found in the network");

            if(gettype($this->ClientPublicHash) !== "string")
                throw new InvalidClientPublicHashException("The client's public hash must be a string");
            if(strlen($this->ClientPublicHash) !== 64)
                throw new InvalidClientPublicHashException("The client's public hash must be 64 characters in length");
            if(Validate::hash($this->ClientPublicHash) == false)
                throw new InvalidClientPublicHashException("The client's public hash is not a valid hash");

            if(gettype($this->ChallengeAnswer) !== "string")
                throw new BadSessionChallengeAnswerException("The session challenge answer must be a string");
            if(strlen($this->ChallengeAnswer) !== 40)
                throw new BadSessionChallengeAnswerException("The session challenge answer is incorrect");

            return true;
        }


        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "session_id" => $this->SessionID,
                "client_public_hash" => $this->ClientPublicHash,
                "challenge_answer" => $this->ChallengeAnswer
            ];
        }

        /**
         * Constructs object from an array representation
         *
         * @param array $data
         * @return SessionIdentification
         */
        public static function fromArray(array $data): SessionIdentification
        {
            $sessionIdentificationObject = new SessionIdentification();

            if(isset($data["session_id"]))
                $sessionIdentificationObject->SessionID = $data["session_id"];

            if(isset($data["client_public_hash"]))
                $sessionIdentificationObject->ClientPublicHash = $data["client_public_hash"];

            if(isset($data["challenge_answer"]))
                $sessionIdentificationObject->ChallengeAnswer = $data["challenge_answer"];

            return $sessionIdentificationObject;
        }
    }