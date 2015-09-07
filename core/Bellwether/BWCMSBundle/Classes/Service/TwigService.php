<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\EntityManager;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMediaEntity;
use Bellwether\BWCMSBundle\Entity\ThumbStyleEntity;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Knp\Menu\FactoryInterface;
use Gregwar\Image\Image;
use Bellwether\Common\Pagination;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TwigService extends BaseService implements \Twig_ExtensionInterface
{

    private $factory;

    private $currentSkinFolder = null;

    private $skinAssetPrefix = null;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    function __construct(FactoryInterface $factory = null, ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setFactory($factory);
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * Initializes the runtime environment.
     *
     * This is where you can load some file that contains filter functions for instance.
     *
     * @param \Twig_Environment $environment The current Twig_Environment instance
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->setEnvironment($environment);
    }

    /**
     * @return \Twig_Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param \Twig_Environment $environment
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;
    }

    /**
     * Returns the token parser instances to add to the existing list.
     *
     * @return array An array of Twig_TokenParserInterface or Twig_TokenParserBrokerInterface instances
     */
    public function getTokenParsers()
    {
        return array();
    }

    /**
     * Returns the node visitor instances to add to the existing list.
     *
     * @return \Twig_NodeVisitorInterface[] An array of Twig_NodeVisitorInterface instances
     */
    public function getNodeVisitors()
    {
        return array();
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ellipse', array($this, 'getEllipse')),
        );
    }

    /**
     * Returns a list of tests to add to the existing list.
     *
     * @return array An array of tests
     */
    public function getTests()
    {
        return array();
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'skin' => new \Twig_Function_Method($this, 'getSkin', array('is_safe' => array('html'))),
            'skinAsset' => new \Twig_Function_Method($this, 'getSkinAsset', array('is_safe' => array('html'))),
            'setSkinAssetPrefix' => new \Twig_Function_Method($this, 'setSkinAssetPrefix', array('is_safe' => array('html'))),
            'link' => new \Twig_Function_Method($this, 'getContentLink'),
            'menu' => new \Twig_Function_Method($this, 'getContentMenu', array('is_safe' => array('html'))),
            'widget' => new \Twig_Function_Method($this, 'getContentWidget', array('is_safe' => array('html'))),
            'meta' => new \Twig_Function_Method($this, 'getContentMeta', array('is_safe' => array('html'))),
            'pref' => new \Twig_Function_Method($this, 'getPreference', array('is_safe' => array('html'))),
            'image' => new \Twig_Function_Method($this, 'getImage'),
            'thumb' => new \Twig_Function_Method($this, 'getThumbImage'),
            'pagination' => new \Twig_Function_Method($this, 'getPagination', array('is_safe' => array('html'))),
            'loc' => new \Twig_Function_Method($this, 'getLocale', array('is_safe' => array('html'))),
            'emailLink' => new \Twig_Function_Method($this, 'getEmailLink', array('is_safe' => array('html'))),
            'exit' => new \Twig_Function_Method($this, 'doExit', array('is_safe' => array('html'))),
        );
    }

    /**
     * Returns a list of operators to add to the existing list.
     *
     * @return array An array of operators
     */
    public function getOperators()
    {
        return array();
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return array();
    }

    /**
     * @param string $template
     * @return string
     */
    public function getSkin($template)
    {
        $skinFolder = $this->getCurrentSkinFolder();
        return '@' . $skinFolder . DIRECTORY_SEPARATOR . $template;
    }

    public function setSkinAssetPrefix($prefix)
    {
        $this->skinAssetPrefix = $prefix;
    }

    /**
     * @param string $template
     * @return string
     */
    public function getSkinAsset($template)
    {
        $skinFolder = $this->getCurrentSkinFolder();
        $returnValue = '/skins/' . strtolower($skinFolder) . '/';
        if (!is_null($this->skinAssetPrefix)) {
            $returnValue .= $this->skinAssetPrefix . '/';
        }
        $returnValue .= $template;
        return $returnValue;
    }

    /**
     * @param ContentEntity $contentEntity
     * @return null|string
     */
    public function getContentLink($contentEntity)
    {
        return $this->cq()->getPublicURL($contentEntity);
    }

    function getEllipse($text, $limit = 300, $end = '...')
    {
        if (strlen($text) <= $limit) {
            return $text;
        }

        $textArray = explode(' ', $text);
        $newText = '';
        foreach ($textArray as $word) {
            $newText = $newText . $word . ' ';
            if (strlen($newText) >= $limit) {
                break;
            }
        }
        $newText = $newText . $end;
        return $newText;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param array $options
     * @return string
     */
    public function getContentMenu($contentEntity, $options = array())
    {
        if (!($contentEntity instanceof ContentEntity)) {
            $contentEntity = $this->cq()->getContentBySlugPath($contentEntity);
            if (is_null($contentEntity)) {
                return '';
            }
        }

        $resolver = new OptionsResolver();
        $resolver->setDefaults(array(
            'factory' => $this->factory,
            'environment' => $this->environment,
            'emptyTitle' => false,
            'template' => false,
            'allow_safe_labels' => false,
            'currentClass' => 'active',
            'firstClass' => 'first',
            'lastClass' => 'last',
            'class' => 'menu-' . $contentEntity->getSlug(),
            'id' => 'menu-' . $contentEntity->getSlug(),
        ));
        $menuOptions = $resolver->resolve($options);
        $contentClass = $this->cm()->getContentClass($contentEntity->getType(), $contentEntity->getSchema());
        return $contentClass->render($contentEntity, $menuOptions);


    }

    /**
     * @param ContentEntity $contentEntity
     * @param array $options
     * @return string
     */
    public function getContentWidget($contentEntity, $options = array())
    {
        if (!($contentEntity instanceof ContentEntity)) {
            $contentEntity = $this->cq()->getContentBySlugPath($contentEntity);
            if (is_null($contentEntity)) {
                return '';
            }
        }

        $contentClass = $this->cm()->getContentClass($contentEntity->getType(), $contentEntity->getSchema());
        return $contentClass->render($contentEntity);
    }

    /**
     * @param ContentEntity $contentEntity
     * @param $metaKey
     * @param bool $default
     * @return bool
     */
    public function getContentMeta($contentEntity, $metaKey, $default = false)
    {
        return $this->cm()->getContentMeta($contentEntity, $metaKey, $default);
    }


    public function getPreference($type, $field = false, $default = false)
    {
        return $this->pref()->getPreference($type, $field, $default);
    }

    /**
     * @param ContentEntity $contentEntity
     * @param bool $default
     * @return bool|string
     */
    public function getImage($contentEntity, $default = false)
    {
        /**
         * @var ContentMediaEntity $media
         */
        if (empty($contentEntity)) {
            return $default;
        }
        if (!$this->mm()->isMedia($contentEntity)) {
            return $default;
        }
        $media = $contentEntity->getMedia()->first();
        $this->mm()->checkAndCreateMediaCacheFile($media);
        if ($this->mm()->isImage($contentEntity)) {
            $filename = $this->mm()->getMediaCachePath($media);
            $path = $this->getThumbService()->open($filename)->resize($media->getWidth(), $media->getHeight())->cacheFile('guess');
        } else {
            $filename = $this->mm()->getMimeResourceImage($media->getExtension());
            $path = $this->getThumbService()->open($filename)->resize(128, 128)->cacheFile('guess');
        }
        return $path;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param $thumbSlug
     * @param float $scaleFactor
     * @return mixed|null|string
     */
    public function getThumbImage($contentEntity, $thumbSlug, $scaleFactor = 1.0)
    {
        if (empty($contentEntity)) {
            return null;
        }

        $thumbEntity = $this->cache()->fetch('thumbStyle_' . $thumbSlug);
        if (empty($thumbEntity)) {
            $thumbEntity = $this->mm()->getThumbStyle($thumbSlug, $this->sm()->getCurrentSite());
            if (empty($thumbEntity)) {
                $thumbEntity = new ThumbStyleEntity();
                $thumbEntity->setSite($this->sm()->getCurrentSite());
                $thumbInfo = $this->tp()->getCurrentSkin()->getThumbStyleDefault($thumbSlug);
                if (!is_null($thumbInfo)) {
                    $thumbEntity->setName($thumbInfo['name']);
                    $thumbEntity->setSlug($thumbSlug);
                    $thumbEntity->setMode($thumbInfo['mode']);
                    $thumbEntity->setWidth($thumbInfo['width']);
                    $thumbEntity->setHeight($thumbInfo['height']);
                } else {
                    $thumbEntity->setName($thumbSlug);
                    $thumbEntity->setSlug($thumbSlug);
                    $thumbEntity->setMode('scaleResize');
                    $thumbEntity->setWidth(100);
                    $thumbEntity->setHeight(100);
                }
                $this->em()->persist($thumbEntity);
                $this->em()->flush();
            }
            $this->cache()->save('thumbStyle_' . $thumbSlug, $thumbEntity, 600);
        }

        $url = $this->generateUrl('media_thumb_view', array(
            'siteSlug' => $this->sm()->getCurrentSite()->getSlug(),
            'contentId' => $contentEntity->getId(),
            'thumbSlug' => $thumbSlug,
            'scale' => $scaleFactor
        ));

        return $url;
    }

    /**
     * @param Pagination $pager
     */
    public function getPagination(Pagination $pager)
    {

        $paginationTemplate = $this->tp()->getCurrentSkin()->getPaginationTemplate();

        $html = $this->container->get('templating')->render($paginationTemplate, array('cp' => $pager));

        return $html;
    }

    /**
     * @param string $string
     * @return string
     */
    public function getLocale($string)
    {
        return call_user_func_array(array($this->locale(), "get"), func_get_args());
    }


    /**
     * @param string $email
     * @param string $email
     * @return string
     */
    public function getEmailLink($email)
    {
        $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        $key = str_shuffle($character_set);
        $cipher_text = '';
        $id = 'e' . rand(1, 999999999);
        for ($i = 0; $i < strlen($email); $i += 1) {
            $cipher_text .= $key[strpos($character_set, $email[$i])];
        }
        $script = 'var a="' . $key . '";var b=a.split("").sort().join("");var c="' . $cipher_text . '";var d="";';
        $script .= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
        $script .= 'document.getElementById("' . $id . '").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"';
        $script = "eval(\"" . str_replace(array("\\", '"'), array("\\\\", '\"'), $script) . "\")";
        $script = '<script type="text/javascript">/*<![CDATA[*/' . $script . '/*]]>*/</script>';
        return '<span id="' . $id . '">[javascript protected email address]</span>' . $script;
    }

    public function doExit()
    {
        exit();
    }

    /**
     * @return string
     */
    public function getCurrentSkinFolder()
    {
        if (is_null($this->currentSkinFolder)) {
            $this->currentSkinFolder = $this->sm()->getCurrentSite()->getSkinFolderName();
        }
        return $this->currentSkinFolder;
    }

    /**
     * @return Image
     */
    public function getThumbService()
    {
        return $this->container->get('image.handling');
    }


    /**
     * @param FactoryInterface $factory
     */
    public function setFactory(FactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'BWCMS_Twig_Extras';
    }
}