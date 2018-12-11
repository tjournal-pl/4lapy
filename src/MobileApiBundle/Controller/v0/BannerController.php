<?php

/*
 * @copyright Copyright (c) NotAgency
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations\Parameter;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\MobileApiBundle\Dto\Request\BannersRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\BannerService as ApiBannerService;
use FourPaws\Helpers\TaggedCacheHelper;
use Bitrix\Main\Application;

class BannerController extends FOSRestController
{
    /**
     * @var ApiBannerService
     */
    private $apiBannerService;

    private $cacheTime = 3600;
    private $cachePath = '/api/banners';

    public function __construct(ApiBannerService $apiBannerService)
    {
        $this->apiBannerService = $apiBannerService;
    }

    /**
     * @Rest\Get(path="/baner_list/")
     * @Rest\View()
     *
     * @param BannersRequest $bannersRequest
     * @return ApiResponse
     * @throws \Bitrix\Main\SystemException
     */
    public function getBannersListAction(BannersRequest $bannersRequest): ApiResponse
    {
        $cache = Application::getInstance()->getCache();
        $cacheId = md5(serialize([
            $bannersRequest->getCityId(),
            $bannersRequest->getSectionCode(),
        ]));
        if ($cache->startDataCache($this->cacheTime, $cacheId, $this->cachePath)) {
            $tagCache = $cache->isStarted() ? new TaggedCacheHelper($this->cachePath) : null;

            $apiResponse = (new ApiResponse())
                ->setData($this->apiBannerService->setCityId($bannersRequest->getCityId())
                    ->getList($bannersRequest->getSectionCode())
                );

            if ($tagCache) {
                TaggedCacheHelper::addManagedCacheTags([$this->cachePath]);
                $tagCache->end();
            }

            $cache->endDataCache($apiResponse);
        } else {
            $apiResponse = $cache->getVars();
        }

        return $apiResponse;
    }

    /**
     * @Rest\Get("/promo_baner/")
     * @Rest\View()
     *
     * @param BannersRequest $bannersRequest
     * @return ApiResponse
     * @throws \Bitrix\Main\SystemException
     */
    public function getPromoBannersAction(BannersRequest $bannersRequest): ApiResponse
    {
        $cache = Application::getInstance()->getCache();
        $cacheId = md5(serialize([
            $bannersRequest->getCityId(),
        ]));
        if ($cache->startDataCache($this->cacheTime, $cacheId, $this->cachePath)) {
            $tagCache = $cache->isStarted() ? new TaggedCacheHelper($this->cachePath) : null;

            $apiResponse = (new ApiResponse())
                ->setData($this->apiBannerService->setCityId($bannersRequest->getCityId())
                    ->getList('mobile_promo'));

            if ($tagCache) {
                TaggedCacheHelper::addManagedCacheTags([$this->cachePath]);
                $tagCache->end();
            }

            $cache->endDataCache($apiResponse);
        } else {
            $apiResponse = $cache->getVars();
        }
        return $apiResponse;
    }
}
