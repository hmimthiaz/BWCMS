<?php

namespace Bellwether\BWCMSBundle\Classes\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Bellwether\BWCMSBundle\Classes\Base\BaseService;

use Bellwether\BWCMSBundle\Entity\PreferenceEntity;
use Bellwether\BWCMSBundle\Entity\ContentEntity;
use Bellwether\BWCMSBundle\Entity\SearchEntity;
use Bellwether\BWCMSBundle\Classes\Constants\PreferenceFieldType;
use Bellwether\BWCMSBundle\Classes\Content\ContentType;
use Bellwether\BWCMSBundle\Classes\Constants\ContentPublishType;


class SearchService extends BaseService
{

    private $commonWords = null;

    function __construct(ContainerInterface $container = null, RequestStack $request_stack = null)
    {
        $this->setContainer($container);
        $this->setRequestStack($request_stack);
    }

    /**
     * @return SearchService
     */
    public function getManager()
    {
        return $this;
    }

    /**
     * Service Init.
     */
    public function init()
    {
        if (!$this->loaded) {

        }
        $this->loaded = true;
    }

    public function runIndex()
    {
        $indexContentTypes = $this->cm()->getIndexedContentTypes();
        if (empty($indexContentTypes)) {
            return;
        }

        $contentRepository = $this->cm()->getContentRepository();
        $qb = $contentRepository->createQueryBuilder('node');
        $qb->add('orderBy', 'node.modifiedDate ASC');
        /**
         * @var ContentType $contentType
         */
        $condition = array();
        foreach ($indexContentTypes as $contentType) {
            $condition[] = " (node.type = '" . $contentType->getType() . "' AND node.schema = '" . $contentType->getSchema() . "' )";
        }
        if (!empty($condition)) {
            $qb->andWhere(' ( ' . implode(' OR ', $condition) . ' ) ');
        }
        $qb->andWhere($qb->expr()->gt('node.modifiedDate', ':date_modified'));
        $qb->setParameter('date_modified', $this->getLastIndexedDate(), \Doctrine\DBAL\Types\Type::DATETIME);
        $qb->setFirstResult(0);
        $qb->setMaxResults(5);
        $result = $qb->getQuery()->getResult();
        $lastContentModifiedDate = new \DateTime();

        if (!empty($result)) {
            /**
             * @var ContentEntity $content
             */
            foreach ($result as $content) {
                $this->indexContent($content);
                $lastContentModifiedDate = $content->getModifiedDate();
            }
        }
        $this->saveLastIndexDate($lastContentModifiedDate);
    }

    public function indexContent(ContentEntity $content = null)
    {
        if (is_null($content)) {
            return;
        }

        $contentClass = $this->cm()->getContentClass($content->getType(), $content->getSchema());
        if (!$contentClass->isIndexed()) {
            return;
        }
        $contentFields = $this->cm()->getContentFields();
        $indexedFields = $contentClass->getIndexedFields();

        $indexCoreFields = array_intersect($contentFields, $indexedFields);
        $indexMetaFields = array_diff($indexedFields, $contentFields);

        $indexText = null;
        if (!empty($indexCoreFields)) {
            foreach ($indexCoreFields as $field) {
                $indexText = $indexText . ' ' . $content->{'get' . $field}(true);
            }
        }
        if (!empty($indexMetaFields)) {
            $metaFields = $this->cm()->getContentAllMeta($content);
            foreach ($indexMetaFields as $field) {
                if (isset($metaFields[$field])) {
                    $indexText = $indexText . ' ' . $metaFields[$field];
                }
            }
        }
        $searchEntity = $this->searchIndexEntity($content);
        if (is_null($indexText)) {
            if (!is_null($searchEntity)) {
                $this->em()->remove($searchEntity);
                $this->em()->flush();
            }
            return;
        }
        if (is_null($searchEntity)) {
            $searchEntity = new SearchEntity();
            $searchEntity->setContent($content);
            $searchEntity->setSite($content->getSite());
        }
        $indexText = $this->cleanText($indexText);
        $searchEntity->setKeywords($indexText);
        $searchEntity->setIndexedDate(new \DateTime());
        $this->em()->persist($searchEntity);
        $this->em()->flush();
        return true;
    }

