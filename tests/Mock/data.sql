-- CATEGORIES
CREATE TABLE `categories`
(
    `id`   int PRIMARY KEY NOT NULL,
    `name` varchar(255)    NOT NULL
);

INSERT INTO `categories` (`id`, `name`)
VALUES (1, 'Category #1'),
       (2, 'Category #2'),
       (3, 'Category #3');

-- PRODUCTS
CREATE TABLE `products`
(
    `id`          int           NOT NULL,
    `category_id` int DEFAULT NULL,
    `name`        varchar(255)  NOT NULL,
    `description` text,
    `price`       decimal(8, 2) NOT NULL,
    FOREIGN KEY (category_id) REFERENCES categories (id) ON UPDATE cascade ON DELETE set null
);

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`)
VALUES (1, 1, 'Product #1', 'Lorem ipsum', '6.99'),
       (2, 1, 'Product #2', 'Dolor sit', '5.69'),
       (3, NULL, 'Product #3', NULL, '18.99'),
       (4, 2, 'Product #4', 'Dolor sit', '12.49'),
       (5, 3, 'Product #5', 'Aster mor', '16.20'),
       (6, 1, 'Product #6', NULL, '32.00'),
       (7, NULL, 'Product #7', 'Loras dore', '15.00'),
       (8, 2, 'Product #8', NULL, '19.99'),
       (9, 1, 'Product #9', NULL, '21.00'),
       (10, 3, 'Product #10', 'Mora de sito', '22.00');

-- ORDERS
CREATE TABLE `orders`
(
    `id`         int PRIMARY KEY NOT NULL,
    `customer`   varchar(255)    NOT NULL,
    `address`    varchar(255)    NOT NULL,
    `created_at` datetime        NOT NULL DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `orders` (`id`, `customer`, `address`, `created_at`)
VALUES (1, 'John Doe', '9998 High Street', '2022-09-01 16:04:21'),
       (2, 'Moe Lester', '88 New Street', '2022-09-11 17:16:43'),
       (3, 'Jane Dane', '96 Victoria Street', '2022-09-16 07:38:18'),
       (4, 'Kelly J Lozano', '13 West Street', '2022-09-06 09:20:15');

-- ORDER_PRODUCT
CREATE TABLE `order_product`
(
    `order_id`   int           NOT NULL,
    `product_id` int DEFAULT NULL,
    `price`      decimal(8, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders (id) ON UPDATE cascade ON DELETE cascade,
    FOREIGN KEY (product_id) REFERENCES products (id) ON UPDATE cascade ON DELETE set null
);

INSERT INTO `order_product` (`order_id`, `product_id`, `price`)
VALUES (1, 1, '6.99'),
       (1, 4, '12.49'),
       (1, 7, '15.00'),
       (2, 10, '22.00'),
       (2, 6, '32.00'),
       (3, 2, '5.69'),
       (3, NULL, '7.00'),
       (3, 6, '32.00'),
       (4, 9, '21.00'),
       (4, 7, '15.00'),
       (4, 1, '6.99'),
       (4, 2, '5.69');
