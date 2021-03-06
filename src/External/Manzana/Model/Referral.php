<?php

namespace FourPaws\External\Manzana\Model;

use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * Class Referral
 *
 * @package FourPaws\External\Manzana\Model
 *
 * @ExclusionPolicy("none")
 * @XmlNamespace(uri="http://www.w3.org/2001/XMLSchema-instance", prefix="xsi")
 * @XmlRoot("Referral_Card")
 */
class Referral
{
    public const IS_MODERATED = ['Не указано', '1'];
    public const SUCCESS_MODERATE = ['Да', '200000'];
    public const CANCEL_MODERATE = ['Нет', '200001'];
    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("card_number")
     */
    public $cardNumber;

    /**
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("referral_number")
     */
    public $referralNumber;

    /**
     * @XmlElement(cdata=false)
     * @Type("float")
     * @SerializedName("sum_referral_bonus")
     */
    public $sumReferralBonus;

    /**
     * Актуальность реферала
     * 1 - Не указано, 2000 - Да, 2001 - Нет
     * @XmlElement(cdata=false)
     * @Type("string")
     * @SerializedName("is_questionnaire_actual")
     */
    public $isQuestionnaireActual;

    /**
     * @return bool
     */
    public function isModerated(): bool
    {
        return \in_array($this->isQuestionnaireActual, static::IS_MODERATED, true);
    }

    /**
     * @return bool
     */
    public function isSuccessModerate(): bool
    {
        return \in_array($this->isQuestionnaireActual, static::SUCCESS_MODERATE, true);
    }

    /**
     * @return bool
     */
    public function isCancelModerate(): bool
    {
        return \in_array($this->isQuestionnaireActual, static::CANCEL_MODERATE, true);
    }

    /**
     * @return float
     */
    public function getBonus(): float
    {
        return (float)$this->sumReferralBonus;
    }

    /**
     * @return string
     */
    public function getCardNumber(): string
    {
        return $this->cardNumber ?? '';
    }

    /**
     * @return string
     */
    public function getReferralNumber(): string
    {
        return $this->referralNumber ?? '';
    }
}
