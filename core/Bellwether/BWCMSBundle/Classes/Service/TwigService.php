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
use Bellwether\BWCMSBundle\Classes\Service\ThumbService;

use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortByType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentSortOrderType;


use Knp\Menu\FactoryInterface;
use Gregwar\Image\Image;
use Bellwether\Common\Pagination;
use Symfony\Component\OptionsResolver\OptionsResolver;


class TwigService extends BaseService implements \Twig_ExtensionInterface
{

    private $factory;

    private $currentSkinFolder = null;

    private $skinAssetPrefix = null;

    private $s3SkinEnabled = null;

    private $s3SkinURLPrefix = null;

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
            new \Twig_SimpleFilter('rgb', array($this, 'getRGB')),
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
            'download' => new \Twig_Function_Method($this, 'getContentDownloadLink'),
            'menu' => new \Twig_Function_Method($this, 'getContentMenu', array('is_safe' => array('html'))),
            'widget' => new \Twig_Function_Method($this, 'getContentWidget', array('is_safe' => array('html'))),
            'meta' => new \Twig_Function_Method($this, 'getContentMeta', array('is_safe' => array('html'))),
            'pref' => new \Twig_Function_Method($this, 'getPreference', array('is_safe' => array('html'))),
            'image' => new \Twig_Function_Method($this, 'getImage'),
            'thumb' => new \Twig_Function_Method($this, 'getThumbImage'),
            'taxonomy' => new \Twig_Function_Method($this, 'getContentTaxonomy'),
            'pagination' => new \Twig_Function_Method($this, 'getPagination', array('is_safe' => array('html'))),
            'loc' => new \Twig_Function_Method($this, 'getLocale', array('is_safe' => array('html'))),
            'emailLink' => new \Twig_Function_Method($this, 'getEmailLink', array('is_safe' => array('html'))),
            'navLink' => new \Twig_Function_Method($this, 'getNavLink'),
            'lang' => new \Twig_Function_Method($this, 'getLanguages', array('is_safe' => array('html'))),
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
        if ($this->getS3SkinEnabled()) {
            $returnValue = $this->getS3SkinURLPrefix() . $returnValue;
        }
        return $returnValue;
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

    function getRGB($hexColor, $alpha = 1.0)
    {
        $hex = str_replace("#", "", $hexColor);
        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "rgba({$r},{$g},{$b},{$alpha})";
    }

