<?php
/**
 *
 * CacheCollector.php
 *
 * @author:     Szymon Michałowski <szmnmichalowski@gmail.com>
 * @data:       2017-02-08 21:58
 */

namespace DoctrineCacheToolbar\Collector;

use Doctrine\ORM\EntityManager;
use Zend\Mvc\MvcEvent;
use ZendDeveloperTools\Collector\AbstractCollector;
use ZendDeveloperTools\Collector\AutoHideInterface;
use Doctrine\Common\Cache\ArrayCache;

/**
 * Class CacheCollector
 * @package DoctrineCacheToolbar\Collector
 */
class CacheCollector extends AbstractCollector implements AutoHideInterface
{
    /**
     * @var string
     */
    protected $name = 'cache.toolbar';

    /**
     * @var int
     */
    protected $priority = 15;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param MvcEvent $mvcEvent
     * @return array
     */
    public function collect(MvcEvent $mvcEvent)
    {
        if (!isset($this->data)) {
            return $this->data = [];
        }
    }

    /**
     * {@inheritdoc}
     * @return bool
     */
    public function canHide()
    {
        if (!$this->getEntityManager()) {
            return true;
        }

        $isCacheEnabled = $this->getEntityManager()
            ->getConfiguration()
            ->isSecondLevelCacheEnabled();

        if (!$isCacheEnabled) {
            return true;
        }

        return false;
    }

    /**
     * Get cache stats
     *
     * @return array
     */
    public function getCacheStats()
    {
        if (!$this->getEntityManager()) {
            throw new \LogicException('Entity Manager must be set.');
        }

        $config = $this->getEntityManager()->getConfiguration();

        if (!$config->isSecondLevelCacheEnabled()) {
            return $this->data;
        }

        $logger = $config->getSecondLevelCacheConfiguration()
            ->getCacheLogger();

        if (null === $logger) {
            throw new \LogicException('Cache logger must be set.');
        }

        $info = [
            'info' => [
                'metadata_adapter'    => is_object($config->getMetadataCacheImpl())
                    ? $config->getMetadataCacheImpl()
                    : 'NA',
                'query_adapter'       => is_object($config->getQueryCacheImpl())
                    ? $config->getQueryCacheImpl()
                    : 'NA',
                'result_adapter'      => is_object($config->getResultCacheImpl())
                    ? $config->getResultCacheImpl()
                    : 'NA',
                'hydration_adapter'   => is_object($config->getHydrationCacheImpl())
                    ? $config->getHydrationCacheImpl()
                    : 'NA',
            ]
        ];

        $total = [
            'total' => [
                'put' => $logger->getPutCount(),
                'hit' => $logger->getHitCount(),
                'miss' => $logger->getMissCount(),
            ]
        ];

        $regions = [
            'regions' => [
                'put' => $logger->getRegionsPut(),
                'hit' => $logger->getRegionsHit(),
                'miss' => $logger->getRegionsMiss(),
            ]
        ];

        $this->data = array_merge($info, $total, $regions);
        return $this->data;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}