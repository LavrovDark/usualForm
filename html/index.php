<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('ТЕСТ'); ?>
<div class="top-section">
    <form method="post" js-form-typical>
        <div>
            <input type="tel" data-input data-mask-phone required autocomplete="tel" name="phone" placeholder="Телефон">
        </div>
        <div>
            <input type="email" data-input required autocomplete="email" name="email" placeholder="Email">
        </div>
        <div>
            <input type="text" name="fullName" placeholder="ФИО" required>
        </div>
        <div>
            <label>
                <input type="radio" name="radio" value="Инпут 1">
                Инпут 1
            </label>
            <label>
                <input type="radio" name="radio" value="Инпут 2">
                Инпут 2
            </label>
        </div>
        <div>
            <label>
                <input type="checkbox" name="check" value="Чекбокс 1">
                Чекбокс
            </label>
        </div>
        <div><textarea name="comment" cols="30" rows="5"></textarea></div>
        <button type="send" js-send-form>
            Отправить
        </button>
        <input type="hidden" name="csrf_token" value="<?= bitrix_sessid() ?>">
    </form>
</div>
<?php require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'); ?>