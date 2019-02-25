<?php

namespace FourPaws\PersonalBundle\Service;

use Adv\Bitrixtools\Tools\Log\LoggerFactory;
use Bitrix\Highloadblock\DataManager;
use Bitrix\Main\SystemException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application as App;
use FourPaws\PersonalBundle\Exception\CouponIsAlreadyMaxedException;
use FourPaws\PersonalBundle\Exception\CouponNoFreeItemsException;
use FourPaws\PersonalBundle\Exception\NoActiveUserCouponException;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class PiggyBankService
 *
 * @package FourPaws\SaleBundle\Service
 */
class PiggyBankService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    protected const MARKS = [
        'VIRTUAL' => [
            'ID' => 89617, // (род.товар 89616, внешний код ТП 2000341)
        ],
        'PHYSICAL' => [
            'ID' => 89728, // (род.товар 89727, внешний код ТП 3006077)
        ],
    ];
    public const MARK_RATE = 400;
    public const MARKS_PER_RATE = 1;
    public const MARKS_PER_RATE_VETAPTEKA = 2;
    public const PROMO_TITLE_MASK = 'Собиралка 03-04.19%';

    public const COUPON_LEVELS = [
        1 => [
            'LEVEL' => 1,
            'MARKS_TO_LEVEL_UP' => 7,
            'MARKS_TO_LEVEL_UP_FROM_BOTTOM' => 7,
            'DISCOUNT' => 10,
            'SALE_TYPE' => 'small',
        ],
        2 => [
            'LEVEL' => 2,
            'MARKS_TO_LEVEL_UP' => 8,
            'MARKS_TO_LEVEL_UP_FROM_BOTTOM' => 15,
            'DISCOUNT' => 20,
            'SALE_TYPE' => 'middle',
        ],
        3 => [
            'LEVEL' => 3,
            'MARKS_TO_LEVEL_UP' => 10,
            'MARKS_TO_LEVEL_UP_FROM_BOTTOM' => 25,
            'DISCOUNT' => 30,
            'SALE_TYPE' => 'large',
        ],
    ];

    /** @var array */
    private $marksIds;
    /** @var int */
    private $physicalMarkId;
    /** @var int */
    private $virtualMarkId;
    /** @var ArrayCollection */
    public $levelsByDiscount;
    /** @var ArrayCollection */
    public $activeCoupon; //TODO getters, setters (and use them instead of direct calls). Move to coupon(?) service. Set private
	/** @var int */
    private $userId;
    /** @var int */
    private $activeMarksQuantity;

    /** @var BasketService */
    protected $basketService;
    /** @var CurrentUserProviderInterface */
    protected $currentUserProvider;

	/** @var DataManager */
	protected $couponDataManager;

    /**
     * PiggyBankService constructor.
     */
    public function __construct()
    {
        $this->setLogger(LoggerFactory::create('PiggyBankService'));

        $container = App::getInstance()->getContainer();
        $this->basketService = $container->get(BasketService::class);
        $this->couponDataManager = $container->get('bx.hlblock.coupon');
        $this->currentUserProvider = $container->get(CurrentUserProviderInterface::class);
    }

	/**
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function isEnoughMarksForFirstCoupon(): bool
    {
        return $this->getAvailableMarksQuantity() >= self::COUPON_LEVELS[1]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'];
    }

	/**
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function isUserHasActiveCoupon(): bool
    {
        $couponsCount = $this->couponDataManager::getCount([
            'UF_USER_ID'     => $this->getCurrentUserId(),
            'UF_PROMO'       => self::PROMO_TITLE_MASK,
            'UF_AVAILABLE'   => false,
            'UF_DEACTIVATED' => false,
            'UF_USED'        => false,
        ]);

        return (bool)$couponsCount;
    }

	/**
	 * @todo Что, если свободный купон успеют занять прежде, чем функция закончит выполнение?
	 * @todo Обработать ситуацию, если linkCouponToCurrentUser вернет false
	 *
	 * @return void
	 * @throws CouponNoFreeItemsException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \FourPaws\PersonalBundle\Exception\CouponNotLinkedException
	 */
    public function addFirstLevelCouponToUser(): void
    {
        global $USER;
        if (!$USER->IsAdmin())
        {
            die();
        }

        /** @var CouponService $couponService */
    	$couponService = App::getInstance()->getContainer()->get('coupon.service');

        $freeCouponId = $this->getFreeCouponId(1);
	    $couponService->linkCouponToCurrentUser($freeCouponId); //TODO unlock coupon in case of exceptions (inside)
    }

	/**
	 * @todo обработать CouponNoFreeItems (закончились свободные купоны)
	 * @param int $level
	 * @return int
	 * @throws CouponNoFreeItemsException
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	public function getFreeCouponId(int $level): int
    {
        global $USER;
        if (!$USER->IsAdmin())
        {
            die();
        }

        //TODO lock coupon until update
        $coupon = $this->couponDataManager::query()
            ->setSelect([
                'ID',
            ])
            ->setFilter([
                'UF_USER_ID'     => false,
                'UF_PROMO'       => self::PROMO_TITLE_MASK,
                'UF_AVAILABLE'   => true,
                'UF_DEACTIVATED' => false,
                'UF_USED'        => false,
                'UF_DISCOUNT'    => self::COUPON_LEVELS[$level]['DISCOUNT'],
            ])
            ->setOrder([
                'ID' => 'ASC',
            ])
            ->setLimit(1)
            ->exec()
            ->fetch();

        if (!$coupon)
        {
        	throw new CouponNoFreeItemsException(sprintf(
                'No free coupons available of level %s',
                $level
	        ));
        }

        return $coupon['ID'];
    }

	/**
     * @return void
	 */
	public function upgradeCoupon(): void
    {
        global $USER;
        if (!$USER->IsAdmin())
        {
            die();
        }

		try {
			$currentCoupon = $this->getActiveCoupon();

			if (!$currentCoupon)
			{
				throw new NoActiveUserCouponException('User has no active coupon');
			}
			if ($currentCoupon['LEVEL'] >= max(array_keys(self::COUPON_LEVELS)))
			{
                throw new CouponIsAlreadyMaxedException('User already has max level coupon');
			}

	        /** @var CouponService $couponService */
	        $couponService = App::getInstance()->getContainer()->get('coupon.service');

	        $freeCouponId = $this->getFreeCouponId($currentCoupon['LEVEL'] + 1);
	        $couponService->linkCouponToCurrentUser($freeCouponId); //TODO unlock coupon in case of exceptions (inside)
			//TODO транзакция (если не получен новый купон, то не гасить старый)

            $couponService->deactivateCoupon($currentCoupon['ID']); //FIXME what to do if can't be deactivated?

            $currentCoupon = $this->getActiveCoupon(true);
        } catch (\Exception $e) {
            $this->log()->critical(\sprintf(
                'Not possible to upgrade coupon: %s: %s',
                \get_class($e),
                $e->getMessage()
            ));
        }
	}

	/**
	 * @fixme Отрабатывает два раза, должен один
	 *
	 * @return int
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function getAvailableMarksQuantity(): int
    {
		$availableMarksQuantity = $this->getAllMarksQuantity() - $this->getUsedMarksQuantity(); //TODO move function

		if ($availableMarksQuantity < 0)
		{
			//TODO Exception / Пользователем получено на ' . abs($availableMarksQuantity) . ' марок меньше, чем потрачено
			// TODO обработчик аномальной ситуации, если общее количество полученных марок меньше, чем количество потраченных марок
		}

        return $availableMarksQuantity;
    }

    /**
     * @return int
     * @todo исключить дубли, создаваемые при импорте из Manzana
     */
    public function getAllMarksQuantity(): int
    {

        /**
         * @TODO уточнить, подойдет ли использование FUSER_ID
         * @TODO del comment
         * - Получить все корзины, привязанные к заказам, в которых есть марки
         * - Просуммировать количество марок в этих корзинах
         *
         * Другой вариант (плохой):
         * - Получить все заказы пользователя
         * - Получить все товары в этих заказах
         * - Получить
         */

    	//OrderService::getUserOrders()
    	//OrderService::getOrderItems()

	    //$currentFUserId = $this->orderStorageService->getStorage()->getFuserId();

		$marksQuantity = $this->basketService->getMarksQuantityFromUserBaskets();

        return $marksQuantity;
    }

    /**
	 * @return int
	 *
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
    public function getUsedMarksQuantity(): int
    {
        $allUserCoupons = $this->couponDataManager::query()
	        ->setSelect(['UF_DISCOUNT'])
	        ->setFilter([
                'UF_PROMO'       => self::PROMO_TITLE_MASK,
                'UF_AVAILABLE'   => false,
                'UF_DEACTIVATED' => false,
                'UF_USER_ID'     => $this->getCurrentUserId(),
	        ])
	        ->exec()
	        ->fetchAll();

        $usedMarksQuantity = array_reduce(array_column($allUserCoupons, 'UF_DISCOUNT'), function($carry, $discountValue) {
        	$carry += self::COUPON_LEVELS[$this->getLevelByDiscountValue($discountValue)]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'];
            return $carry;
        }, 0);

        return $usedMarksQuantity;
    }

    /**
	 * @param int $discountValue
	 * @return int
	 */
	public function getLevelByDiscountValue(int $discountValue): int
    {
    	if (!$this->levelsByDiscount)
	    {
            $levelsByDiscount = [];
            foreach (self::COUPON_LEVELS as $level => $levelInfo)
            {
                $levelsByDiscount[$levelInfo['DISCOUNT']] = $level;
		    }

            $this->levelsByDiscount = new ArrayCollection($levelsByDiscount);
	    }

    	return $this->levelsByDiscount[$discountValue];
    }

	/**
	 * @todo получать купон из БД
	 * @todo вынести основную логику в CouponService, добавив фильтр по UF_PROMO
	 *
	 * @param bool $refresh
	 * @return ArrayCollection
	 *
	 */
    public function getActiveCoupon(bool $refresh = false): ArrayCollection //TODO возвращать Coupon (сделать Entity)
    {
        global $USER;
        if (!$USER->IsAdmin())
        {
            die();
        }

    	if (!$refresh && $this->activeCoupon)
	    {
	    	return $this->activeCoupon;
	    }

    	try {
	        $coupon = $this->couponDataManager::query()
	            ->setSelect([
	            		'ID',
	            		'UF_COUPON',
	            		'UF_DISCOUNT',
	                ])
	            ->setFilter([
	                'UF_USER_ID'     => $this->getCurrentUserId(),
	                'UF_PROMO'       => self::PROMO_TITLE_MASK,
	                'UF_AVAILABLE'   => false,
	                'UF_DEACTIVATED' => false,
	                'UF_USED'        => false,
	            ])
		        ->setOrder([
	                'UF_DISCOUNT' => 'DESC',
	                'UF_COUPON' => 'ASC',
		        ])
		        ->setLimit(1) // одновременно несколько купонов по акции "Копилка-собиралка" быть не может, но на всякий случай берется купон с наибольшей скидкой
	            ->exec()
	            ->fetch();

	        if ($coupon)
	        {
	            $coupon = [
                    'ID' => $coupon['ID'],
                    'LEVEL' => $this->getLevelByDiscountValue($coupon['UF_DISCOUNT']),
	                'COUPON_NUMBER' => $coupon['UF_COUPON'],
	                'DISCOUNT' => $coupon['UF_DISCOUNT'],
	            ];
	        }
	        else
	        {
                $coupon = [];
	        }

	    } catch (\Exception $e) {
    		//TODO log errors
            $coupon = [];
	    }

        $this->activeCoupon = new ArrayCollection($coupon);

        if (empty($coupon) && $this->getActiveMarksQuantity(true) >= self::COUPON_LEVELS[1]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'])
        {
        	//TODO обработать: аномальная ситуация, 15 марок уже есть, а первый купон еще не получен (т.е. не сработал обработчик, автоматически дающий купон 1-го уровня)
        }

        return $this->activeCoupon;
    }

    	/**
	 * @param bool $withoutCoupons
	 * @return int
	 */
    public function getActiveMarksQuantity(bool $withoutCoupons = false): int
    {
        if ($this->activeMarksQuantity)
        {
            return $this->activeMarksQuantity;
        }

        $activeMarks = 0;

        if (!$this->getActiveCoupon()->isEmpty())
        {
            $activeMarks += self::COUPON_LEVELS[$this->getActiveCoupon()['LEVEL']]['MARKS_TO_LEVEL_UP_FROM_BOTTOM'];
        }

        $activeMarks += $this->getAvailableMarksQuantity();

        $this->activeMarksQuantity = $activeMarks;

        return $this->activeMarksQuantity;
    }

    /**
     * @return array
     */
    public function getMarksIds(): array
    {
        if ($this->marksIds)
        {
            return $this->marksIds;
        }

        $marksIds = array_map(function($mark) { return $mark['ID']; }, self::MARKS);

        $this->marksIds = $marksIds;
        return $this->marksIds;
    }

    /**
     * @return int
     */
    public function getPhysicalMarkId(): int
    {
        if ($this->physicalMarkId)
        {
            return $this->physicalMarkId;
        }

        $physicalMarkId = self::MARKS['PHYSICAL']['ID'];
        $this->physicalMarkId = $physicalMarkId;

        return $this->physicalMarkId;
    }

    /**
     * @return int
     */
    public function getVirtualMarkId(): int
    {
        if ($this->virtualMarkId)
        {
            return $this->virtualMarkId;
        }

        $virtualMarkId = self::MARKS['VIRTUAL']['ID'];
        $this->virtualMarkId = $virtualMarkId;

        return $this->virtualMarkId;
    }

	/**
	 * @return int
	 */
	private function getCurrentUserId(): int
    {
    	if ($this->userId)
	    {
	    	return $this->userId;
	    }

        $this->userId = $this->currentUserProvider->getCurrentUserId();
    	return $this->userId;
    }

	/**
	 * @return LoggerInterface
	 */
	protected function log(): LoggerInterface
	{
	    return $this->logger;
	}
}