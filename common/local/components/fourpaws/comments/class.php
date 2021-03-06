<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

if (!\defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Highloadblock\DataManager;
use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\Date;
use FourPaws\App\Application as App;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\AppBundle\Exception\CaptchaErrorException;
use FourPaws\AppBundle\Exception\EmptyUserDataComments;
use FourPaws\AppBundle\Exception\ErrorAddComment;
use FourPaws\AppBundle\Exception\UserNotFoundAddCommentException;
use FourPaws\FormBundle\Exception\FileCountException;
use FourPaws\FormBundle\Exception\FileSaveException;
use FourPaws\FormBundle\Exception\FileSizeException;
use FourPaws\FormBundle\Exception\FileTypeException;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\Helpers\TaggedCacheHelper;
use FourPaws\KioskBundle\Service\KioskService;
use FourPaws\ReCaptchaBundle\Service\ReCaptchaInterface;
use FourPaws\UserBundle\Exception\WrongEmailException;
use FourPaws\UserBundle\Exception\WrongPasswordException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserAuthorizationInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */

class CCommentsComponent extends \CBitrixComponent
{
    /**
     * @var UserAuthorizationInterface $userService
     */
    public $userAuthService;
    /**
     * @var DataManager $hlEntity
     */
    private $hlEntity;
    /**
     * @var CurrentUserProviderInterface $userService
     */
    private $userCurrentUserService;

    /**
     * @param bool $addNotAuth
     *
     * @return bool
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws CaptchaErrorException
     * @throws EmptyUserDataComments
     * @throws ErrorAddComment
     * @throws FileCountException
     * @throws FileSaveException
     * @throws FileSizeException
     * @throws FileTypeException
     * @throws LoaderException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws UserNotFoundAddCommentException
     * @throws WrongEmailException
     * @throws WrongPasswordException
     * @throws WrongPhoneNumberException
     */
    public static function addComment(bool $addNotAuth = false): bool
    {
        $class = new static();
        $class->setUserBundle();
        $class->arResult['AUTH'] = $class->userAuthService->isAuthorized();
        if (!$class->arResult['AUTH']) {
            $recaptchaService = App::getInstance()->getContainer()->get(ReCaptchaInterface::class);
            if (!$recaptchaService->checkCaptcha() && !KioskService::isKioskMode()) {
                throw new CaptchaErrorException('Капча не валидна');
            }
        }
        $data = $class->getData($addNotAuth);
        $class->arParams['HL_ID'] = $data['HL_ID'];
        $class->arParams['OBJECT_ID'] = $data['UF_OBJECT_ID'];
        unset($data['HL_ID']);

        $class->setHLEntity();
        if (!empty($data)) {
            $res = $class->hlEntity::add($data);
            if ($res->isSuccess()) {
                return true;
            }
        }

        throw new ErrorAddComment(
            'Произошла ошибка при добавлении комментария ' . implode('<br/>', $res->getErrorMessages())
        );
    }

    /**
     * @param int $hlID
     *
     * @return mixed
     *@throws ObjectPropertyException
     * @throws \Exception
     * @throws LogicException
     * @throws LoaderException
     * @throws SystemException
     * @throws RuntimeException
     * @throws ArgumentException
     */
    public static function getHLEntity(int $hlID)
    {
        /** @todo Расширить Adv\Bitrixtools\Tools\HLBlock\HLBlockFactory методом createTableObjectById */
        Loader::includeModule('highloadblock');

        $result = HighloadBlockTable::query()->setSelect(['*'])->setFilter(['ID' => $hlID])->exec();

        if ($result->getSelectedRowsCount() > 1) {
            throw new LogicException('Неверный фильтр: найдено несколько HLBlock.');
        }

        $hlBlockFields = $result->fetch();

        if (!\is_array($hlBlockFields)) {
            throw new RuntimeException('HLBlock не найден.');
        }

        $dataManager = HighloadBlockTable::compileEntity($hlBlockFields)->getDataClass();

        if (\is_string($dataManager)) {
            return new $dataManager;
        }

        if (\is_object($dataManager)) {
            return $dataManager;
        }

        throw new RuntimeException('Ошибка компиляции сущности для HLBlock.');
    }

    /**
     * @return array
     * @throws ServiceNotFoundException
     * @throws SystemException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws LoaderException*
     * @throws ApplicationCreateException
     * @throws LogicException
     */
    public static function getNextItems(): array
    {
        $class = new static();
        $class->setUserBundle();
        $request = Application::getInstance()->getContext()->getRequest();
        $class->arParams['HL_ID'] = $request->get('hl_id');
        $class->arParams['OBJECT_ID'] = $request->get('object_id');
        $class->arParams['TYPE'] = $request->get('type');
        $class->arParams['ITEMS_COUNT'] = $request->get('items_count');
        $class->arParams['PAGE'] = $request->get('page');
        $class->arParams['SORT_DESC'] = $request->get('sort_desc');
        $class->arParams['ACTIVE_DATE_FORMAT'] = $request->get('active_date_format') ?? 'd.m.Y';
        $class->setHLEntity();
        $items = $class->getComments();

        // формируем массив, чтобы на фронте можно было отрендерить изображения к комментариям
        foreach ($items['ITEMS'] as &$comment) {
            if ($comment['UF_PHOTOS'] && !empty($comment['UF_PHOTOS'])) {
                $photos = [];
                foreach ($comment['UF_PHOTOS'] as $photoId) {
                    if (isset($items['IMAGES'][$photoId])) {
                        $photos[] = [
                          'ID' => $photoId,
                          'URL' =>   $items['IMAGES'][$photoId],
                        ];
                    }
                }
                $comment['UF_PHOTOS'] = $photos;
            } else {
                $comment['UF_PHOTOS'] = false;
            }
        }

        return $items['ITEMS'];
    }

    /**
     * {@inheritdoc}
     */
    public function onPrepareComponentParams($params): array
    {
        $params['HL_ID'] = (int)$params['HL_ID'];
        $params['OBJECT_ID'] = (int)$params['OBJECT_ID'];
        $params['SORT_DESC'] = !empty($params['SORT_DESC']) ? $params['SORT_DESC'] : 'Y';
        $params['ITEMS_COUNT'] = (int)$params['ITEMS_COUNT'] <= 0 ? (int)$params['ITEMS_COUNT'] : 5;
        $params['ACTIVE_DATE_FORMAT'] = trim($params['ACTIVE_DATE_FORMAT']);
        $params['ACTIVE_DATE_FORMAT'] =
            \strlen($params['ACTIVE_DATE_FORMAT']) <= 0 ? $params['ACTIVE_DATE_FORMAT'] : Date::getFormat();
        if (empty($params['TYPE'])) {
            $params['TYPE'] = 'iblock';
        }

        $params['CACHE_TYPE'] = $params['CACHE_TYPE'] ?: 'A';
        $params['CACHE_TIME'] = $params['CACHE_TIME'] ?: 360000;

        return $params;
    }

    /**
     * {@inheritdoc}
     * @throws SystemException
     * @throws ServiceNotFoundException
     * @throws RuntimeException
     * @throws LogicException
     * @throws Exception
     */
    public function executeComponent()
    {
        $this->setFrameMode(true);
        if ($this->arParams['HL_ID'] === 0) {
            ShowError('Не выбран HL блок комментариев');

            return false;
        }
        if ($this->arParams['OBJECT_ID'] === 0) {
            ShowError('Не выбран объект комментирования');

            return false;
        }

        $this->arResult['AUTH'] = false;
        try {
            $this->setUserBundle();
            $this->arResult['AUTH'] = $this->userAuthService->isAuthorized();
        } catch (ApplicationCreateException $e) {
            ShowError($e->getMessage());

            return false;
        } catch (ServiceCircularReferenceException $e) {
            ShowError($e->getMessage());

            return false;
        }

        $cachePath = $this->getCachePath() ?: $this->getPath();
        if ($this->startResultCache($this->arParams['CACHE_TIME'], false, $cachePath)) {
            $tagCache = new TaggedCacheHelper($cachePath);
            $tagCache->addTags([
                'hlb:field:comments_objectId:' . $this->arParams['OBJECT_ID'],
            ]);

            try {
                $this->setHLEntity();
            } catch (LoaderException|SystemException $e) {
                $this->abortResultCache();
                $tagCache->abortTagCache();
                ShowError($e->getMessage());

                return false;
            }

            try {
                $comments = $this->getComments();
                $this->arResult['COMMENTS'] = $comments['ITEMS'];
                $this->arResult['COUNT_COMMENTS'] = $comments['COUNT'];
                $this->arResult['COMMENT_IMAGES'] = $comments['IMAGES'];
            } catch (ArgumentException $e) {
                $this->abortResultCache();
                $tagCache->abortTagCache();
                ShowError($e->getMessage());

                return false;
            }
            $this->arResult['RATING'] = $this->getRating();

            $this->setResultCacheKeys(['AUTH']);

            $this->includeComponentTemplate();
        }

        return true;
    }

    /**
     * @param bool $addNotAuth
     *
     * @return array
     * @throws ArgumentException
     * @throws EmptyUserDataComments
     * @throws FileCountException
     * @throws FileSaveException
     * @throws FileSizeException
     * @throws FileTypeException
     * @throws ObjectException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws UserNotFoundAddCommentException
     * @throws WrongEmailException
     * @throws WrongPasswordException
     * @throws WrongPhoneNumberException
     */
    public function getData(bool $addNotAuth = false): array
    {
        $data = Application::getInstance()->getContext()->getRequest()->getPostList()->toArray();

        unset($data['action'], $data['g-recaptcha-response']);
        if ($this->arResult['AUTH']) {
            $data['UF_USER_ID'] = $this->userCurrentUserService->getCurrentUserId();
            $data['UF_PHOTOS'] = $this->getPhotoData();
        } else {
            if (!$addNotAuth || ((!empty($data['EMAIL']) || !empty($data['PHONE'])) && !empty($data['PASSWORD']))) {
                $userRepository = $this->userCurrentUserService->getUserRepository();
                $filter = [
                    'LOGIC' => 'OR',
                ];
                if (!empty($data['EMAIL'])) {
                    if (filter_var($data['EMAIL'], FILTER_VALIDATE_EMAIL) === false) {
                        throw new WrongEmailException(
                            'Введен некорректный email'
                        );
                    }
                    $filter[] = [
                        '=EMAIL' => $data['EMAIL'],
                    ];
                }
                if (!empty($data['PHONE']) && PhoneHelper::isPhone($data['PHONE'])) {
                    $filter[] = [
                        '=PERSONAL_PHONE' => PhoneHelper::normalizePhone($data['PHONE']),
                    ];
                }
                if (count($filter) > 1) {
                    $users = $userRepository->findBy($filter);
                    if (\is_array($users) && !empty($users)) {
                        foreach ($users as $user) {
                            if ($user->equalPassword($data['PASSWORD'])) {
                                $data['UF_USER_ID'] = $user->getId();
                                break;
                            }
                        }
                    } else {
                        throw new UserNotFoundAddCommentException(
                            'Пользователь не найден, либо данные введены неверно'
                        );
                    }
                    if (empty($data['UF_USER_ID'])) {
                        /** разрешено добавлять анонимно - включается флагов в параметрах метода */
                        throw new WrongPasswordException(
                            'Неверный пароль'
                        );
                    }
                } else {
                    throw new EmptyUserDataComments('Телефон или email обязательны');
                }
            }
        }
        unset($data['PHONE'], $data['EMAIL'], $data['PASSWORD']);
        $data['UF_ACTIVE'] = 0;
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $data['UF_DATE'] = new Date();

        return $data;
    }

    /**
     * @return array|null
     * @throws FileCountException
     * @throws FileSaveException
     * @throws FileSizeException
     * @throws FileTypeException
     * @throws SystemException
     */
    public function getPhotoData(): ?array
    {
        $fileList = Application::getInstance()->getContext()->getRequest()->getFileList()->toArray();

        if (!isset($fileList['UF_PHOTOS']) || empty($fileList['UF_PHOTOS'])) {
            return null;
        }

        $dataFiles = [];

        $fileList = $fileList['UF_PHOTOS'];

        $filesCount = count($fileList['name']);

        if (count($fileList['name']) > 6) { // на самом деле 5, но с фронта приходит незаполненное поле
            throw new FileCountException('');
        }

        for ($i = 0; $i < $filesCount; $i++) {
            $file = [
                'name' => $fileList['name'][$i],
                'type' => $fileList['type'][$i],
                'tmp_name' => $fileList['tmp_name'][$i],
                'error' => $fileList['error'][$i],
                'size' => $fileList['size'][$i],
            ];

            if ($file['error'] === 4) {
                continue; // на фронте всегда остается одно незаполненное поле
            }

            if ($file['error'] !== 0) {
                throw new FileSaveException('');
            }

            if ($file['size'] > (5 * 1024 * 1024)) {
                throw new FileSizeException('');
            }

            if (!\in_array($file['type'], ['image/jpeg', 'image/jpg', 'image/png'])) {
                throw new FileTypeException('');
            }

            $dataFiles[] = [
                'name' => $file['name'],
                'size' => $file['size'],
                'tmp_name' => $file['tmp_name'],
                'type' => $file['type'],
                'MODULE_ID' => 'iblock',
            ];
        }

        return $dataFiles;
    }

    /**
     * @throws ServiceNotFoundException
     * @throws ApplicationCreateException
     * @throws ServiceCircularReferenceException
     */
    protected function setUserBundle(): void
    {
        $this->userAuthService = App::getInstance()->getContainer()->get(UserAuthorizationInterface::class);
        $this->userCurrentUserService = App::getInstance()->getContainer()->get(CurrentUserProviderInterface::class);
    }

    /**
     * @throws \Exception
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws LogicException
     * @throws LoaderException
     * @throws SystemException
     * @throws RuntimeException
     */
    protected function setHLEntity(): void
    {
        $this->hlEntity = static::getHLEntity($this->arParams['HL_ID']);
    }

    /**
     * @throws SystemException
     * @throws ArgumentException
     * @return array
     */
    protected function getComments(): array
    {
        $query = $this->hlEntity::query();
        $query->setSelect(['*']);
        $query->setFilter(
            [
                'UF_OBJECT_ID' => $this->arParams['OBJECT_ID'],
                'UF_TYPE'      => $this->arParams['TYPE'],
                'UF_ACTIVE'    => 1,
            ]
        );
        $query->setOrder(
            [
                'UF_DATE' => ($this->arParams['SORT_DESC'] === 'Y') ? 'desc' : 'asc',
                'ID'      => ($this->arParams['SORT_DESC'] === 'Y') ? 'desc' : 'asc',
            ]
        );
        $query->countTotal(true);
        if ($this->arParams['ITEMS_COUNT'] > 0) {
            $query->setLimit($this->arParams['ITEMS_COUNT']);
        }
        if ((int)$this->arParams['PAGE'] > 0) {
            $query->setOffset($this->arParams['ITEMS_COUNT'] * (int)$this->arParams['PAGE']);
        }

        $res = $query->exec();
        $items = [];
        $userIds = [];
        $imageIds = [];

        while ($item = $res->fetch()) {
            if ($item['UF_DATE'] instanceof Date) {
                $item['DATE_FORMATED'] = $item['UF_DATE']->format($this->arParams['ACTIVE_DATE_FORMAT']);
            }
            if ((int)$item['UF_USER_ID'] > 0) {
                $userIds[$item['ID']] = (int)$item['UF_USER_ID'];
            } else {
                $item['USER_NAME'] = 'Анонимно';
            }
            $items[$item['ID']] = $item;

            if ($item['UF_PHOTOS'] && is_array($item['UF_PHOTOS']) && !empty($item['UF_PHOTOS'])) {
                $imageIds = array_merge($imageIds, $item['UF_PHOTOS']);
            }
        }
        if (!empty($userIds)) {
            $users = $this->userCurrentUserService->getUserRepository()->findBy(['ID' => array_unique($userIds)]);
            if (\is_array($users) && !empty($users)) {
                foreach ($users as $user) {
                    foreach ($userIds as $itemID => $userID) {
                        if ($userID === $user->getId()) {
                            $items[$itemID]['USER_NAME'] = $user->getFullName();
                            unset($userIds[$itemID]);
                        }
                    }
                }
            }
        }

        $imageIds = array_filter($imageIds);
        $images = [];
        if (!empty($imageIds)) {
            $rsFile = CFile::GetList(false, ['@ID' => array_unique($imageIds)]);

            while ($arFile = $rsFile->GetNext()) {
                $images[$arFile['ID']] = CFile::GetFileSRC($arFile);
            }
        }

        return [
            'ITEMS' => $items,
            'IMAGES' => $images,
            'COUNT' => $res->getCount(),
        ];
    }

    /**
     * @return int
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws ArgumentException
     */
    protected function getRating(): int
    {
        $rating = 0;
        if (\is_array($this->arResult['COMMENTS']) && !empty($this->arResult['COMMENTS'])) {
            $rating = $this->getSumMarkComments() / $this->arResult['COUNT_COMMENTS'];
        }

        return $rating;
    }

    /**
     * @return int
     * @throws ArgumentException
     * @throws SystemException
     * @throws ObjectPropertyException
     */
    protected function getSumMarkComments(): int
    {
        $query = $this->hlEntity::query();
        $query->setSelect(['SUM']);
        $query->setFilter(
            [
                'UF_OBJECT_ID' => $this->arParams['OBJECT_ID'],
                'UF_TYPE'      => $this->arParams['TYPE'],
                'UF_ACTIVE'    => 1,
            ]
        );
        $query->registerRuntimeField(
            'SUM',
            new ExpressionField(
                'SUM',
                'SUM(%s)',
                ['UF_MARK']
            )
        );

        return (int)$query->exec()->fetch()['SUM'];
    }
}
