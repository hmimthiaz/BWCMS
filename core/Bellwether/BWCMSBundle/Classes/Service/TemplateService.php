<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;
use Bellwether\BWCMSBundle\Skins\Generic\GenericSkin;

use Bellwether\BWCMSBundle\Classes\Base\BaseSkin;

class TemplateService extends BaseService
{

    private $skins = array();

    private $currentSkin = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    public function init()
    {
        if (!$this->loaded) {
            $this->addDefaultSkins();
        }
        $this->loaded = true;
    }

    /**
     * @return TemplateService
     */
    public function getManager()
    {
        return $this;
    }

    private function addDefaultSkins()
    {
        $this->registerSkin(new GenericSkin($this->container, $this->requestStack));
        //Call other Skins
        $this->getEventDispatcher()->dispatch('BWCMS.Skin.Register');
    }

    /**
     * @param BaseSkin $classInstance
     */
    public function registerSkin($classInstance)
    {
        $slug = $classInstance->getFolderName();
        if (isset($this->skins[$slug])) {
            throw new \RuntimeException("Skins: `{$slug}` already exists.");
        }
        $this->skins[$slug] = $classInstance;

        /**
         * @var \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader $twigLoader
         */
        $twigLoader = $this->container->get('twig.loader');
        $twigLoader->addPath($classInstance->getPath(), $classInstance->getFolderName());
    }

    /**
     * @param $folderName
     */
    public function setSkin($folderName)
    {
        $this->currentSkin = $folderName;
    }

    /**
     * @return BaseSkin|null
     */
    public function getCurrentSkin()
    {
        if (!is_null($this->currentSkin)) {
            return $this->getSkinClass($this->currentSkin);
        }
        return null;
    }

    /**
     * @return array
     */
    public function getSkins()
    {
        $returnSkins = array();
        if (!empty($this->skins)) {
            foreach ($this->skins as $skin) {
                $returnSkins[$skin->getFolderName()] = $skin->getName();
            }
        }
        return $returnSkins;
    }

    /**
     * @param string $folderName
     * @return BaseSkin
     */
    public function getSkinClass($folderName)
    {
        if (!isset($this->skins[$folderName])) {
            throw new \RuntimeException("Skins: `{$folderName}` does not exists.");
        }
        return $this->skins[$folderName];
    }

}
