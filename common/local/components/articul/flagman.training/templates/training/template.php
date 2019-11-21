<?php if (!empty($arResult['SECTIONS'])): ?>
    <section class="service-flagship-store" data-item-service-flagship-store="training">
        <div class="b-container">
            <div class="service-flagship-store__header service-flagship-store__header_training">
                <div class="service-flagship-store__inner-header">
                    <div class="service-flagship-store__header-title">Тренировочный клуб</div>
                </div>
            </div>
            <div class="service-flagship-store__content" data-content-service-flagship-store="true" style="display: block">
                <div class="service-flagship-store__title">
                    В тренировочном клубе вы можете пройти мастер-класс вместе со своей собакой
                </div>
                <div class="service-flagship-store__descr">
                    Запись на&nbsp;мастер класс по&nbsp;послушанию питомца. Вы&nbsp;сможете задать вопросы по&nbsp;правильному воспитанию вашей собаки опытному кинологу, а&nbsp;так&nbsp;же, разучить несколько команд.
                </div>
                <a class="link-walking-flagship-store" href="/events/Правила_тренировочного_клуба.pdf" target="_blank">Правила тренировочного клуба</a>

                <form class="form-signup-training-flagship js-form-validation" data-form-signup-training-flagship="true">
                    <div class="form-signup-training-flagship__content">
                        <div class="b-input-line">
                            <div class="b-input-line__label-wrapper">
                                <span class="b-input-line__label">Дата</span>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block" data-date-training-flagship="true">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                    <?php foreach ($arResult['SECTIONS'] as $section) : ?>
                                        <option value="<?=$section['ID']?>" data-url="/flagman/getlocalschedule/<?=$section['ID']?>/"
                                                data-date-option="<?=$section['NAME']?>"><?=$section['NAME']?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                        </div>

                        <div class="b-input-line b-input-line--time">
                            <div class="b-input-line__label-wrapper">
                                <label class="b-input-line__label">Время</label>
                            </div>
                            <div class="b-select">
                                <select class="b-select__block" data-time-training-flagship="true">
                                    <option value="" disabled="disabled" selected="selected">выберите</option>
                                </select>
                                <div class="b-error"><span class="js-message"></span></div>
                            </div>
                        </div>

                        <div class="form-signup-training-flagship__btn-wrap">
                            <button type="submit" class="b-button" data-popup-id="training-flagship-store" data-btn-training-flagship-store="true">Записаться</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
<?php endif; ?>