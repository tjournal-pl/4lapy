<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\GroupTable;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\PageNavigation;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\EmptyEntityClass;
use FourPaws\Enum\UserGroup;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\PersonalBundle\Entity\Referral;
use FourPaws\PersonalBundle\Service\ReferralService;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

/**
 * Class FourPawsPersonalCabinetReferralComponent
 */
class FourPawsPersonalCabinetReferralComponent extends CBitrixComponent
{
    /**
     * @var ReferralService
     */
    private $referralService;

    /** @var UserAuthorizationInterface */
    private $authUserProvider;

    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;
    /** @var User */
    private $curUser;
    /** @var string */
    private $cachePath;
    /** @var Application */
    private $instance;
    /** @var Cache */
    private $cache;

    /**
     * FourPawsPersonalCabinetReferralComponent constructor.
     *
     * @param null|\CBitrixComponent $component
     *
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws \RuntimeException
     * @throws ServiceCircularReferenceException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        try {
            $container = App::getInstance()->getContainer();
        } catch (ApplicationCreateException $e) {
            $logger = LoggerFactory::create('component');
            $logger->error(sprintf(
                'Component execute error: [%s] %s in %s:%d',
                $e->getCode(),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            ));
            /** @noinspection PhpUnhandledExceptionInspection */
            throw new SystemException($e->getMessage(), $e->getCode(), $e->getFile(), $e->getLine(), $e);
        }
        $this->referralService = $container->get('referral.service');
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
        $this->authUserProvider = $container->get(UserAuthorizationInterface::class);
    }

    /**
     * @param $params
     *
     * @return array
     */
    public function onPrepareComponentParams($params): array
    {
        $params['PAGE_COUNT'] = 10;
        /** @noinspection SummerTimeUnsafeTimeManipulationInspection */
        /** кешируем на сутки, можно будет увеличить если обновления будут не очень частые - чтобы лишний кеш не хранился */
        $params['CACHE_TIME'] = 24 * 60 * 60;
        return $params;
    }

    /**
     * {@inheritdoc}
     * @return bool|null
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws ObjectException
     * @throws SystemException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     */
    public function executeComponent(): ?bool
    {
        if (!$this->checkPermissions()) {
            return null;
        }

        $this->init();
        /** @var PageNavigation $nav */
        $nav = $this->arResult['NAV'];
        $cacheItems = [];
        $this->arResult['USER_ID'] = $this->curUser->getId();
        if ($this->cache->initCache($this->arParams['CACHE_TIME'],
            serialize([
                'userId'        => $this->arResult['USER_ID'],
                'page'          => $nav->getCurrentPage(),
                'search'        => $this->arResult['search'],
                'referral_type' => $this->arResult['referralType'],
            ]),
            $this->cachePath)) {
            $result = $this->cache->getVars();
            $this->arResult['NAV'] = unserialize($result['NAV']);
            $this->arResult['BONUS'] = $result['BONUS'];
            $cacheItems = unserialize($result['cacheItems']);

            $this->arResult['COUNT'] = $result['COUNT'];
            $this->arResult['COUNT_ACTIVE'] = $result['COUNT_ACTIVE'];
            $this->arResult['COUNT_MODERATE'] = $result['COUNT_MODERATE'];
        } elseif ($this->cache->startDataCache()) {
            $tagCache = new TaggedCacheHelper($this->cachePath);

            $cacheItems = $this->loadData($tagCache);
            $this->loadCounters();

            $tagCache->addTags([
                'personal:referral:' . $this->arResult['USER_ID'],
                'hlb:field:referral_user:' . $this->arResult['USER_ID'],
            ]);

            $tagCache->end();
            $this->cache->endDataCache([
                'NAV'        => serialize($this->arResult['NAV']),
                'BONUS'      => $this->arResult['BONUS'],
                'cacheItems' => serialize($cacheItems),

                'COUNT'          => $this->arResult['COUNT'],
                'COUNT_ACTIVE'   => $this->arResult['COUNT_ACTIVE'],
                'COUNT_MODERATE' => $this->arResult['COUNT_MODERATE'],
            ]);
        }

        $this->showTemplate($cacheItems);

        return true;
    }

    /**
     * @param $cacheItems
     *
     * @throws SystemException
     */
    protected function showTemplate($cacheItems): void
    {
        /** @var PageNavigation $nav */
        $nav = $this->arResult['NAV'];
        if ($this->startResultCache(
            $this->arParams['CACHE_TIME'],
            [
                'cacheItems'    => $cacheItems,
                'count'         => $nav->getRecordCount(),
                'page'          => $nav->getCurrentPage(),
                'bonus'         => $this->arResult['BONUS'],
                'search'        => $this->arResult['search'],
                'referral_type' => $this->arResult['referralType'],
            ],
            $this->cachePath
        )) {
            TaggedCacheHelper::addManagedCacheTags([
                'personal:referral',
                'personal:referral:' . $this->arResult['USER_ID'],
                'hlb:field:referral_user:' . $this->arResult['USER_ID'],
            ]);

            $this->arResult['referral_type'] = $this->referralService->getReferralType();
            $this->arResult['FORMATED_BONUS'] = \number_format($this->arResult['BONUS'], 0, '.', ' ');

            $this->includeComponentTemplate();
        }
    }

    /**
     * @return bool
     */
    protected function checkPermissions(): bool
    {
        if (!$this->authUserProvider->isAuthorized()) {
            define('NEED_AUTH', true);

            return false;
        }

        try {
            $this->curUser = $this->currentUserProvider->getCurrentUser();
            $optId = (int)GroupTable::query()->setFilter(['STRING_ID' => UserGroup::OPT_CODE])->setLimit(1)->setSelect(['ID'])->setCacheTtl(360000)->exec()->fetch()['ID'];
            if ($optId === 0) {
                $optId = UserGroup::OPT_ID;
            }
            if (!\in_array($optId, $this->currentUserProvider->getUserGroups(), true)) {
                LocalRedirect('/personal');
            }
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);

            return false;
        } catch (Exception $e) {
            define('NEED_AUTH', true);

            return false;
        }

        return true;
    }

    /**
     * @param TaggedCacheHelper $tagCache
     */
    protected function redirect(TaggedCacheHelper $tagCache): void
    {
        $tagCache->abortTagCache();
        $this->cache->abortDataCache();
        TaggedCacheHelper::clearManagedCache(['personal:referral:' . $this->arResult['USER_ID']]);
        LocalRedirect($this->request->getRequestUri());
        die();
    }

    /**
     * @throws SystemException
     */
    protected function init(): void
    {
        $this->instance = Application::getInstance();

        $this->setFrameMode(true);

        $this->arResult['ITEMS'] = new ArrayCollection();

        $nav = new PageNavigation('nav-referral');
        $nav->allowAllRecords(false)->setPageSize($this->arParams['PAGE_COUNT'])->initFromUri();

        $this->arResult['NAV'] = $nav;
        $this->cache = $this->instance->getCache();
        $this->request = $this->instance->getContext()->getRequest();
        $this->arResult['search'] = (string)$this->request->get('search');
        $this->arResult['referralType'] = (string)$this->request->get('referral_type');
        $this->cachePath = $this->getCachePath() ?: $this->getPath();
    }

    /**
     * @param TaggedCacheHelper $tagCache
     *
     * @return ArrayCollection|null
     * @throws ApplicationCreateException
     * @throws EmptyEntityClass
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function loadData(TaggedCacheHelper $tagCache): ?ArrayCollection
    {
        $cacheItems = $items = new ArrayCollection();
        try {
            /** @var ArrayCollection $items
             * @var bool $redirect
             */
            $main = empty($this->arResult['referralType']) && empty($this->arResult['search']);
            [$items, $redirect, $this->arResult['BONUS'], $this->arResult['NAV']] = $this->referralService->getCurUserReferrals($this->arResult['NAV'],
                $main, false);
            if ($this->arResult['BONUS'] > 0) {
                /** отбрасываем дробную часть - нужно ли? */
                $this->arResult['BONUS'] = floor($this->arResult['BONUS']);
            }
            if ($redirect) {
                $this->redirect($tagCache);
            }
            $this->arResult['ITEMS'] = $items;
        } catch (NotAuthorizedException $e) {
            define('NEED_AUTH', true);
            $tagCache->abortTagCache();
            $this->cache->abortDataCache();
            return null;
        }

        if (!$items->isEmpty()) {
            /** @var Referral $item */
            /** @noinspection ForeachSourceInspection */
            foreach ($items as $item) {
                if ($item instanceof Referral) {
                    $cardId = $item->getCard();
                    $cacheItems[$cardId] = [
                        'bonus'     => $item->getBonus(),
                        'card'      => $cardId,
                        'moderated' => $item->isModerate(),
//                            'dateEndActive' => $item->getDateEndActive(),
                    ];
                }
            }
        }

        return $cacheItems;
    }

    protected function loadCounters(): void
    {
        try {
            $this->arResult['COUNT'] = $this->referralService->getAllCountByUser();
            $this->arResult['COUNT_ACTIVE'] = $this->referralService->getActiveCountByUser();
            $this->arResult['COUNT_MODERATE'] = $this->referralService->getModeratedCountByUser();
        } catch (ArgumentException|SystemException $e) {
            /** @todo залогировать ошибку */
        }

    }
}
