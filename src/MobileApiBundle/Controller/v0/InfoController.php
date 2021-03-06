<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\MobileApiBundle\Controller\v0;

use Bitrix\Main\Application;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\LocationBundle\LocationService;
use FourPaws\MobileApiBundle\Controller\BaseController;
use FourPaws\MobileApiBundle\Dto\Request\InfoRequest;
use FourPaws\MobileApiBundle\Dto\Response as ApiResponse;
use FourPaws\MobileApiBundle\Services\Api\InfoService as ApiInfoService;

class InfoController extends BaseController
{
    /**
     * @var ApiInfoService
     */
    private $apiInfoService;
    private $cacheTime = 3600;
    private $cachePath = '/api/info';

    /** @var LocationService */
    private $locationService;

    public function __construct(ApiInfoService $apiInfoService, LocationService $locationService)
    {
        $this->apiInfoService = $apiInfoService;
        $this->locationService = $locationService;
    }

    /**
     * Получить статичные разделы
     *
     * @todo Вакансии, Конкурсы
     * @Rest\Get("/info/")
     * @Rest\View()

     * @param InfoRequest $infoRequest
     * @return ApiResponse
     * @throws \Bitrix\Main\SystemException
     *
     */
    public function getInfoAction(InfoRequest $infoRequest): ApiResponse
    {
        $cache = Application::getInstance()->getCache();
        $cityId = $infoRequest->getCityId();
        $cityCodeRegion = null;

        if (!$cityId) {
            $cityId = $this->locationService->getCurrentLocation();
        }

        if ($cityId) {
            $cityCodeRegion = $this->locationService->getRegionCode($cityId);
        }

        $cacheId = md5(serialize([
            $cityId,
            $infoRequest->getFields(),
            $infoRequest->getType(),
            $infoRequest->getOfferTypeCode(),
            $infoRequest->getInfoId()
        ]));
        if ($cache->startDataCache($this->cacheTime, $cacheId, $this->cachePath)) {
            $tagCache = $cache->isStarted() ? new TaggedCacheHelper($this->cachePath) : null;

            $apiResponse = (new ApiResponse())
                ->setData([
                    'info' => $this->apiInfoService->getInfo(
                        $infoRequest->getType(),
                        $infoRequest->getInfoId(),
                        $infoRequest->getFields(),
                        $infoRequest->getOfferTypeCode(),
                        $cityCodeRegion
                    )
                ]);

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