    private function searchIndexEntity(ContentEntity $content)
    {
        $criteria = array(
            'site' => $content->getSite(),
            'content' => $content
        );
        return $this->getSearchRepository()->findOneBy($criteria);
    }

    private function cleanText($string)
    {
        //remove html
        $string = strip_tags($string);
        //remove html entites
        $string = preg_replace("/&#?[a-z0-9]+;/i", "", $string);
        //$only words
        $string = preg_replace('/\W+/', ' ', $string);
        //unique iy
        $arrayWords = explode(" ", $string);
        $arrayWords = array_unique($arrayWords);
        $string = implode(" ", $arrayWords);
        //remove stop words
        $string = preg_replace('/\b(' . implode('|', $this->getCommonWords()) . ')\b/i', '', $string);
        $string = str_replace('  ', ' ', $string);
        $string = str_replace('  ', ' ', $string);
        return $string;
    }

    public function getCommonWords()
    {
        if (!is_null($this->commonWords)) {
            return $this->commonWords;
        }

        // MYSQL FULLtext Stop words
        $this->commonWords = array(" a ", "a's", "able", "about", "above", "according", "accordingly", "across", "actually",
            "after", "afterwards", "again", "against", "ain't", "all", "allow", "allows", "almost", "alone", "along",
            "already", "also", "although", "always", "am", "among", "amongst", "an", "and", "another", "any", "anybody",
            "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate",
            "are", "aren't", "around", "as", "aside", "ask", "asking", "associated", "at", "available", "away", "awfully",
            "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being",
            "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "but", "by",
            "c'mon", "c's", "came", "can", "can't", "cannot", "cant", "cause", "causes", "certain", "certainly", "changes",
            "clearly", "co", "com", "come", "comes", "concerning", "consequently", "consider", "considering", "contain",
            "containing", "contains", "corresponding", "could", "couldn't", "course", "currently", "definitely", "described",
            "despite", "did", "didn't", "different", "do", "does", "doesn't", "doing", "don't", "done", "down", "downwards",
            "during", "each", "edu", "eg", "eight", "either", "else", "elsewhere", "enough", "entirely", "especially", "et",
            "etc", "even", "ever", "every", "everybody", "everyone", "everything", "everywhere", "ex", "exactly", "example",
            "except", "far", "few", "fifth", "first", "five", "followed", "following", "follows", "for", "former", "formerly",
            "forth", "four", "from", "further", "furthermore", "get", "gets", "getting", "given", "gives", "go", "goes", "going",
            "gone", "got", "gotten", "greetings", "had", "hadn't", "happens", "hardly", "has", "hasn't", "have", "haven't", "having",
            "he", "he's", "hello", "help", "hence", "her", "here", "here's", "hereafter", "hereby", "herein", "hereupon", "hers",
            "herself", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "i'd", "i'll", "i'm",
            "i've", "ie", "if", "ignored", "immediate", "in", "inasmuch", "inc", "indeed", "indicate", "indicated", "indicates",
            "inner", "insofar", "instead", "into", "inward", "is", "isn't", "it", "it'd", "it'll", "it's", "its", "itself", "just",
            "keep", "keeps", "kept", "know", "knows", "known", "last", "lately", "later", "latter", "latterly", "least", "less", "lest",
            "let", "let's", "like", "liked", "likely", "little", "look", "looking", "looks", "ltd", "mainly", "many", "may", "maybe", "me",
            "mean", "meanwhile", "merely", "might", "more", "moreover", "most", "mostly", "much", "must", "my", "myself", "name", "namely",
            "nd", "near", "nearly", "necessary", "need", "needs", "neither", "never", "nevertheless", "new", "next", "nine", "no", "nobody",
            "non", "none", "noone", "nor", "normally", "not", "nothing", "novel", "now", "nowhere", "obviously", "of", "off", "often", "oh",
            "ok", "okay", "old", "on", "once", "one", "ones", "only", "onto", "or", "other", "others", "otherwise", "ought", "our", "ours",
            "ourselves", "out", "outside", "over", "overall", "own", "particular", "particularly", "per", "perhaps", "placed", "please", "plus",
            "possible", "presumably", "probably", "provides", "que", "quite", "qv", "rather", "rd", "re", "really", "reasonably", "regarding",
            "regardless", "regards", "relatively", "respectively", "right", "said", "same", "saw", "say", "saying", "says", "second", "secondly",
            "see", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven",
            "several", "shall", "she", "should", "shouldn't", "since", "six", "so", "some", "somebody", "somehow", "someone", "something", "sometime",
            "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "sub", "such", "sup", "sure",
            "t's", "take", "taken", "tell", "tends", "th", "than", "thank", "thanks", "thanx", "that", "that's", "thats", "the", "their",
            "theirs", "them", "themselves", "then", "thence", "there", "there's", "thereafter", "thereby", "therefore", "therein", "theres",
            "thereupon", "these", "they", "they'd", "they'll", "they're", "they've", "think", "third", "this", "thorough", "thoroughly",
            "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "took", "toward", "towards", "tried",
            "tries", "truly", "try", "trying", "twice", "two", "un", "under", "unfortunately", "unless", "unlikely", "until", "unto", "up",
            "upon", "us", "use", "used", "useful", "uses", "using", "usually", "value", "various", "very", "via", "viz", "vs", "want", "wants",
            "was", "wasn't", "way", "we", "we'd", "we'll", "we're", "we've", "welcome", "well", "went", "were", "weren't", "what", "what's",
            "whatever", "when", "whence", "whenever", "where", "where's", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever",
            "whether", "which", "while", "whither", "who", "who's", "whoever", "whole", "whom", "whose", "why", "will", "willing", "wish", "with",
            "within", "without", "won't", "wonder", "would", "wouldn't", "yes", "yet", "you", "you'd", "you'll", "you're", "you've", "your",
            "yours", "yourself", "yourselves", "zer");

        return $this->commonWords;
    }

