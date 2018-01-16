<?php

namespace FourPaws\UserBundle\Repository;

use Bitrix\Main\UserTable;
use CUser;
use FourPaws\AppBundle\Serialization\ArrayOrFalseHandler;
use FourPaws\AppBundle\Serialization\BitrixBooleanHandler;
use FourPaws\AppBundle\Serialization\BitrixDateHandler;
use FourPaws\AppBundle\Serialization\BitrixDateTimeHandler;
use FourPaws\Helpers\Exception\WrongPhoneNumberException;
use FourPaws\Helpers\PhoneHelper;
use FourPaws\UserBundle\Entity\User;
use FourPaws\UserBundle\Exception\BitrixRuntimeException;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\TooManyUserFoundException;
use FourPaws\UserBundle\Exception\UsernameNotFoundException;
use FourPaws\UserBundle\Exception\ValidationException;
use JMS\Serializer\ArrayTransformerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Exception\RuntimeException;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Exception\ValidatorException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserRepository
{
    const FIELD_ID = 'ID';
    
    /** @var Serializer $builder */
    protected $serializer;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var CUser
     */
    private $cuser;

    /**
     * @var \CAllMain|\CMain
     */
    private $cmain;
    
    /**
     * UserRepository constructor.
     *
     * @param ValidatorInterface $validator
     *
     * @throws RuntimeException
     */public function __construct(ValidatorInterface $validator)
    {
        $this->serializer = SerializerBuilder::create()->configureHandlers(
            function (HandlerRegistry $registry) {
                $registry->registerSubscribingHandler(new BitrixDateHandler());
                $registry->registerSubscribingHandler(new BitrixDateTimeHandler());
                $registry->registerSubscribingHandler(new BitrixBooleanHandler());
                $registry->registerSubscribingHandler(new ArrayOrFalseHandler());
            }
        )->build();
        
        $this->cuser = new CUser();
        $this->validator = $validator;
        global $APPLICATION;
        $this->cmain = $APPLICATION;
    }


    /**
     * @param User $user
     *
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function create(User $user): bool
    {
        $validationResult = $this->validator->validate($user, null, ['create']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to create');
        }

        $result = $this->cuser->Add(
            $this->serializer->toArray($user, SerializationContext::create()->setGroups(['create']))
        );
        if ((int)$result > 0) {
            $user->setId((int)$result);
            return true;
        }

        throw new BitrixRuntimeException($this->cuser->LAST_ERROR);
    }

    /**
     * @param int $id
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @return null|User
     */
    public function find(int $id)
    {
        $this->checkIdentifier($id);
        $result = $this->findBy([static::FIELD_ID => $id], [], 1);
        return reset($result);
    }
    
    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param array $criteria
     * @param array $orderBy
     * @param null|int $limit
     * @param null|int $offset
     *
     * @return User[]
     */
    public function findBy(array $criteria = [], array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $result = UserTable::query()
            ->setSelect(['*', 'UF_*'])
            ->setFilter($criteria)
            ->setOrder($orderBy)
            ->setLimit($limit)
            ->setOffset($offset)
            ->exec();
        if (0 === $result->getSelectedRowsCount()) {
            return [];
        }

        /**
         * todo change group name to constant
         */
        return $this->serializer->fromArray(
            $result->fetchAll(),
            sprintf('array<%s>', User::class),
            DeserializationContext::create()->setGroups(['read'])
        );
    }
    
    /**
     * @param string $rawLogin
     *
     * @param bool   $onlyActive
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @return int
     * @throws WrongPhoneNumberException
     */
    public function findIdentifierByRawLogin(string $rawLogin, bool $onlyActive = true): int
    {
        return (int)$this->findIdAndLoginByRawLogin($rawLogin, $onlyActive)['ID'];
    }
    
    /**
     * @param string $rawLogin
     *
     * @param bool   $onlyActive
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @return string
     * @throws WrongPhoneNumberException
     */
    public function findLoginByRawLogin(string $rawLogin, bool $onlyActive = true): string
    {
        return (string)$this->findIdAndLoginByRawLogin($rawLogin, $onlyActive)['LOGIN'];
    }
    
    /**
     * @param string $rawLogin
     *
     * @param bool   $onlyActive
     *
     * @throws UsernameNotFoundException
     * @throws TooManyUserFoundException
     * @return array|false
     * @throws WrongPhoneNumberException
     */
    protected function findIdAndLoginByRawLogin(string $rawLogin, bool $onlyActive = true)
    {
        $query = UserTable::query()
            ->addSelect('ID')
            ->addSelect('LOGIN')
            ->setFilter([
                [
                    'LOGIC' => 'OR',
                    [
                        '=LOGIN' => $rawLogin,
                    ],
                    [
                        '=EMAIL' => $rawLogin,
                    ],
                    [
                        '=PERSONAL_PHONE' => PhoneHelper::isPhone($rawLogin) ? PhoneHelper::normalizePhone(
                            $rawLogin
                        ) : $rawLogin,
                    ],
                ],
            ]
        );
        if ($onlyActive) {
            $query->addFilter('ACTIVE', 'Y');
        }
        $result = $query->exec();


        if (1 === $result->getSelectedRowsCount()) {
            return $result->fetchRaw();
        }
        if (0 === $result->getSelectedRowsCount()) {
            throw new UsernameNotFoundException(sprintf('No user with such raw login %s', $rawLogin));
        }

        throw new TooManyUserFoundException('Found more than one user with same raw login');
    }

    /**
     * @param User $user
     *
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws ValidationException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function update(User $user) : bool
    {
        $this->checkIdentifier($user->getId());
        $validationResult = $this->validator->validate($user, null, ['update']);
        if ($validationResult->count() > 0) {
            throw new ValidationException('Wrong entity passed to update');
        }
        if ($this->cuser->Update(
            $user->getId(),
            $this->serializer->toArray($user, SerializationContext::create()->setGroups(['update']))
        )) {
            return true;
        }
        throw new BitrixRuntimeException($this->cuser->LAST_ERROR);
    }

    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     * @throws BitrixRuntimeException
     * @return bool
     */
    public function delete(int $id) : bool
    {
        $this->checkIdentifier($id);
        if (CUser::Delete($id)) {
            return true;
        }

        $bitrixException = $this->cmain->GetException();
        throw new BitrixRuntimeException($bitrixException->GetString(), $bitrixException->GetID() ?: null);
    }

    /**
     * @param int $id
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidIdentifierException
     */
    protected function checkIdentifier(int $id)
    {
        try {
            $result = $this->validator->validate($id, [
                new NotBlank(),
                new GreaterThanOrEqual(['value' => 1]),
                new Type(['type' => 'integer']),
            ], ['delete']);
        } catch (ValidatorException $exception) {
            throw new ConstraintDefinitionException('Wrong constraint configuration');
        }
        if ($result->count()) {
            throw new InvalidIdentifierException(sprintf('Wrong identifier %s passed', $id));
        }
    }
}
