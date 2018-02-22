<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\Helpers;

/**
 * Class DateHelper
 *
 * @package FourPaws\Helpers
 */
class DateHelper
{
    /** именительный падеж */
    const NOMINATIVE = 'Nominative';
    
    /** родительный падеж */
    const GENITIVE = 'Genitive';
    
    /** именительный падеж короткий*/
    const SHORT_NOMINATIVE = 'ShortNominative';
    
    /** родительный падеж короткий */
    const SHORT_GENITIVE = 'ShortGenitive';

    /** дательный падеж множ. число */
    const DATIVE_PLURAL = 'DativePlural';

    /**Месяца в родительном падеже*/
    private static $monthGenitive = [
        '#1#'  => 'Января',
        '#2#'  => 'Февраля',
        '#3#'  => 'Марта',
        '#4#'  => 'Апреля',
        '#5#'  => 'Мая',
        '#6#'  => 'Июня',
        '#7#'  => 'Июля',
        '#8#'  => 'Августа',
        '#9#'  => 'Сентября',
        '#10#' => 'Октября',
        '#11#' => 'Ноября',
        '#12#' => 'Декабря',
    ];
    
    /** Месяца в именительном падеже  */
    private static $monthNominative = [
        '#1#'  => 'Январь',
        '#2#'  => 'Февраль',
        '#3#'  => 'Март',
        '#4#'  => 'Апрель',
        '#5#'  => 'Май',
        '#6#'  => 'Июнь',
        '#7#'  => 'Июль',
        '#8#'  => 'Август',
        '#9#'  => 'Сентябрь',
        '#10#' => 'Октябрь',
        '#11#' => 'Ноябрь',
        '#12#' => 'Декабрь',
    ];
    
    /** кратские месяца в именительном падеже  */
    private static $monthShortNominative = [
        '#1#'  => 'янв',
        '#2#'  => 'фев',
        '#3#'  => 'мар',
        '#4#'  => 'апр',
        '#5#'  => 'май',
        '#6#'  => 'июн',
        '#7#'  => 'июл',
        '#8#'  => 'авг',
        '#9#'  => 'сен',
        '#10#' => 'окт',
        '#11#' => 'ноя',
        '#12#' => 'дек',
    ];
    
    /**кратские месяца в родительном падеже*/
    private static $monthShortGenitive = [
        '#1#'  => 'янв',
        '#2#'  => 'фев',
        '#3#'  => 'мар',
        '#4#'  => 'апр',
        '#5#'  => 'мая',
        '#6#'  => 'июн',
        '#7#'  => 'июл',
        '#8#'  => 'авг',
        '#9#'  => 'сен',
        '#10#' => 'окт',
        '#11#' => 'ноя',
        '#12#' => 'дек',
    ];
    
    /**дни недели в именительном падеже*/
    private static $dayOfWeekNominative = [
        '#1#' => 'Понедельник',
        '#2#' => 'Вторник',
        '#3#' => 'Среда',
        '#4#' => 'Четверг',
        '#5#' => 'Пятница',
        '#6#' => 'Суббота',
        '#7#' => 'Воскресенье',
    ];

    /** дни недели в множ. числе дат. падеже */
    private static $dayOfWeekDativePlural = [
        '#1#' => 'Понедельникам',
        '#2#' => 'Вторникам',
        '#3#' => 'Средам',
        '#4#' => 'Четвергам',
        '#5#' => 'Пятницам',
        '#6#' => 'Субботам',
        '#7#' => 'Воскресеньям',
    ];

    /**краткие дни недели*/
    private static $dayOfWeekShortNominative = [
        '#1#' => 'пн',
        '#2#' => 'вт',
        '#3#' => 'ср',
        '#4#' => 'чт',
        '#5#' => 'пт',
        '#6#' => 'сб',
        '#7#' => 'вс',
    ];
    
    /**
     * @param string $date
     *
     * @param string $case
     *
     * @return string
     */
    public static function replaceRuMonth(string $date, string $case = 'Nominative') : string
    {
        return static::replaceStringByArray(
            [
                'date'    => $date,
                'case'    => $case,
                'type'    => 'month',
                'pattern' => '|#\d{1,2}#|',
            ]
        );
    }
    
    private static function replaceStringByArray(array $params)
    {
        preg_match($params['pattern'], $params['date'], $matches);
        if (!empty($matches[0]) && !empty($params['case'])) {
            $items = static::${$params['type'] . $params['case']};
            if (!empty($items)) {
                return str_replace($matches[0], $items[$matches[0]], $params['date']);
            }
        }
        
        return $params['date'];
    }
    
    /**
     * @param string $date
     *
     * @param string $case
     *
     * @return string
     */
    public static function replaceRuDayOfWeek(string $date, string $case = 'Nominative') : string
    {
        return static::replaceStringByArray(
            [
                'date'    => $date,
                'case'    => $case,
                'type'    => 'dayOfWeek',
                'pattern' => '|#\d{1}#|',
            ]
        );
    }
}
