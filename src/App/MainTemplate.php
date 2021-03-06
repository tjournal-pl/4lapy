<?php

namespace FourPaws\App;

use Bitrix\Main\Application as BitrixApplication;

/**
 * Class MainTemplate
 *
 * Класс для основного шаблона
 *
 * @package FourPaws\App
 */
class MainTemplate extends TemplateAbstract
{
    /**
     * @return string
     */
    public function getIndexMainClass(): string
    {
        return $this->isIndex() ? ' b-wrapper--main js-main-page-wrapper' : '';
    }

    /**
     * @return string
     */
    public function getWrapperClass(): string
    {
        $result = '';
        if ($this->isOrderPage()) {
            $result = ' b-page-wrapper--order ';
        } elseif ($this->isPaymentPage()) {
            $result = ' b-page-wrapper--payment ';
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getHeaderClass(): string
    {
        return $this->hasShortHeaderFooter() ? ' b-header--short ' : '';
    }

    /**
     * @return string
     */
    public function getFooterClass(): string
    {
        return $this->hasShortHeaderFooter() ? ' b-footer--short ' : '';
    }

    /**
     * @return bool
     */
    public function isIndex(): bool
    {
        return $this->isPage('/');
    }

    /**
     * Страница 404
     *
     * @return bool
     */
    public function is404(): bool
    {
        return \defined('ERROR_404') && ERROR_404 === 'Y';
    }

    /**
     * Страница, недоступная для неавторизованных
     *
     * @return bool
     */
    public function isForbidden(): bool
    {
        /**
         * It's bitrix way
         */
        global $USER;

        return \defined('NEED_AUTH') && NEED_AUTH === true && !$USER->IsAuthorized();
    }

    /**
     * @return bool
     */
    public function hasHeaderPublicationListContainer(): bool
    {
        return $this->isListNews() || $this->isListArticles() || $this->isListShares() || $this->isListSharesFilter();
    }

    /**
     * @return bool
     */
    public function hasShortHeaderFooter(): bool
    {
        return ($this->isOrderPage() && !$this->isOrderInterviewPage()) || $this->isPaymentPage();
    }

    /**
     * @return bool
     */
    public function isNews(): bool
    {
        return $this->isPartitionDir('/news');
    }

    /**
     * @return bool
     */
    public function isListNews(): bool
    {
        return $this->isDir('/news');
    }

    /**
     * @return bool
     */
    public function isArticles(): bool
    {
        return $this->isPartitionDir('/articles');
    }

    /**
     * @return bool
     */
    public function isListArticles(): bool
    {
        return $this->isDir('/articles');
    }

    /**
     * @return bool
     */
    public function isShares(): bool
    {
        return $this->isPartitionDir('/shares');
    }

    /**
     * @return bool
     */
    public function isPublications(): bool
    {
        return $this->isShares() || $this->isNews() || $this->isArticles();
    }

    /**
     * @return bool
     */
    public function isCatalog(): bool
    {
        return $this->isPartitionDir('/catalog');
    }

    /**
     * return bool
     */
    public function isCatalogDetail(): bool
    {
        return $this->isCatalog() && $this->isPartitionPage('.html');
    }

    /**
     * @return bool
     */
    public function isListShares(): bool
    {
        return $this->isDir('/shares');
    }

    /**
     * @return bool
     */
    public function isListSharesFilter(): bool
    {
        return $this->isPartitionDir('/shares') && strpos($this->getPath(), '.html') === false;
    }

    /**
     * @return bool
     */
    public function hasHeaderDetailPageContainer(): bool
    {
        return $this->isDetailNews() || $this->isDetailArticles() || $this->isDetailShares();
    }

    /**
     * @return bool
     */
    public function isDetailNews(): bool
    {
        return $this->isPartitionDir('/news');
    }

    /**
     * @return bool
     */
    public function isDetailArticles(): bool
    {
        return $this->isPartitionDir('/articles');
    }

    /**
     * @return bool
     */
    public function isDetailShares(): bool
    {
        return $this->isPartitionDir('/shares') && !$this->isListShares() && !$this->isListSharesFilter();
    }

    /**
     * @return bool
     */
    public function hasHeaderPersonalContainer(): bool
    {
        return ($this->isPersonalDirectory() || $this->isPersonal()) && !$this->isRegister() && !$this->isForgotPassword();
    }

    /**
     * @return bool
     */
    public function isPersonalDirectory(): bool
    {
        return $this->isPartitionDir('/personal');
    }

    /**
     * @return bool
     */
    public function isPersonal(): bool
    {
        return $this->isDir('/personal');
    }

    /**
     * @return bool
     */
    public function isRegister(): bool
    {
        return $this->isDir('/personal/register');
    }

    /**
     * @return bool
     */
    public function isAjaxRegister(): bool
    {
        return $this->isDir('/ajax/user/auth/register-r');
    }

    /**
     * @return bool
     */
    public function isForgotPassword(): bool
    {
        return $this->isDir('/personal/forgot-password');
    }

    /**
     * @return bool
     */
    public function hasPiggyBank(): bool
    {
        return $this->isPiggyBank();
    }

        /**
     * @return bool
     */
    public function isPiggyBank(): bool
    {
        return $this->isDir('/personal/kopi-marki');
    }

    /**
     * @return bool
     */
    public function hasPersonalOffers(): bool
    {
        return $this->isPersonalOffers();
    }

    /**
     * @return bool
     */
    public function isPersonalOffers(): bool
    {
        return $this->isDir('/personal/personal-offers');
    }

    /**
     * @return bool
     */
    public function hasHeaderBlockShopList(): bool
    {
        return $this->isShopList();
    }

    /**
     * @return bool
     */
    public function isShopList(): bool
    {
        return $this->isDir('/shops');
    }

    /**
     * @return bool
     */
    public function hasPersonalProfile(): bool
    {
        return $this->isPersonal();
    }

    /**
     * @return bool
     */
    public function hasPersonalAddress(): bool
    {
        return $this->isPersonalAddress();
    }

    /**
     * @return bool
     */
    public function isPersonalAddress(): bool
    {
        return $this->isDir('/personal/address');
    }

    /**
     * @return bool
     */
    public function hasPersonalPet(): bool
    {
        return $this->isPersonalPet();
    }

    /**
     * @return bool
     */
    public function isPersonalPet(): bool
    {
        return $this->isDir('/personal/pets');
    }

    /**
     * @return bool
     */
    public function hasPersonalReferral(): bool
    {
        return $this->isPersonalReferral();
    }

    /**
     * @return bool
     */
    public function isPersonalReferral(): bool
    {
        return $this->isDir('/personal/referral');
    }

    /**
     * @return bool
     */
    public function isOrderPage(): bool
    {
        return $this->isDir('/sale/order') || $this->isPartitionDir('/sale/order');
    }

    /**
     * @return bool
     */
    public function isOrderCompletePage(): bool
    {
        return $this->isDir('/sale/order/complete') || $this->isPartitionDir('/sale/order/complete');
    }

    /**
     * @return bool
     */
    public function isOrderInterviewPage(): bool
    {
        return $this->isPartitionDir('/sale/order/interview');
    }

    /**
     * @return bool
     */
    public function isPaymentPage(): bool
    {
        return $this->isDir('/sale/payment') || $this->isPartitionDir('/sale/payment');
    }

    /**
     * @return bool
     */
    public function isOrderDeliveryPage(): bool
    {
        return $this->isDir('/sale/order/delivery');
    }

    /**
     * @return bool
     */
    public function isOrderSubscribePage(): bool
    {
        return $this->isDir('/personal/subscribe');
    }

    /**
     * @return bool
     */
    public function isOrderHistoryPage(): bool
    {
        return $this->isDir('/personal/orders');
    }

    /**
     * @return bool
     */
    public function hasOrderDeliveryPage(): bool
    {
        return $this->isOrderDeliveryPage() || $this->isOrderSubscribePage() || $this->isOrderHistoryPage();
    }

    /**
     * Нет основного враппера
     *
     * @return bool
     */
    public function hasMainWrapper(): bool
    {
        return !$this->isForbidden() && !$this->is404();
    }

    /**
     * @return bool
     */
    public function isFeedback(): bool
    {
        return $this->isPage('/feedback');
    }

    /**
     * @return bool
     */
    public function isPaymentAndDelivery(): bool
    {
        return $this->isPage('/payment-and-delivery/');
    }

    /**
     * @return bool
     */
    public function hasContent(): bool
    {
        return !$this->isPersonal() && !$this->isIndex() && !$this->isOrderPage() && !$this->isPersonalDirectory()
            && !$this->isShopList()
            && !$this->isListShares()
            && !$this->is404()
            && !$this->isCatalog()
            && !$this->isPublications()
            && !$this->isPaymentAndDelivery()
            && !$this->isFeedback();
    }

    /**
     * @return bool
     */
    public function isBasket(): bool
    {
        return $this->isDir('/cart');
    }

    /**
     * @return bool
     */
    public function hasFastOrder(): bool
    {
        return $this->isCatalogDetail() || $this->isBasket();
    }

    /**
     * @return bool
     */
    public function hasUserAuth(): bool
    {
        return $this->isDir('/ajax/user/auth/login-o') || $this->isDir('/api/user_login') || $this->isPartitionDirByFilePath('/personal') || $this->isPartitionDirByFilePath('/sale');
    }

    /**
     * @return bool
     */
    public function isSearchPage(): bool
    {
        return $this->isDir('/catalog/search');
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\SystemException
     */
    public function isCatalogPopup(): bool
    {
        return $this->isPartitionDir('/ajax/catalog');
    }

    /**
     * @return bool
     */
    public function isDobrolap(): bool
    {
        return $this->isPartitionPage('/dobrolap/');
    }

    /**
     * @return bool
     */
    public function isFlagman(): bool
    {
        return $this->isPartitionPage('/events/');
    }

    public function isStampsPage() :bool
    {
        return $this->isDir('/personal/marki') || $this->isDir('/home');
    }

    /**
     * @return bool
     */
    public function isFrontOffice(): bool
    {
        $isFrontOffice = $this->isDir('/local/components/fourpaws/front_office.avatar');

        return $isFrontOffice;
    }
}
