<?php

namespace Bellwether\BWCMSBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\ContentMetaEntity;


class ContentManager extends BaseService
{

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return ContentManager
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * @param ContentEntity $content
     * @return ContentEntity|void
     */
    public function save(ContentEntity $content = null)
    {
        if (null === $content) {
            return;
        }

        if($content->getId()==null){
            $content->setCreatedDate(new \DateTime());
        }
        $content->setModifiedDate(new \DateTime());
        if ($content->getAuthor() == null) {
            $content->setAuthor($this->getUser());
        }
        $this->em()->persist($content);
        $this->em()->flush();
        return $content;
    }


}