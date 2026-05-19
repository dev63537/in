SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20),
  `address` TEXT,
  `role` ENUM('customer','admin') DEFAULT 'customer',
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(110) NOT NULL UNIQUE,
  `description` TEXT,
  `image` VARCHAR(255),
  `status` ENUM('active','inactive') DEFAULT 'active',
  `sort_order` INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `products` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL UNIQUE,
  `description` TEXT,
  `price` DECIMAL(10,2) NOT NULL,
  `sale_price` DECIMAL(10,2) DEFAULT 0,
  `stock` INT DEFAULT 0,
  `sizes` VARCHAR(200) DEFAULT 'XS,S,M,L,XL,XXL',
  `colors` VARCHAR(200) DEFAULT 'Black,White,Beige',
  `image` VARCHAR(255),
  `gallery` TEXT,
  `tags` VARCHAR(255),
  `featured` TINYINT(1) DEFAULT 0,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED,
  `order_number` VARCHAR(30) NOT NULL UNIQUE,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `discount` DECIMAL(10,2) DEFAULT 0,
  `shipping_charge` DECIMAL(10,2) DEFAULT 0,
  `status` ENUM('pending','processing','shipped','delivered','cancelled','refunded') DEFAULT 'pending',
  `payment_method` VARCHAR(50) DEFAULT 'COD',
  `payment_status` ENUM('pending','paid','failed','refunded') DEFAULT 'pending',
  `shipping_name` VARCHAR(100),
  `shipping_email` VARCHAR(150),
  `shipping_phone` VARCHAR(20),
  `shipping_address` TEXT,
  `shipping_city` VARCHAR(80),
  `shipping_state` VARCHAR(80),
  `shipping_pincode` VARCHAR(10),
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED,
  `product_name` VARCHAR(200),
  `product_image` VARCHAR(255),
  `size` VARCHAR(20),
  `color` VARCHAR(50),
  `quantity` INT NOT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL,
  `subtotal` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED,
  `reviewer_name` VARCHAR(100),
  `rating` TINYINT NOT NULL,
  `comment` TEXT,
  `status` ENUM('pending','approved') DEFAULT 'approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `newsletter_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `status` ENUM('active','unsubscribed') DEFAULT 'active',
  `subscribed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `coupon_codes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(30) NOT NULL UNIQUE,
  `type` ENUM('percent','fixed') DEFAULT 'percent',
  `value` DECIMAL(10,2) NOT NULL,
  `min_order` DECIMAL(10,2) DEFAULT 0,
  `usage_limit` INT DEFAULT 0,
  `used_count` INT DEFAULT 0,
  `expires_at` DATE,
  `status` ENUM('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`name`,`email`,`password`,`role`,`status`) VALUES
('Admin User','admin@luxemode.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin','active'),
('Priya Sharma','priya@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','customer','active'),
('Rahul Gupta','rahul@example.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','customer','active');

INSERT INTO `categories` (`name`,`slug`,`description`,`status`,`sort_order`) VALUES
('Womens Fashion','womens-fashion','Trendy styles for women','active',1),
('Mens Fashion','mens-fashion','Premium menswear collection','active',2),
('Kids Wear','kids-wear','Fun and comfortable kids clothing','active',3),
('Ethnic Wear','ethnic-wear','Traditional and fusion ethnic wear','active',4),
('Accessories','accessories','Complete your look','active',5),
('Western Wear','western-wear','Modern western clothing','active',6);

INSERT INTO `products`
(`category_id`,`name`,`slug`,`description`,`price`,`sale_price`,`stock`,`sizes`,`colors`,`image`,`tags`,`featured`) VALUES
(1,'Floral Wrap Midi Dress','floral-wrap-midi-dress','Elegant floral wrap dress perfect for all occasions.',2499,1799,45,'XS,S,M,L,XL','Rose,Ivory,Navy','https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600','new,featured,women',1),
(1,'Boho Floral Maxi Dress','boho-floral-maxi-dress','Free-spirited maxi dress with stunning floral print.',3299,0,30,'XS,S,M,L,XL','Multicolor,White','https://images.unsplash.com/photo-1496747611176-843222e1e57c?w=600','new,women',0),
(2,'Slim Fit Chinos','slim-fit-chinos','Modern slim fit chinos for the contemporary man.',1899,1299,80,'28,30,32,34,36,38','Khaki,Navy,Olive,Black','https://images.unsplash.com/photo-1473966968600-fa801b869a1a?w=600','men,featured',1),
(2,'Premium Oxford Shirt','premium-oxford-shirt','Classic Oxford button-down shirt. Perfect for office.',2199,0,60,'S,M,L,XL,XXL','White,Light Blue,Pink,Grey','https://images.unsplash.com/photo-1598032895397-b9472444bf93?w=600','new,men',0),
(4,'Anarkali Kurta Set','anarkali-kurta-set','Stunning Anarkali kurta with matching dupatta.',3999,2999,25,'XS,S,M,L,XL','Magenta,Teal,Golden','https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=600','ethnic,featured,sale',1),
(1,'High Waist Denim Shorts','high-waist-denim-shorts','Trendy high-waist denim shorts for casual look.',1599,1199,55,'XS,S,M,L,XL','Blue,Black,White','https://images.unsplash.com/photo-1541099649105-f69ad21f3246?w=600','sale,women',0),
(2,'Graphic Print T-Shirt','graphic-print-tshirt','Comfortable graphic tee. 100% pure cotton.',799,0,120,'XS,S,M,L,XL,XXL','White,Black,Grey','https://images.unsplash.com/photo-1583743814966-8936f5b7be1a?w=600','men,new',0),
(5,'Gold Layered Necklace','gold-layered-necklace','Elegant multi-layer gold-tone necklace.',899,699,200,'One Size','Gold','https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=600','accessories,sale',0),
(6,'Wide Leg Palazzo Pants','wide-leg-palazzo-pants','Flowy wide-leg palazzo pants. Comfort meets style.',1799,1399,40,'XS,S,M,L,XL','Black,Cream,Burgundy','https://images.unsplash.com/photo-1594938298603-c8148c4b84b4?w=600','western,women',0),
(3,'Kids Denim Dungaree','kids-denim-dungaree','Adorable denim dungaree for kids.',1299,999,35,'2-3Y,4-5Y,6-7Y,8-9Y,10-11Y','Blue,Black','https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?w=600','kids,new',0),
(1,'Satin Slip Dress','satin-slip-dress','Luxurious satin slip dress for evening occasions.',4499,3499,20,'XS,S,M,L','Champagne,Black,Red','https://images.unsplash.com/photo-1566174053879-31528523f8ae?w=600','new,featured,women',1),
(2,'Relaxed Fit Linen Shirt','relaxed-fit-linen-shirt','Breathable linen shirt perfect for summers.',1699,0,70,'S,M,L,XL,XXL','White,Sky Blue,Beige','https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600','men',0);

INSERT INTO `reviews` (`product_id`,`reviewer_name`,`rating`,`comment`,`status`) VALUES
(1,'Ananya M.',5,'Absolutely love this dress! The fabric is so soft and the fit is perfect.','approved'),
(1,'Divya R.',4,'Beautiful design, got so many compliments. Slightly runs small, order one up.','approved'),
(3,'Vikram S.',5,'Best chinos I have ever bought. Great quality for the price!','approved'),
(5,'Meera K.',5,'Wore this to a wedding and everyone asked where I got it. Stunning!','approved'),
(11,'Pooja T.',4,'Elegant dress, arrived quickly. Great packaging.','approved');

INSERT INTO `coupon_codes` (`code`,`type`,`value`,`min_order`,`usage_limit`,`status`) VALUES
('WELCOME10','percent',10,500,100,'active'),
('LUXE20','percent',20,1500,50,'active'),
('FLAT200','fixed',200,999,30,'active');

SET FOREIGN_KEY_CHECKS = 1;