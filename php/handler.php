<?php

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Application;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Highloadblock\HighloadBlockTable as HL;

header('Content-Type: application/json'); //Заголовок ответа обработчика

$request = Application::getInstance()->getContext()->getRequest();

/* Стандартная проверка запроса */
if (!$request->isAjaxRequest() || !$request->isPost() || !$request->getPost('csrf_token')) {
    $response = [
        'success' => false,
    ];
    echo Json::encode($response);
    die();
}
/* Проверка на csrf-токен */
if (!check_bitrix_sessid('csrf_token')) {
    $response = [
        'success' => false,
    ];
    echo Json::encode($response);
    die();
}
/* Объявляем обязательные поля */
$requredFields = ['phone', 'email', 'fullName'];
$additionalFields = ['comment', 'radio', 'check'];

$errors = []; //Массив, куда будем складывать ошибки

/* Подготавливаем массив с необходимыми нам полями, одновременно проверяя их на пустоту */
foreach ($requredFields as $item) {
    $postData[$item] = htmlspecialchars($request->getPost($item));
    if (empty($postData[$item])) {
        array_push($errors, $item);
    }
}

foreach ($additionalFields as $item) {
    $postData[$item] = htmlspecialchars($request->getPost($item));
}

//Валидация телефона
$phone = preg_replace('/[^0-9]/', '', $postData['phone']);

if (mb_strlen($phone) < 11) {
    $errors[] = 'wrong_phone';
}

//Валидация Email
if (!filter_var($postData['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'wrong_email';
}

/* Если высыпались ошибки, прекращаем работу скрипта и отдаем сообщение об ошибке в ответе */
if (!empty($errors)) {
    $response = [
        'success' => false,
        'data' => $errors,
        'message' => 'Заполнены не все обязательные поля'
    ];
    echo Json::encode($response);
    die();
}
/* Выполняем какие-то действия с полями, например запись в ХЛ */


if (!Loader::includeModule('highloadblock')) {
    $response = [
        'success' => false,
        'message' => 'Ошибка при загрузке модуля'
    ];
    echo Json::encode($response);
    die();
}
//ID нужных ХЛ и ИБ лучше записать в константы, получив из ИБ настроек
$hlblock = HL::getById(HL_ID_TEST)->fetch();

$entity = HL::compileEntity($hlblock);
$entity_data_class = $entity->getDataClass();

//Проверяем на дубль записи, если необходимо
$rs = $entity_data_class::getList([
    'select' => ['*'],
    'filter' => [
        '=UF_PHONE' => $postData['phone'],
        '=UF_EMAIL' => $postData['email'],
        '=UF_FULL_NAME' => $postData['fullName'],
    ]
]);

if ($ar = $rs->Fetch()) {
    $response = [
        'success' => false,
        'message' => 'Запись уже существует'
    ];
    echo Json::encode($response);
    die();
}

//Подготовка массива с данными для записи в ХЛ
$data = [
    'UF_PHONE' => $postData['phone'],
    'UF_EMAIL' => $postData['email'],
    'UF_FULL_NAME' => $postData['fullName'],
    'UF_DATE' => new DateTime()
];

if(!empty($postData['comment'])){
    $data['UF_COMMENT'] = $postData['comment'];
}

$result = $entity_data_class::add($data);

if(!$result->isSuccess()){
    $response = [
        'success' => false,
        'message' => 'Ошибка при добавлении записи в ХЛ'
    ];
    echo Json::encode($response);
    die();
}

/* Если нужны данные с бэка, передаем их в поле data в ответе */
$data = [
    'postData' => $postData
];

//Формируем конечный массив с ответом
$response = [
    'success' => true,
    'message' => 'Запрос успешно обработан',
    'data' => $data
];

//Возвращаем конечный массив в виде JSON
echo Json::encode($response);
