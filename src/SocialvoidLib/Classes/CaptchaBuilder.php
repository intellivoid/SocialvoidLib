<?php

    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Classes;

    use Exception;
    use SocialvoidLib\Classes\Captcha\PhraseBuilder;
    use SocialvoidLib\Interfaces\CaptchaInterface;
    use function imagecolorallocate;
    use function imagettfbbox;
    use function imagettftext;

    class CaptchaBuilder implements CaptchaInterface
    {
        /**
         * @var array
         */
        protected $fingerprint = array();

        /**
         * @var bool
         */
        protected $useFingerprint = false;

        /**
         * @var array
         */
        protected $textColor = array();

        /**
         * @var array
         */
        protected $lineColor = null;

        /**
         * @var array
         */
        protected $backgroundColor = null;

        /**
         * @var array
         */
        protected $backgroundImages = array();

        /**
         * @var resource
         */
        protected $contents = null;

        /**
         * @var string
         */
        protected $phrase = null;

        /**
         * @var PhraseBuilder
         */
        protected $builder;

        /**
         * @var bool
         */
        protected $distortion = true;

        /**
         * The maximum number of lines to draw in front of
         * the image. null - use default algorithm
         */
        protected $maxFrontLines = null;

        /**
         * The maximum number of lines to draw behind
         * the image. null - use default algorithm
         */
        protected $maxBehindLines = null;

        /**
         * The maximum angle of char
         */
        protected $maxAngle = 8;

        /**
         * The maximum offset of char
         */
        protected $maxOffset = 5;

        /**
         * Is the interpolation enabled ?
         *
         * @var bool
         */
        protected $interpolation = true;

        /**
         * Ignore all effects
         *
         * @var bool
         */
        protected $ignoreAllEffects = false;

        /**
         * Allowed image types for the background images
         *
         * @var array
         */
        protected $allowedBackgroundImageTypes = array('image/png', 'image/jpeg', 'image/gif');

        /**
         * @var false|int
         */
        private $background;

        /**
         * The image contents
         */
        public function getContents()
        {
            return $this->contents;
        }

        /**
         * Enable/Disables the interpolation
         *
         * @param $interpolate bool  True to enable, false to disable
         * @return CaptchaBuilder
         * @noinspection PhpUnused
         */
        public function setInterpolation(bool $interpolate = true): CaptchaBuilder
        {
            $this->interpolation = $interpolate;

            return $this;
        }

        /**
         * Temporary dir, for OCR check
         *
         * @var string
         */
        public $tempDir = 'temp/';

        /**
         * Public Constructor
         *
         * @param null $phrase
         * @param PhraseBuilder|null $builder
         */
        public function __construct($phrase = null, PhraseBuilder $builder = null)
        {
            if ($builder === null)
            {
                $this->builder = new PhraseBuilder;
            }
            else
            {
                $this->builder = $builder;
            }

            $this->phrase = is_string($phrase) ? $phrase : $this->builder->build($phrase);
        }

        /**
         * Setting the phrase
         *
         * @param $phrase
         * @noinspection PhpUnused
         */
        public function setPhrase($phrase)
        {
            $this->phrase = (string) $phrase;
        }

        /**
         * Enables/disable distortion
         *
         * @param $distortion
         * @return $this
         * @noinspection PhpUnused
         */
        public function setDistortion($distortion): CaptchaBuilder
        {
            $this->distortion = (bool) $distortion;

            return $this;
        }

        /**
         * @param $maxBehindLines
         * @return $this
         * @noinspection PhpUnused
         */
        public function setMaxBehindLines($maxBehindLines): CaptchaBuilder
        {
            $this->maxBehindLines = $maxBehindLines;

            return $this;
        }

        /**
         * @param $maxFrontLines
         * @return $this
         * @noinspection PhpUnused
         */
        public function setMaxFrontLines($maxFrontLines): CaptchaBuilder
        {
            $this->maxFrontLines = $maxFrontLines;

            return $this;
        }

        /**
         * @param $maxAngle
         * @return $this
         * @noinspection PhpUnused
         */
        public function setMaxAngle($maxAngle): CaptchaBuilder
        {
            $this->maxAngle = $maxAngle;

            return $this;
        }

        /**
         * @param $maxOffset
         * @return $this
         * @noinspection PhpUnused
         */
        public function setMaxOffset($maxOffset): CaptchaBuilder
        {
            $this->maxOffset = $maxOffset;

            return $this;
        }

        /**
         * Gets the captcha phrase
         *
         * @return string|null
         */
        public function getPhrase(): ?string
        {
            return $this->phrase;
        }

        /**
         * Returns true if the given phrase is good
         *
         * @param $phrase
         * @return bool
         */
        public function testPhrase($phrase): bool
        {
            return ($this->builder->niceize($phrase) == $this->builder->niceize($this->getPhrase()));
        }

        /**
         * Instantiation
         *
         * @param null $phrase
         * @return CaptchaBuilder
         */
        public static function create($phrase = null): CaptchaBuilder
        {
            return new self($phrase);
        }

        /**
         * Sets the text color to use
         *
         * @param $r
         * @param $g
         * @param $b
         * @return $this
         * @noinspection PhpUnused
         */
        public function setTextColor($r, $g, $b): CaptchaBuilder
        {
            $this->textColor = array($r, $g, $b);

            return $this;
        }

        /**
         * Sets the background color to use
         *
         * @param $r
         * @param $g
         * @param $b
         * @return $this
         * @noinspection PhpUnused
         */
        public function setBackgroundColor($r, $g, $b): CaptchaBuilder
        {
            $this->backgroundColor = array($r, $g, $b);

            return $this;
        }

        /**
         * @param $r
         * @param $g
         * @param $b
         * @return $this
         * @noinspection PhpUnused
         */
        public function setLineColor($r, $g, $b): CaptchaBuilder
        {
            $this->lineColor = array($r, $g, $b);

            return $this;
        }

        /**
         * Sets the ignoreAllEffects value
         *
         * @param bool $ignoreAllEffects
         * @return CaptchaBuilder
         * @noinspection PhpMissingReturnTypeInspection
         * @noinspection PhpUnused
         */
        public function setIgnoreAllEffects(bool $ignoreAllEffects)
        {
            $this->ignoreAllEffects = $ignoreAllEffects;

            return $this;
        }

        /**
         * Sets the list of background images to use (one image is randomly selected)
         *
         * @param array $backgroundImages
         * @return $this
         * @noinspection PhpUnused
         */
        public function setBackgroundImages(array $backgroundImages): CaptchaBuilder
        {
            $this->backgroundImages = $backgroundImages;

            return $this;
        }

        /**
         * Draw lines over the image
         * @noinspection SpellCheckingInspection
         *
         * @param $image
         * @param $width
         * @param $height
         * @param null $tcol
         */
        protected function drawLine($image, $width, $height, $tcol = null)
        {
            if ($this->lineColor === null)
            {
                $red = $this->rand(100, 255);
                $green = $this->rand(100, 255);
                $blue = $this->rand(100, 255);
            }
            else
            {
                $red = $this->lineColor[0];
                $green = $this->lineColor[1];
                $blue = $this->lineColor[2];
            }

            if ($tcol === null)
            {
                $tcol = imagecolorallocate($image, $red, $green, $blue);
            }

            if ($this->rand(0, 1))
            { // Horizontal
                $Xa   = $this->rand(0, $width/2);
                $Ya   = $this->rand(0, $height);
                $Xb   = $this->rand($width/2, $width);
                $Yb   = $this->rand(0, $height);
            }
            else
            { // Vertical
                $Xa   = $this->rand(0, $width);
                $Ya   = $this->rand(0, $height/2);
                $Xb   = $this->rand(0, $width);
                $Yb   = $this->rand($height/2, $height);
            }

            imagesetthickness($image, $this->rand(1, 3));
            imageline($image, $Xa, $Ya, $Xb, $Yb, $tcol);
        }

        /**
         * Apply some post effects
         */
        protected function postEffect($image)
        {
            if (!function_exists('imagefilter'))
            {
                return;
            }

            if ($this->backgroundColor != null || $this->textColor != null)
            {
                return;
            }

            // Negate ?
            if ($this->rand(0, 1) == 0)
            {
                imagefilter($image, IMG_FILTER_NEGATE);
            }

            // Edge ?
            if ($this->rand(0, 10) == 0)
            {
                imagefilter($image, IMG_FILTER_EDGEDETECT);
            }

            // Contrast
            imagefilter($image, IMG_FILTER_CONTRAST, $this->rand(-50, 10));

            // Colorize
            if ($this->rand(0, 5) == 0)
            {
                imagefilter($image, IMG_FILTER_COLORIZE, $this->rand(-80, 50), $this->rand(-80, 50), $this->rand(-80, 50));
            }
        }

        /**
         * Writes the phrase on the image
         *
         * @param $image
         * @param $phrase
         * @param $font
         * @param $width
         * @param $height
         * @return false|int
         */
        protected function writePhrase($image, $phrase, $font, $width, $height)
        {
            $length = mb_strlen($phrase);
            if ($length === 0)
            {
                return imagecolorallocate($image, 0, 0, 0);
            }

            // Gets the text size and start position
            $size = $width / $length - $this->rand(0, 3) - 1;
            $box = imagettfbbox($size, 0, $font, $phrase);
            $textWidth = $box[2] - $box[0];
            $textHeight = $box[1] - $box[7];
            $x = ($width - $textWidth) / 2;
            $y = ($height - $textHeight) / 2 + $size;

            if (!$this->textColor)
            {
                $textColor = array($this->rand(0, 150), $this->rand(0, 150), $this->rand(0, 150));
            } else {
                $textColor = $this->textColor;
            }
            $col = imagecolorallocate($image, $textColor[0], $textColor[1], $textColor[2]);

            // Write the letters one by one, with random angle
            for ($i=0; $i<$length; $i++) {
                $symbol = mb_substr($phrase, $i, 1);
                $box = imagettfbbox($size, 0, $font, $symbol);
                $w = $box[2] - $box[0];
                $angle = $this->rand(-$this->maxAngle, $this->maxAngle);
                $offset = $this->rand(-$this->maxOffset, $this->maxOffset);
                imagettftext($image, $size, $angle, $x, $y + $offset, $col, $font, $symbol);
                $x += $w;
            }

            return $col;
        }

        /**
         * Try to read the code against an OCR
         * @noinspection SpellCheckingInspection
         */
        public function isOCRReadable(): bool
        {
            if (!is_dir($this->tempDir))
            {
                @mkdir($this->tempDir, 0755, true);
            }

            $tempj = $this->tempDir . uniqid('captcha', true) . '.jpg';
            $tempp = $this->tempDir . uniqid('captcha', true) . '.pgm';

            $this->save($tempj);
            shell_exec("convert $tempj $tempp");
            $value = trim(strtolower(shell_exec("ocrad $tempp")));

            @unlink($tempj);
            @unlink($tempp);

            return $this->testPhrase($value);
        }

        /**
         * Builds while the code is readable against an OCR
         *
         * @param int $width
         * @param int $height
         * @param null $font
         * @param null $fingerprint
         * @throws Exception
         * @noinspection PhpUnused
         */
        public function buildAgainstOCR(int $width = 150, int $height = 40, $font = null, $fingerprint = null)
        {
            do
            {
                $this->build($width, $height, $font, $fingerprint);
            } while ($this->isOCRReadable());
        }


        /**
         * Generate the image
         *
         * @param int $width
         * @param int $height
         * @param null $font
         * @param null $fingerprint
         * @return $this
         * @throws Exception
         * @noinspection PhpUndefinedVariableInspection
         */
        public function build($width = 150, $height = 40, $font = null, $fingerprint = null): CaptchaBuilder
        {
            if (null !== $fingerprint)
            {
                $this->fingerprint = $fingerprint;
                $this->useFingerprint = true;
            }
            else
            {
                $this->fingerprint = array();
                $this->useFingerprint = false;
            }

            if ($font === null)
                $font = __DIR__ . '..' . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR .'Fonts' . DIRECTORY_SEPARATOR . 'captcha'.$this->rand(0, 7).'.ttf';

            if (empty($this->backgroundImages))
            {
                // if background images list is not set, use a color fill as a background
                $image   = imagecreatetruecolor($width, $height);
                if ($this->backgroundColor == null)
                {
                    $bg = imagecolorallocate($image, $this->rand(200, 255), $this->rand(200, 255), $this->rand(200, 255));
                }
                else
                {
                    $color = $this->backgroundColor;
                    $bg = imagecolorallocate($image, $color[0], $color[1], $color[2]);
                }

                $this->background = $bg;
                imagefill($image, 0, 0, $bg);
            }
            else
            {
                // use a random background image
                $randomBackgroundImage = $this->backgroundImages[rand(0, count($this->backgroundImages)-1)];
                $imageType = $this->validateBackgroundImage($randomBackgroundImage);
                $image = $this->createBackgroundImageFromType($randomBackgroundImage, $imageType);
            }

            // Apply effects
            if (!$this->ignoreAllEffects)
            {
                $square = $width * $height;
                $effects = $this->rand($square/3000, $square/2000);

                // set the maximum number of lines to draw in front of the text
                if ($this->maxBehindLines != null && $this->maxBehindLines > 0)
                {
                    $effects = min($this->maxBehindLines, $effects);
                }

                if ($this->maxBehindLines !== 0)
                {
                    for ($e = 0; $e < $effects; $e++)
                    {
                        $this->drawLine($image, $width, $height);
                    }
                }
            }

            // Write CAPTCHA text
            $color = $this->writePhrase($image, $this->phrase, $font, $width, $height);

            // Apply effects
            if (!$this->ignoreAllEffects)
            {
                $square = $width * $height;
                $effects = $this->rand($square/3000, $square/2000);

                // set the maximum number of lines to draw in front of the text
                if ($this->maxFrontLines != null && $this->maxFrontLines > 0)
                {
                    $effects = min($this->maxFrontLines, $effects);
                }

                if ($this->maxFrontLines !== 0)
                {
                    for ($e = 0; $e < $effects; $e++)
                    {
                        $this->drawLine($image, $width, $height, $color);
                    }
                }
            }

            // Distort the image
            if ($this->distortion && !$this->ignoreAllEffects)
            {
                $image = $this->distort($image, $width, $height, $bg);
            }

            // Post effects
            if (!$this->ignoreAllEffects)
            {
                $this->postEffect($image);
            }

            $this->contents = $image;

            return $this;
        }

        /**
         * Distorts the image
         */
        public function distort($image, $width, $height, $bg)
        {
            $contents = imagecreatetruecolor($width, $height);
            $X          = $this->rand(0, $width);
            $Y          = $this->rand(0, $height);
            $phase      = $this->rand(0, 10);
            $scale      = 1.1 + $this->rand(0, 10000) / 30000;
            for ($x = 0; $x < $width; $x++)
            {
                for ($y = 0; $y < $height; $y++)
                {
                    $Vx = $x - $X;
                    $Vy = $y - $Y;
                    $Vn = sqrt($Vx * $Vx + $Vy * $Vy);

                    if ($Vn != 0)
                    {
                        $Vn2 = $Vn + 4 * sin($Vn / 30);
                        $nX  = $X + ($Vx * $Vn2 / $Vn);
                        $nY  = $Y + ($Vy * $Vn2 / $Vn);
                    }
                    else
                    {
                        $nX = $X;
                        $nY = $Y;
                    }

                    $nY = $nY + $scale * sin($phase + $nX * 0.2);

                    if ($this->interpolation)
                    {
                        $p = $this->interpolate(
                            $nX - floor($nX),
                            $nY - floor($nY),
                            $this->getColor($image, floor($nX), floor($nY), $bg),
                            $this->getColor($image, ceil($nX), floor($nY), $bg),
                            $this->getColor($image, floor($nX), ceil($nY), $bg),
                            $this->getColor($image, ceil($nX), ceil($nY), $bg)
                        );
                    }
                    else
                    {
                        $p = $this->getColor($image, round($nX), round($nY), $bg);
                    }

                    if ($p == 0)
                    {
                        $p = $bg;
                    }

                    imagesetpixel($contents, $x, $y, $p);
                }
            }

            return $contents;
        }

        /**
         * Saves the Captcha to a jpeg file
         */
        public function save($filename, $quality = 90)
        {
            imagejpeg($this->contents, $filename, $quality);
        }

        /**
         * Gets the image GD
         * @noinspection PhpUnused
         */
        public function getGd()
        {
            return $this->contents;
        }

        /**
         * Gets the image contents
         */
        public function get($quality = 90)
        {
            ob_start();
            $this->output($quality);

            return ob_get_clean();
        }

        /**
         * Gets the HTML inline base64
         */
        public function inline($quality = 90): string
        {
            return 'data:image/jpeg;base64,' . base64_encode($this->get($quality));
        }

        /**
         * Outputs the image
         */
        public function output($quality = 90)
        {
            imagejpeg($this->contents, null, $quality);
        }

        /**
         * @return array
         * @noinspection PhpUnused
         */
        public function getFingerprint(): array
        {
            return $this->fingerprint;
        }

        /**
         * Returns a random number or the next number in the
         * fingerprint
         */
        protected function rand($min, $max)
        {
            if (!is_array($this->fingerprint))
            {
                $this->fingerprint = array();
            }

            if ($this->useFingerprint)
            {
                $value = current($this->fingerprint);
                next($this->fingerprint);
            }
            else
            {
                $value = mt_rand($min, $max);
                $this->fingerprint[] = $value;
            }

            return $value;
        }

        /**
         * @param $x
         * @param $y
         * @param $nw
         * @param $ne
         * @param $sw
         * @param $se
         * @return int
         */
        protected function interpolate($x, $y, $nw, $ne, $sw, $se): int
        {
            list($r0, $g0, $b0) = $this->getRGB($nw);
            list($r1, $g1, $b1) = $this->getRGB($ne);
            list($r2, $g2, $b2) = $this->getRGB($sw);
            list($r3, $g3, $b3) = $this->getRGB($se);

            $cx = 1.0 - $x;
            $cy = 1.0 - $y;

            $m0 = $cx * $r0 + $x * $r1;
            $m1 = $cx * $r2 + $x * $r3;
            $r  = (int) ($cy * $m0 + $y * $m1);

            $m0 = $cx * $g0 + $x * $g1;
            $m1 = $cx * $g2 + $x * $g3;
            $g  = (int) ($cy * $m0 + $y * $m1);

            $m0 = $cx * $b0 + $x * $b1;
            $m1 = $cx * $b2 + $x * $b3;
            $b  = (int) ($cy * $m0 + $y * $m1);

            return ($r << 16) | ($g << 8) | $b;
        }

        /**
         * @param $image
         * @param $x
         * @param $y
         * @param $background
         * @return int
         */
        protected function getColor($image, $x, $y, $background): int
        {
            $L = imagesx($image);
            $H = imagesy($image);
            if ($x < 0 || $x >= $L || $y < 0 || $y >= $H) {
                return $background;
            }

            return imagecolorat($image, $x, $y);
        }

        /**
         * Returns the RGB relations of a color
         *
         * @param $color
         * @return array
         * @noinspection PhpCastIsUnnecessaryInspection
         */
        protected function getRGB($color): array
        {
            return [
                (int)($color >> 16) & 0xff,
                (int)($color >> 8) & 0xff,
                (int)($color) & 0xff
            ];
        }

        /**
         * Validate the background image path. Return the image type if valid
         *
         * @param string $backgroundImage
         * @return string
         * @throws Exception
         * @noinspection SpellCheckingInspection
         */
        protected function validateBackgroundImage(string $backgroundImage): string
        {
            // check if file exists
            if (!file_exists($backgroundImage))
            {
                $backgroundImageExploded = explode('/', $backgroundImage);
                $imageFileName = count($backgroundImageExploded) > 1? $backgroundImageExploded[count($backgroundImageExploded)-1] : $backgroundImage;
                throw new Exception('Invalid background image: ' . $imageFileName);
            }

            // check image type
            $finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
            $imageType = finfo_file($finfo, $backgroundImage);
            finfo_close($finfo);

            if (!in_array($imageType, $this->allowedBackgroundImageTypes))
                throw new Exception('Invalid background image type! Allowed types are: ' . join(', ', $this->allowedBackgroundImageTypes));

            return $imageType;
        }

        /**
         * Create background image from type
         *
         * @param string $backgroundImage
         * @param string $imageType
         * @return resource
         * @throws Exception
         */
        protected function createBackgroundImageFromType(string $backgroundImage, string $imageType)
        {
            switch ($imageType)
            {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($backgroundImage);
                    break;

                case 'image/png':
                    $image = imagecreatefrompng($backgroundImage);
                    break;

                case 'image/gif':
                    $image = imagecreatefromgif($backgroundImage);
                    break;

                default:
                    throw new Exception('Not supported file type for background image!');
            }
            return $image;
        }

        /**
         * @return false|int
         * @noinspection PhpUnused
         */
        public function getBackground()
        {
            return $this->background;
        }
    }
