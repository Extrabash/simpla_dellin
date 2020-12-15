# simpla_dellin
Модуль расчета доставки деловые линии

1) Добавим необходимые поля в базу,

1.1) Поля габаритов для Варианта товара - s_variants:

    ALTER TABLE `s_variants`
        ADD `length` DECIMAL(9,4) NOT NULL AFTER `external_id`,
        ADD `height` DECIMAL(9,4) NOT NULL AFTER `length`,
        ADD `width` DECIMAL(9,4) NOT NULL AFTER `height`,
        ADD `weight` DECIMAL(9,4) NOT NULL AFTER `width`;

1.2) Поля для выбора модуля доставки и сохранения настроек модуля в таблице доставок - s_delivery:

    ALTER TABLE `s_delivery`
        ADD `module` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `separate_payment`,
        ADD `settings` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `module`;

1.3) Поле для сохранения данных от модуля в заказе - s_orders:

    ALTER TABLE `s_orders`
        ADD `delivery_info` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `delivery_info`;
