<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\BitrixOrm\Model;

use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\FileTable;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Collection\HlbReferenceItemCollection;
use FourPaws\BitrixOrm\Utils\ReferenceUtils;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Query\OfferQuery;
use JMS\Serializer\Annotation\Type;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/**
 * Class Share
 *
 * @package FourPaws\BitrixOrm\Model
 */
class Share extends IblockElement
{

    /**
     * @var string
     * @JMS\Serializer\Annotation\Type("string")
     */
    protected $ACTIVE_FROM = '';

    /**
     * @var string
     * @JMS\Serializer\Annotation\Type("string")
     */
    protected $ACTIVE_TO = '';

    /**
     * @var int
     * @Type("string")
     */
    protected $PREVIEW_PICTURE;

    /**
     * @var int
     * @Type("string")
     */
    protected $DETAIL_PICTURE;

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_LABEL = '';

    /**
     * @var int
     * @Type("int")
     */
    protected $PROPERTY_LABEL_IMAGE = 0;

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_SHARE_TYPE = '';

    /**
     * @var HlbReferenceItemCollection
     */
    protected $shareType;

    /**
     * @var string[]
     * @Type("array")
     */
    protected $PROPERTY_TYPE = [];

    /**
     * @var HlbReferenceItemCollection
     */
    protected $type;

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_ONLY_MP = 'N';

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_SHORT_URL = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_OLD_URL = '';

    /**
     * @var string[]
     * @Type("array")
     */
    protected $PROPERTY_PRODUCTS = [];

    /**
     * @var string[]
     * @Type("array")
     */
    protected $PROPERTY_BASKET_RULES = [];

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_JSON_GROUP_SET = '';

    /**
     * @var string
     * @Type("string")
     */
    protected $PROPERTY_PREMISE_BONUS = false;

    /**
     * @var bool
     * @Type("bool")
     */
    protected $PROPERTY_SIGNCHARGE = false;

    /**
     * @var OfferCollection
     */
    protected $products;

    /**
     * @return string
     */
    public function getPropertyLabel(): string
    {
        return trim((string)$this->PROPERTY_LABEL);
    }

    /**
     * @return bool
     */
    public function hasLabel(): bool
    {
        return !empty($this->getPropertyLabel());
    }

