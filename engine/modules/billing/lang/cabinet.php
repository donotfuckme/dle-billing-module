<?PHP 
/*
=====================================================
 Billing
-----------------------------------------------------
 evgeny.tc@gmail.com
-----------------------------------------------------
 This code is copyrighted
=====================================================
*/

$billing_lang = array (

	/* Cabinet */
	'cabinet_off' => "Личный кабинет отключён!",
	'cabinet_controller_error' => "Файл контроллера user.{c} не найден!",
	'cabinet_metod_error' => "Метод контроллера user.{c}->{m} не найден!",
	'cabinet_theme_error' => "Невозможно загрузить шаблон ",
	
	/* Pay */
	'pay_need_login' => "Требуется авторизация!",
	'pay_hash_error' => "Время ожидания модуля закончилось. Повторите попытку",
	'pay_paysys_error' => "Платёжная система не выбрана",
	'pay_summa_error' => "Не указана сумма",
	'pay_minimum_error' => "Минимальная сумма оплаты для ",
	'pay_main_error' => "Пополнение баланса не доступно",
	'pay_invoice_error' => "Квитанция не найдена",
	'pay_file_error' => "Файл платёжной системы не найден!",
	
	'pay_invoice' => "Квитанция #{id}",
	'pay_msgOk' => "Пополнен счёт через {paysys} на {money}",
	
	'pay_error_title' => "Ошибка",
	
	'pay_getErr_key' => "Ключ доступа платёжной системы устарел",
	'pay_getErr_paysys' => "Платёжная система не найдена",
	'pay_getErr_invoice' => "Квитанция не найдена, либо уже оплачена",

	/* Refund */
	'refund_error_requisites' => "Не указаны <a href=\"{link_to_user}\">реквизиты</a>",
	'refund_error_balance' => "Недостаточно средств",
	'refund_error_minimum' => "Минимум для вывода: ",
	'refund_msgOk' => "Вывод средств из системы",
	'refund_wait' => "Ожидается",
	'refund_email_title' => "Запрос вывода средств",
	'refund_email_msg' => "Пользователь запросил вывод средств.<br /><br />Подробнее - ",
	
	/* Transfer */
	'transfer_error_get' => "Получатель не найден",
	'transfer_error_minimum' => "Минимум для перевода: ",
	'transfer_error_name_me' => "Вы не можете отправить средства самому себе",
	
	'transfer_log_for' => "Перевод средств для <a href=/user/{login}>{login}</a>",
	'transfer_log_from' => "Перевод средств от <a href=/user/{login}>{login}</a>",
	'transfer_log_text' => "Перевод для пользователя <a href=\"{link}\">{user}</a> выполнен. Комиссия составляет {com}",
	'transfer_msgOk' => "Перевод отправлен",
	
);

?>