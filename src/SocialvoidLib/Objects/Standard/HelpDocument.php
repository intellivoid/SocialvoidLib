<?php

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Modes\Standard\ParseMode;
    use SocialvoidLib\Classes\Utilities;

    class HelpDocument
    {
        /**
         * The ID of the help document
         *
         * @var string
         */
        public $ID;

        /**
         * The text of the help document
         *
         * @var string
         */
        public $Text;

        /**
         * The text entities in the help document
         *
         * @var TextEntity[]
         */
        public $Entities;

        public function __construct()
        {
            $this->Entities = [];
        }

        /**
         * Returns an array representation of the object
         *
         * @return array
         */
        public function toArray(): array
        {
            $entities = [];

            foreach($this->Entities as $textEntity)
                $entities[] = $textEntity->toArray();

            return [
                'id' => $this->ID,
                'text' => $this->Text,
                'entities' => $entities
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return HelpDocument
         */
        public static function fromArray(array $data): HelpDocument
        {
            $helpDocumentObject = new HelpDocument();

            if(isset($data['id']))
                $helpDocumentObject->ID = $data['id'];

            if(isset($data['text']))
                $helpDocumentObject->Text = $data['text'];

            if(isset($data['entities']))
            {
                foreach($data['entities'] as $entity)
                    $helpDocumentObject->Entities[] = TextEntity::fromArray($entity);
            }

            return $helpDocumentObject;
        }

        /**
         * Constructs object from a Markdown document
         *
         * @param string $input
         * @return HelpDocument
         * @noinspection PhpRedundantOptionalArgumentInspection
         */
        public static function fromMarkdownDocument(string $input): HelpDocument
        {
            $helpDocumentObject = new HelpDocument();
            $helpDocumentObject->Text = Utilities::extractTextWithoutEntities($input, ParseMode::Markdown);
            $helpDocumentObject->ID = hash('crc32', $helpDocumentObject->Text);
            $entities = Utilities::extractTextEntities($input, ParseMode::Markdown);
            foreach($entities as $entity)
                $helpDocumentObject->Entities[] = $entity->toArray();

            return $helpDocumentObject;
        }
    }