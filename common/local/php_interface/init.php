<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Page\Asset;
use FourPaws\App\EventInitializer;
use FourPaws\IblockProps\ProductCategoriesProperty;
use FourPaws\IblockProps\OfferRegionDiscountsProperty;
use WebArch\BitrixIblockPropertyType\YesNoType;
use FourPaws\IblockProps\BlocksShowSwitcher;
use WebArch\BitrixNeverInclude\BitrixNeverInclude;
use FourPaws\LandingBundle\Service\ActionLanding;

require_once $_SERVER['DOCUMENT_ROOT'] . '/../vendor/autoload.php';

BitrixNeverInclude::registerModuleAutoload();

/**
 * Регистрируем события
 */
(new EventInitializer())(EventManager::getInstance());

/**
 * Инициализируем скрипты ядра, использующиеся для стандартных js-методов
 */
CUtil::InitJSCore(['core', 'popup', 'fx', 'ui']);

/**
 * Устанавливаем cookie из ENV - для того, чтобы отфильтровать
 */
$cookieEnv = explode(':', getenv('ADDITIONAL_COOKIE'));

if ($cookieEnv) {
    $cookieScript = <<<SCR
    <script data-skip-moving="true">
        window.configDefence = {
            cName: '{$cookieEnv[0]}',
            cValue: '{$cookieEnv[1]}'
        }
    </script>
SCR;

    Asset::getInstance()->addString($cookieScript);
}

/**
 * @todo HardCode
 *
 * Одна сессионная cookie на все поддомены
 */
$cookieDomain = $_SERVER['HTTP_HOST'];
if (mb_strpos($cookieDomain, '4lapy') === 0 || mb_strrpos($cookieDomain, 'stage') === 0) {
    $cookieDomain = '.' . $cookieDomain;
} else {
    $cookieDomain = mb_substr($cookieDomain, mb_strpos($cookieDomain, '.'));
}
ini_set('session.cookie_domain', $cookieDomain);

/**
 * Property initialize
 */
(new YesNoType())->init();
(new BlocksShowSwitcher())->init();
(new ProductCategoriesProperty())->init();
(new OfferRegionDiscountsProperty())->init();
/**
 * @todo впилить
 *
 * IblockSectionLinkType::init();
 * IblockElementLinkType::init();
 * HyperLinkType::init();
 */


AddEventHandler('socialservices', 'OnAuthServicesBuildList', array('CSocServHandlers', 'GetDescription'));

class CSocServHandlers
{
    public function GetDescription()
    {
        return [
            [
                'ID' => 'VK2',
                'CLASS' => 'FourPaws\SocServ\CSocServVK2',
                'NAME' => 'VK',
                'ICON' => 'vkontakte',
            ],
            [
                'ID' => 'FB2',
                'CLASS' => 'FourPaws\SocServ\CSocServFB2',
                'NAME' => 'FB',
                'ICON' => 'facebook',
            ],
            [
                'ID' => 'OK2',
                'CLASS' => 'FourPaws\SocServ\CSocServOK2',
                'NAME' => 'OK',
                'ICON' => 'odnoklassniki',
            ],
        ];
    }
}

AddEventHandler("main", "OnBeforeProlog", "authFromMobileApi", 50);

function authFromMobileApi()
{
    $apiAuthAction = new ActionLanding;
    $apiAuthAction->auth();
}
