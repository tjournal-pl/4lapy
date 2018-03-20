<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SapBundle\Consumer;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\Application;
use FourPaws\BitrixOrm\Model\CatalogProduct;
use FourPaws\Catalog\Model\Brand;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Model\Product;
use FourPaws\Migrator\Entity\AddResult;
use FourPaws\SapBundle\Dto\In\Offers\Material;
use FourPaws\SapBundle\Exception\LoggedException;
use FourPaws\SapBundle\Repository\BrandRepository;
use FourPaws\SapBundle\Service\Materials\CatalogProductService;
use FourPaws\SapBundle\Service\Materials\OfferService;
use FourPaws\SapBundle\Service\Materials\ProductService;
use FourPaws\SapBundle\Service\ReferenceService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;

class MaterialConsumer implements ConsumerInterface, LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var ReferenceService
     */
    private $referenceService;
    /**
     * @var OfferService
     */
    private $offerService;
    /**
     * @var ProductService
     */
    private $productService;
    /**
     * @var CatalogProductService
     */
    private $catalogProductService;
    /**
     * @var \Bitrix\Main\DB\Connection
     */
    private $connection;
    /**
     * @var BrandRepository
     */
    private $brandRepository;

    public function __construct(
        ReferenceService $referenceService,
        OfferService $offerService,
        ProductService $productService,
        CatalogProductService $catalogProductService,
        BrandRepository $brandRepository
    ) {
        $this->connection = Application::getConnection();
        $this->referenceService = $referenceService;
        $this->offerService = $offerService;
        $this->productService = $productService;
        $this->catalogProductService = $catalogProductService;
        $this->brandRepository = $brandRepository;
    }

    /**
     * @param Material $material
     *
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @return bool
     */
    public function consume($material): bool
    {
        if (!$this->support($material)) {
            return false;
        }

        try {
            if ($material->isNotUploadToIm()) {
                return $this->offerService->deactivate($material->getOfferXmlId());
            }

            $this->connection->startTransaction();
            $this->referenceService->fillFromMaterial($material);
            $this->connection->commitTransaction();


            $this->connection->startTransaction();
            $brand = $this->getBrand($material);
            $product = $this->getProduct($material, $brand);
            $offer = $this->getOffer($material, $product);
            $this->getCatalogProduct($material, $offer);
            $this->connection->commitTransaction();
            return true;
        } catch (LoggedException $exception) {
        } catch (\Exception $exception) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $this->log()->log(
                LogLevel::CRITICAL,
                sprintf('Undefined exception: %s [%s]', $exception->getMessage(), $exception->getCode()),
                $exception->getTrace()
            );
        }
        $this->connection->rollbackTransaction();
        return false;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function support($data): bool
    {
        return \is_object($data) && $data instanceof Material;
    }

    /**
     * @param Material $material
     *
     * @throws \RuntimeException
     * @throws LoggedException
     * @return Brand
     */
    protected function getBrand(Material $material): Brand
    {
        /**
         * @todo internal storage for import
         */
        $brand = $this->brandRepository->findByXmlId($material->getBrandCode());
        if (!$brand) {
            $brand = (new Brand())
                ->withXmlId($material->getBrandCode())
                ->withName($material->getBrandName());
            $result = $this->brandRepository->create($brand);
            if (!$result->isSuccess()) {
                $this->log()->log(
                    LogLevel::ERROR,
                    implode(', ', $result->getErrorMessages()),
                    ['NAME' => $material->getBrandName(), 'XML_ID' => $material->getBrandCode()]
                );
                throw new LoggedException('Ошибка создания бренда');
            }
            $this->log()->log(
                LogLevel::DEBUG,
                sprintf('Бренд %s [%s] создан', $brand->getName(), $brand->getId())
            );
        }
        return $brand;
    }

    /**
     * @param Material $material
     *
     * @param Brand    $brand
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \Adv\Bitrixtools\Exception\IblockNotFoundException
     * @throws \RuntimeException
     * @throws LoggedException
     * @return Product
     */
    protected function getProduct(Material $material, Brand $brand): Product
    {
        $product = $this->productService->processMaterial($material);
        $product->withBrandId($brand->getId());

        if ($product->getId()) {
            $result = $this->productService->update($product);
        } else {
            $result = $this->productService->create($product);
        }

        if ($result->isSuccess()) {
            $this->log()->log(LogLevel::DEBUG, sprintf(
                'Элемент инфоблока [Продукт] %s [%s] %s',
                $product->getName(),
                $product->getId(),
                $result instanceof AddResult ? 'Создан' : 'Обновлен'
            ));
            return $product;
        }
        $message = sprintf(
            'Ошибка %s элемент инфоблока [Продукт] %s [%s]',
            $result instanceof AddResult ? 'Создания' : 'Обновления',
            $product->getName(),
            $product->getCode()
        );

        $this->log()->log(LogLevel::CRITICAL, $message, $result->getErrorMessages());
        throw new LoggedException($message);
    }

    /**
     * @param Material $material
     * @param Product  $product
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundDataManagerException
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     * @throws \FourPaws\SapBundle\Exception\CantCreateReferenceItem
     * @throws \FourPaws\SapBundle\Exception\NotFoundReferenceRepositoryException
     * @throws \FourPaws\SapBundle\Exception\LogicException
     * @throws \FourPaws\SapBundle\Exception\RuntimeException
     * @throws LoggedException
     * @return Offer
     */
    protected function getOffer(Material $material, Product $product): Offer
    {
        $offer = $this->offerService->processMaterial($material);
        $offer->withCml2Link($product->getId());

        if ($offer->getId()) {
            $result = $this->offerService->update($offer);
        } else {
            $result = $this->offerService->create($offer);
        }

        if ($result->isSuccess()) {
            $this->log()->log(LogLevel::DEBUG, sprintf(
                'Торговое предложение %s [%s] %s',
                $offer->getName(),
                $offer->getId(),
                $result instanceof AddResult ? 'Создано' : 'Обновлено'
            ));
            return $offer;
        }
        $message = sprintf(
            'Ошибка %s торгового предложения %s [%s]',
            $result instanceof AddResult ? 'Создания' : 'Обновления',
            $offer->getName(),
            $offer->getCode()
        );

        $this->log()->log(LogLevel::CRITICAL, $message, $result->getErrorMessages());
        throw new LoggedException($message);
    }

    /**
     * @param Material $material
     * @param Offer    $offer
     *
     * @throws \FourPaws\SapBundle\Exception\NotFoundBasicUomException
     * @throws \RuntimeException
     * @throws LoggedException
     * @return CatalogProduct
     */
    protected function getCatalogProduct(Material $material, Offer $offer): CatalogProduct
    {
        $catalogProduct = $this->catalogProductService->processMaterial($material);
        $catalogProduct->setId($offer->getId());
        $result = $this->catalogProductService->updateOrCreate($catalogProduct);
        if ($result) {
            $this->log()->log(LogLevel::DEBUG, sprintf(
                'Продуктовое предложение %s создано или обновлено',
                $catalogProduct->getId()
            ));
            return $catalogProduct;
        }
        $message = sprintf(
            'Ошибка создания или обновления продуктового предложения %s',
            $catalogProduct->getId()
        );
        $this->log()->log(LogLevel::CRITICAL, $message);
        throw new LoggedException($message);
    }
}
