<?php

    namespace SocialvoidLib\Objects\Standard;

    use SocialvoidLib\Abstracts\Modes\Standard\ParseMode;
    use SocialvoidLib\Abstracts\Types\BuiltinTypes;
    use SocialvoidLib\Classes\Utilities;
    use SocialvoidLib\Interfaces\StandardObjectInterface;
    use SocialvoidLib\Objects\Standard\ObjectDefinition;
    use SocialvoidLib\Objects\Standard\ParameterDefinition;
    use SocialvoidLib\Objects\Standard\TypeDefinition;

    class HelpDocument implements StandardObjectInterface
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
            $helpDocumentObject->Entities = Utilities::extractTextEntities($input, ParseMode::Markdown);

            return $helpDocumentObject;
        }

        /**
         * @inheritDoc
         */
        public static function getName(): string
        {
            return 'HelpDocument';
        }

        /**
         * @inheritDoc
         */
        public static function getDescription(): string
        {
            return 'A help document is often retrieved from the server as a way to represent a document to the user for multiple purposes, from quick guides to server announcements or the legal documents required to be shown to the user before they register an account to the network.';
        }

        /**
         * @inheritDoc
         */
        public static function getDefinition(): ObjectDefinition
        {
            return new ObjectDefinition(self::getName(), self::getDescription(), self::getParameters());
        }

        /**
         * @inheritDoc
         */
        public static function getParameters(): array
        {
            return [
                new ParameterDefinition('id', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The ID of the document, if the document gets updated then the ID will change'),

                new ParameterDefinition('text', [
                    new TypeDefinition(BuiltinTypes::String, false)
                ], true, 'The text contents of the document'),

                new ParameterDefinition('entities', [
                    new TypeDefinition('TextEntity', true)
                ], true, 'An array of text entities being represented in the text')
            ];
        }
    }