    /**
     * @param ContentEntity $contentEntity
     * @param string $schema
     * @return array
     */
    public function getContentTaxonomy($contentEntity, $schema = null)
    {
        return $this->cq()->getContentTaxonomy($contentEntity, $schema);
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
    public function getContentMeta($contentEntity, $metaKey = null, $default = false)
    {
        if (is_null($metaKey)) {
            return $this->cm()->getContentAllMeta($contentEntity);
        }
        return $this->cm()->getContentMeta($contentEntity, $metaKey, $default);
    }


    public function getPreference($type, $field = false, $default = false)
    {
        return $this->pref()->getPreference($type, $field, $default);
    }

    /**
     * @param ContentEntity $contentEntity
     * @return null|string
     */
    public function getContentLink($contentEntity, $full = false)
    {
        if (empty($contentEntity)) {
            return null;
        }

        return $this->cq()->getPublicURL($contentEntity, $full);
    }

    /**
     * @param ContentEntity $contentEntity
     * @return string
     */
    public function getContentDownloadLink($contentEntity)
    {
        if (empty($contentEntity)) {
            return null;
        }

        $url = $this->s3Service()->getContentDownloadLink($contentEntity);
        if (!is_null($url)) {
            return $url;
        }

        $url = $this->generateUrl('media_download_link', array(
            'siteSlug' => $this->sm()->getCurrentSite()->getSlug(),
            'contentId' => $contentEntity->getId()
        ));

        return $url;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param bool $default
     * @return bool|string
     */
    public function getImage($contentEntity, $default = false)
    {
        if (empty($contentEntity)) {
            return $default;
        }

        $url = $this->s3Service()->getImage($contentEntity);
        if (!is_null($url)) {
            return $url;
        }

        $url = $this->generateUrl('media_image_view', array(
            'siteSlug' => $this->sm()->getCurrentSite()->getSlug(),
            'contentId' => $contentEntity->getId()
        ));
        return $url;
    }

    /**
     * @param ContentEntity $contentEntity
     * @param $thumbSlug
     * @param float $scaleFactor
     * @return mixed|null|string
     */
    public function getThumbImage($contentEntity, $thumbSlug, $scaleFactor = 1.0, $fullURL = false)
    {
        if (empty($contentEntity)) {
            return null;
        }

        $url = $this->s3Service()->getThumbImage($contentEntity, $thumbSlug, $scaleFactor);
        if (!is_null($url)) {
            return $url;
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
            $this->cache()->save('thumbStyle_' . $thumbSlug, $thumbEntity);
        }

        $url = $this->generateUrl('media_thumb_view', array(
            'siteSlug' => $this->sm()->getCurrentSite()->getSlug(),
            'contentId' => $contentEntity->getId(),
            'thumbSlug' => $thumbSlug,
            'scale' => $scaleFactor
        ), $fullURL);

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
     * @param ContentEntity $contentEntity
     * @return array
     */
    public function getNavLink($contentEntity)
    {
        $returnArray = array();
        if (empty($contentEntity)) {
            return $returnArray;
        }

        $returnArray = array(
            'caption' => $contentEntity->getTitle(),
            'link' => '#',
            'target' => '',
            'class' => ''
        );

        $contentMeta = $this->cm()->getContentAllMeta($contentEntity);
        if (isset($contentMeta['linkCaption']) && !empty($contentMeta['linkCaption'])) {
            $returnArray['caption'] = $contentMeta['linkCaption'];
        }
        if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'route') {
            if (isset($contentMeta['linkRoute']) && !empty($contentMeta['linkRoute'])) {
                $returnArray['link'] = $this->tp()->getCurrentSkin()->getNavigationRoute($contentMeta['linkRoute']);
            }
        }
        if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'link') {
            if (isset($contentMeta['linkExternal']) && !empty($contentMeta['linkExternal'])) {
                $returnArray['link'] = $contentMeta['linkExternal'];
            }
        }
        if (isset($contentMeta['linkType']) && $contentMeta['linkType'] == 'content') {
            if (isset($contentMeta['linkContent']) && ($contentMeta['linkContent'] instanceof ContentEntity)) {
                $returnArray['link'] = $this->cq()->getPublicURL($contentMeta['linkContent']);
            }
        }
        if (isset($contentMeta['linkTarget']) && !empty($contentMeta['linkTarget'])) {
            $returnArray['target'] = $contentMeta['linkTarget'];
        }
        if (isset($contentMeta['linkClass']) && !empty($contentMeta['linkClass'])) {
            $returnArray['class'] = $contentMeta['linkClass'];
        }
        return $returnArray;
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

    public function getLanguages($onlyCurrent = false)
    {
        if ($onlyCurrent) {
            return $this->sm()->getCurrentSite();
        }
        return $this->sm()->getAllSites();
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

    public function getS3SkinEnabled()
    {
        if (is_null($this->s3SkinEnabled)) {
            $this->s3SkinEnabled = $this->container->getParameter('media.s3SkinEnabled');
        }
        return $this->s3SkinEnabled;
    }

    public function getS3SkinURLPrefix()
    {
        if (is_null($this->s3SkinURLPrefix)) {
            $this->s3SkinURLPrefix = $this->container->getParameter('media.s3SkinURLPrefix');
        }
        return $this->s3SkinURLPrefix;
    }

    /**
     * @return ThumbService
     */
    public function getThumbService()
    {
        return $this->container->get('BWCMS.Thumb');
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