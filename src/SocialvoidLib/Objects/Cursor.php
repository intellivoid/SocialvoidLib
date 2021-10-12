<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects;

    class Cursor
    {
        /**
         *
         *
         * @var int
         */
        public $ContentLimit;

        /**
         *
         * @var int
         */
        public $Cursor;

        /**
         * Calculates the offset from the current cursor
         *
         * @return int
         */
        public function getOffset(): int
        {
            if($this->Cursor == 1)
                return 0;
            $offset = ($this->Cursor - 1) * $this->ContentLimit;
            if($offset >= 2147483647)
                return 2147483647;
            return $offset;
        }

        /**
         * @param int $content_limit
         * @param int $cursor
         */
        public function __construct(int $content_limit=100, int $cursor=1)
        {
            if($content_limit > 2147483647)
                $content_limit = 2147483647;
            if($cursor > 2147483647)
                $cursor = 2147483647;

            $this->ContentLimit = $content_limit;
            $this->Cursor = $cursor;
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                'content_limit' => $this->ContentLimit,
                'cursor' => $this->Cursor
            ];
        }

        /**
         * Constructs an object from an array representation
         *
         * @param array $data
         * @return Cursor
         */
        public static function fromArray(array $data): Cursor
        {
            $CursorObject = new Cursor();

            if(isset($data['content_limit']))
                $CursorObject->ContentLimit = $data['content_limit'];

            if(isset($data['cursor']))
                $CursorObject->Cursor = $data['cursor'];

            return $CursorObject;
        }
    }