    public function getLastIndexedDate()
    {
        $criteria = array(
            'field' => '_SEARCH_LIT_',
            'fieldType' => PreferenceFieldType::Internal,
            'type' => '_SEARCH_',
            'site' => null
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (is_null($preferenceEntity)) {
            return new \DateTime('@0');
        }
        return new \DateTime($preferenceEntity->getValue());
    }

    public function saveLastIndexDate(\DateTime $dateTime)
    {
        $criteria = array(
            'field' => '_SEARCH_LIT_',
            'fieldType' => PreferenceFieldType::Internal,
            'type' => '_SEARCH_',
            'site' => null
        );
        /**
         * @var \Bellwether\BWCMSBundle\Entity\PreferenceEntity $preferenceEntity
         */
        $preferenceRepo = $this->pref()->getPreferenceRepository();
        $preferenceEntity = $preferenceRepo->findOneBy($criteria);
        if (is_null($preferenceEntity)) {
            $preferenceEntity = new PreferenceEntity();
            $preferenceEntity->setField('_SEARCH_LIT_');
            $preferenceEntity->setType('_SEARCH_');
            $preferenceEntity->setFieldType(PreferenceFieldType::Internal);
            $preferenceEntity->setSite(null);
        }
        $dateString = $dateTime->format(\DateTime::ISO8601);
        $preferenceEntity->setValue($dateString);
        $this->em()->persist($preferenceEntity);
        $this->em()->flush();
        return true;
    }

    /**
     * @return \Bellwether\BWCMSBundle\Entity\SearchRepository
     */
    public function getSearchRepository()
    {
        return $this->em()->getRepository('BWCMSBundle:SearchEntity');
    }
}