    /**
     * @param string $propertyLabel
     *
     * @return Share
     */
    public function withPropertyLabel(string $propertyLabel): Share
    {
        $this->PROPERTY_LABEL = $propertyLabel;

        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyShareType(): string
    {
        return $this->PROPERTY_SHARE_TYPE;
    }

    /**
     * @param string $propertyShareType
     *
     * @return Share
     */
    public function withPropertyShareType(string $propertyShareType): Share
    {
        $this->PROPERTY_SHARE_TYPE = $propertyShareType;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @return HlbReferenceItem
     */
    public function getShareType(): HlbReferenceItem
    {
        if (null === $this->shareType) {
            $this->shareType = ReferenceUtils::getReference(
                Application::getHlBlockDataManager('bx.hlblock.sharetype'),
                $this->getPropertyShareType()
            );
        }
        return $this->shareType;
    }

    /**
     * @return string[]
     */
    public function getPropertyType(): array
    {
        return $this->PROPERTY_TYPE;
    }

    /**
     * @param string[] $propertyType
     *
     * @return Share
     */
    public function withPropertyType(array $propertyType): Share
    {
        $this->PROPERTY_TYPE = $propertyType;

        return $this;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws \RuntimeException
     * @throws ApplicationCreateException
     * @return HlbReferenceItemCollection
     */
    public function getType(): HlbReferenceItemCollection
    {
        if (null === $this->type) {
            $this->type = ReferenceUtils::getReferenceMulti(
                Application::getHlBlockDataManager('bx.hlblock.publicationtype'),
                $this->getPropertyType()
            );
        }
        return $this->type;
    }

    /**
     * @return string
     */
    public function getPropertyOnlyMp(): string
    {
        return $this->PROPERTY_ONLY_MP;
    }

    /**
     * @param string $propertyOnlyMp
     *
     * @return Share
     */
    public function withPropertyOnlyMp(string $propertyOnlyMp): Share
    {
        $this->PROPERTY_ONLY_MP = $propertyOnlyMp;

        return $this;
    }

    /**
     * @return bool
     */
    public function isOnlyMobile(): bool
    {
        return $this->getPropertyOnlyMp() === 'Y';
    }

    /**
     * @return string
     */
    public function getPropertyShortUrl(): string
    {
        return $this->PROPERTY_SHORT_URL;
    }

    /**
     * @param string $propertyShortUrl
     *
     * @return Share
     */
    public function withPropertyShortUrl(string $propertyShortUrl): Share
    {
        $this->PROPERTY_SHORT_URL = $propertyShortUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyOldUrl(): string
    {
        return $this->PROPERTY_OLD_URL;
    }

    /**
     * @param string $propertyOldUrl
     *
     * @return Share
     */
    public function withPropertyOldUrl(string $propertyOldUrl): Share
    {
        $this->PROPERTY_OLD_URL = $propertyOldUrl;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getPropertyProducts(): array
    {
        $this->PROPERTY_PRODUCTS = \is_array($this->PROPERTY_PRODUCTS) ? $this->PROPERTY_PRODUCTS : [];

        return $this->PROPERTY_PRODUCTS;
    }

    /**
     * @param string[] $propertyProducts
     *
     * @return Share
     */
    public function withPropertyProducts(array $propertyProducts): Share
    {
        $this->PROPERTY_PRODUCTS = $propertyProducts;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getProducts(): Collection
    {
        if ((null === $this->products) && $this->getPropertyProducts()) {
            $this->products = (new OfferQuery())->withFilter(['=XML_ID' => $this->getPropertyProducts()])->exec();
        } else {
            $this->products = new ArrayCollection();
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->products;
    }

    /**
     * @return string[]
     */
    public function getPropertyBasketRules(): array
    {
        return $this->PROPERTY_BASKET_RULES;
    }

    /**
     * @param string[] $propertyBasketRules
     *
     * @return Share
     */
    public function withPropertyBasketRules(array $propertyBasketRules): Share
    {
        $this->PROPERTY_BASKET_RULES = $propertyBasketRules;

        return $this;
    }

    /**
     * @param DateTimeImmutable $dateActiveFrom
     *
     * @return $this
     */
    public function withDateActiveFrom(DateTimeImmutable $dateActiveFrom)
    {
        parent::withDateActiveFrom($dateActiveFrom);
        $this->ACTIVE_FROM = $this->DATE_ACTIVE_FROM;

        return $this;
    }

    /**
     * @param DateTimeImmutable $dateActiveTo
     *
     * @return $this
     */
    public function withDateActiveTo(DateTimeImmutable $dateActiveTo)
    {
        parent::withDateActiveTo($dateActiveTo);
        $this->ACTIVE_TO = $this->DATE_ACTIVE_TO;

        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyJsonGroupSet(): string
    {
        return $this->PROPERTY_JSON_GROUP_SET;
    }

    /**
     * @param string $propertyJsonGroupSet
     *
     * @return Share
     */
    public function withPropertyJsonGroupSet(string $propertyJsonGroupSet): Share
    {
        $this->PROPERTY_JSON_GROUP_SET = $propertyJsonGroupSet;
        return $this;
    }

    /**
     * @return int
     */
    public function getPropertyLabelImage(): int
    {
        return $this->PROPERTY_LABEL_IMAGE ?? 0;
    }

    /**
     * @return bool
     */
    public function hasLabelImage(): bool
    {
        return $this->getPropertyLabelImage() > 0;
    }

    /**
     * @param int $image
     */
    public function setPropertyLabelImage(int $image): void
    {
        $this->PROPERTY_LABEL_IMAGE = $image;
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setPropertyPremiseBonus(string $value): Share
    {
        $this->PROPERTY_PREMISE_BONUS = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getPropertyPremiseBonus(): string
    {
        return $this->PROPERTY_PREMISE_BONUS;
    }

    /**
     * @param bool|null $value
     *
     * @return $this
     */
    public function setPropertySigncharge(?bool $value): Share
    {
        $this->PROPERTY_SIGNCHARGE = $value;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getPropertySigncharge(): ?bool
    {
        return $this->PROPERTY_SIGNCHARGE;
    }

    /**
     * @return bool
     */
    public function isBonus(): bool
    {
        //почему-то туда пишется строка 0/1, а не N/Y как предполагается в ONLY_MP
        //отдельный кек - only mobilnoe prilojenie
        return (bool)$this->getPropertyPremiseBonus();
    }

    public function getPropertyLabelImageFile($width = 0, $height = 0)
    {
        $file = $this->getPropertyLabelImage();
        if ($file > 0) {
            $resize = false;
            if ($width > 0 || $height > 0) {
                $resize = true;
            }
            $query = FileTable::query();
            $query->setSelect(['ID', 'WIDTH', 'HEIGHT', 'CONTENT_TYPE', 'SRC']);
            $file = $query->where('ID', $file)
                ->registerRuntimeField(new ExpressionField('SRC', 'concat("/upload/",%s,"/",%s)', ['SUBDIR', 'FILE_NAME']))
                ->exec()->fetch();
            if ($resize && !\in_array($file['CONTENT_TYPE'], ['image/svg+xml', 'text/xml'], true)) {
                $resizeFile = ResizeImageDecorator::createFromPrimary($file['ID']);
                if($height > 0) {
                    $resizeFile->setResizeHeight($height);
                }
                if($width > 0) {
                    $resizeFile->setResizeWidth($width);
                }
                $file = [
                    'ID'     => $file['ID'],
                    'SRC'    => $resizeFile->getSrc(),
                    'WIDTH'  => $resizeFile->getResizeWidth(),
                    'HEIGHT' => $resizeFile->getResizeHeight(),
                ];
            }
            return $file;
        }
        return [];
    }

    public function getPropertyLabelImageFileSrc($width = 0, $height = 0)
    {
        return $this->getPropertyLabelImageFile($width, $height)['SRC'];
    }

    /**
     * @return string
     */
    public function getPreviewPictureSrc(): string
    {
        return \CFile::getPath($this->PREVIEW_PICTURE);
    }

    /**
     * @return string
     */
    public function getDetailPictureSrc(): string
    {
        return \CFile::getPath($this->DETAIL_PICTURE);
    }
}
