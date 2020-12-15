# simpla_dellin
## Модуль расчета доставки деловые линии


##### Добавим необходимые поля в базу,


1. Поля габаритов для Варианта товара - s_variants:

    ALTER TABLE `s_variants`
        ADD `length` DECIMAL(9,4) NOT NULL AFTER `external_id`,
        ADD `height` DECIMAL(9,4) NOT NULL AFTER `length`,
        ADD `width` DECIMAL(9,4) NOT NULL AFTER `height`,
        ADD `weight` DECIMAL(9,4) NOT NULL AFTER `width`;

2. Поля для выбора модуля доставки и сохранения настроек модуля в таблице доставок - s_delivery:

    ALTER TABLE `s_delivery`
        ADD `module` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `separate_payment`,
        ADD `settings` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `module`;

3. Поле для сохранения данных от модуля в заказе - s_orders:

    ALTER TABLE `s_orders`
        ADD `delivery_info` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL AFTER `delivery_info`;

---

##### Научим варианты товаров сохранять и вызывать габариты api/Variants.php, можно ипользовать приложенный файл, но если стоят иные модификации, или же это клон симплы, то:


1. get_variants:

    $query = $this->db->placehold("
            SELECT
                v.id,
                v.product_id ,
                v.price,
                NULLIF(v.compare_price, 0) as compare_price,
                v.sku,
                v.unit,
                IFNULL(v.stock, ?) as stock,
                (v.stock IS NULL) as infinity,
                v.name,
                v.attachment,
                v.position,
                v.length,
                v.height,
                v.width,
                v.weight
            FROM __variants AS v
            WHERE 1
                $product_id_filter
				$variant_id_filter
				$instock_filter
            ORDER BY v.position
        ", $this->settings->max_order_amount);

2. get_variant:

    $query = $this->db->placehold("
            SELECT
                v.id,
                v.product_id ,
                v.price,
                NULLIF(v.compare_price, 0) as compare_price,
                v.sku,
                v.unit,
                IFNULL(v.stock, ?) as stock,
                (v.stock IS NULL) as infinity,
                v.name,
                v.attachment,
                v.length,
                v.height,
                v.width,
                v.weight
            FROM __variants v
            WHERE v.id=?
            LIMIT 1
        ", $this->settings->max_order_amount, $id);
