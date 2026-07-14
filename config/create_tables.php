<?php

require "../config/database.php";

// Adjust this line if your config file uses a different variable name
// e.g. $conn instead of $pdo
$pdo = $pdo ?? $conn ?? null;

if (!$pdo instanceof PDO) {
    die("Database connection not found. Check config/database.php");
}

$tables = [];

$tables['users'] = "
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    image VARCHAR(255) NULL,
    role ENUM('admin','employee','guest') NOT NULL DEFAULT 'guest',
    status TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active,0=Inactive',
    remember_token VARCHAR(255) NULL,
    last_login DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['countries'] = "
CREATE TABLE IF NOT EXISTS countries (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code CHAR(2) NOT NULL UNIQUE,
    currency VARCHAR(10) NOT NULL,
    currency_symbol VARCHAR(10) NOT NULL,
    flag VARCHAR(255) NULL,
    slug VARCHAR(120) UNIQUE,
    status TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['brands'] = "
CREATE TABLE IF NOT EXISTS brands (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    logo VARCHAR(255),
    cover_image VARCHAR(255),
    short_description TEXT,
    history LONGTEXT,
    website VARCHAR(255),
    founded_year YEAR NULL,
    status TINYINT(1) DEFAULT 1,
    meta_title VARCHAR(255),
    meta_description TEXT,
    created_by INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_brand_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['brand_country'] = "
CREATE TABLE IF NOT EXISTS brand_country (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id INT UNSIGNED NOT NULL,
    country_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_brand_country (brand_id, country_id),
    CONSTRAINT fk_bc_brand FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    CONSTRAINT fk_bc_country FOREIGN KEY (country_id) REFERENCES countries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['categories'] = "
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) UNIQUE,
    image VARCHAR(255),
    description TEXT,
    status TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['brand_category'] = "
CREATE TABLE IF NOT EXISTS brand_category (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    brand_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_brand_category (brand_id, category_id),
    CONSTRAINT fk_brand_category_brand FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    CONSTRAINT fk_brand_category_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$tables['products'] = "
CREATE TABLE products (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    brand_id INT UNSIGNED NOT NULL,

    category_id INT UNSIGNED NOT NULL,

    name VARCHAR(200) NOT NULL,

    slug VARCHAR(255) NOT NULL UNIQUE,

    image VARCHAR(255),

    short_description TEXT,

    description LONGTEXT,

    calories INT DEFAULT 0,

    featured TINYINT(1) DEFAULT 0,

    status TINYINT(1) DEFAULT 1,

    meta_title VARCHAR(255),

    meta_description TEXT,

    created_by INT UNSIGNED NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_brand
        FOREIGN KEY (brand_id)
        REFERENCES brands(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id)
        REFERENCES categories(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_product_user
        FOREIGN KEY (created_by)
        REFERENCES users(id)
        ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 ";

 $tables['product_prices'] = "
CREATE TABLE product_prices (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    country_id INT UNSIGNED NOT NULL,

    regular_price DECIMAL(10,2),

    discount_price DECIMAL(10,2),

    currency VARCHAR(10),

    status TINYINT(1) DEFAULT 1,

    updated_on DATETIME DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY product_country(product_id,country_id),

    CONSTRAINT fk_price_product
        FOREIGN KEY(product_id)
        REFERENCES products(id)
        ON DELETE CASCADE,

    CONSTRAINT fk_price_country
        FOREIGN KEY(country_id)
        REFERENCES countries(id)
        ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['product_nutrition'] = "
CREATE TABLE product_nutrition (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL UNIQUE,

    fat DECIMAL(8,2),

    carbs DECIMAL(8,2),

    protein DECIMAL(8,2),

    fiber DECIMAL(8,2),

    sugar DECIMAL(8,2),

    sodium DECIMAL(8,2),

    calories INT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_nutrition_product

    FOREIGN KEY(product_id)

    REFERENCES products(id)

    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['Ingredients'] = "
CREATE TABLE ingredients (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL UNIQUE,

    status TINYINT(1) DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['product_ingredients'] = "
CREATE TABLE product_ingredients (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    ingredient_id INT UNSIGNED NOT NULL,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE KEY product_ingredient(product_id,ingredient_id),

    CONSTRAINT fk_pi_product

    FOREIGN KEY(product_id)

    REFERENCES products(id)

    ON DELETE CASCADE,

    CONSTRAINT fk_pi_ingredient

    FOREIGN KEY(ingredient_id)

    REFERENCES ingredients(id)

    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['product_images'] = "
CREATE TABLE product_images (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED NOT NULL,

    image VARCHAR(255) NOT NULL,

    sort_order INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_product_images

    FOREIGN KEY(product_id)

    REFERENCES products(id)

    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['offers'] = "
CREATE TABLE offers (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    brand_id INT UNSIGNED NOT NULL,

    title VARCHAR(255) NOT NULL,

    slug VARCHAR(255) UNIQUE,

    description TEXT,

    discount_percent DECIMAL(5,2) DEFAULT 0,

    coupon_code VARCHAR(50),

    start_date DATE,

    end_date DATE,

    image VARCHAR(255),

    status TINYINT(1) DEFAULT 1,

    created_by INT UNSIGNED,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_offer_brand
    FOREIGN KEY (brand_id)
    REFERENCES brands(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_offer_user
    FOREIGN KEY (created_by)
    REFERENCES users(id)
    ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['offer_countries'] = "
CREATE TABLE offer_countries (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    offer_id INT UNSIGNED NOT NULL,

    country_id INT UNSIGNED NOT NULL,

    UNIQUE KEY offer_country (offer_id,country_id),

    CONSTRAINT fk_offer_country_offer
    FOREIGN KEY (offer_id)
    REFERENCES offers(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_offer_country_country
    FOREIGN KEY (country_id)
    REFERENCES countries(id)
    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['blogs'] = "
CREATE TABLE blogs (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255) NOT NULL,

    slug VARCHAR(255) UNIQUE,

    image VARCHAR(255),

    excerpt TEXT,

    content LONGTEXT,

    category VARCHAR(100),

    author_id INT UNSIGNED,

    views INT DEFAULT 0,

    status TINYINT(1) DEFAULT 1,

    meta_title VARCHAR(255),

    meta_description TEXT,

    published_at DATETIME,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_blog_author
    FOREIGN KEY(author_id)
    REFERENCES users(id)
    ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['testimonials'] = "
CREATE TABLE testimonials (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150),

    designation VARCHAR(150),

    image VARCHAR(255),

    review TEXT,

    rating TINYINT,

    status TINYINT DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['faqs'] = "
CREATE TABLE faqs (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    question TEXT,

    answer LONGTEXT,

    sort_order INT DEFAULT 0,

    status TINYINT DEFAULT 1,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['contact_messages'] = "
CREATE TABLE contact_messages (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150),

    email VARCHAR(150),

    phone VARCHAR(30),

    subject VARCHAR(255),

    ip_address VARCHAR(50),

    message LONGTEXT,

    status ENUM('new','read','replied') DEFAULT 'new',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['settings'] = "
CREATE TABLE settings (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    site_name VARCHAR(255),

    logo VARCHAR(255),

    favicon VARCHAR(255),

    email VARCHAR(150),

    phone VARCHAR(50),

    address TEXT,

    facebook VARCHAR(255),

    instagram VARCHAR(255),

    twitter VARCHAR(255),

    youtube VARCHAR(255),

    linkedin VARCHAR(255),

    google_analytics TEXT,

    google_tag_manager TEXT,

    copyright TEXT,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['pages'] = "
CREATE TABLE pages (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    title VARCHAR(255),

    slug VARCHAR(255) UNIQUE,

    content LONGTEXT,

    meta_title VARCHAR(255),

    meta_description TEXT,

    status TINYINT DEFAULT 1,

    created_by INT UNSIGNED,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_page_user
    FOREIGN KEY(created_by)
    REFERENCES users(id)
    ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['search_logs'] = "
CREATE TABLE search_logs (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    keyword VARCHAR(255),

    country_id INT UNSIGNED,

    total_search INT DEFAULT 1,

    last_search DATETIME,

    CONSTRAINT fk_search_country
    FOREIGN KEY(country_id)
    REFERENCES countries(id)
    ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['product_views'] = "
CREATE TABLE product_views (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    product_id INT UNSIGNED,

    ip_address VARCHAR(50),

    country_id INT UNSIGNED,

    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_view_product
    FOREIGN KEY(product_id)
    REFERENCES products(id)
    ON DELETE CASCADE,

    CONSTRAINT fk_view_country
    FOREIGN KEY(country_id)
    REFERENCES countries(id)
    ON DELETE SET NULL

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['brand_gallery'] = "
CREATE TABLE brand_gallery (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    brand_id INT UNSIGNED,

    image VARCHAR(255),

    sort_order INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_gallery_brand
    FOREIGN KEY(brand_id)
    REFERENCES brands(id)
    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['seo_urls'] = "
CREATE TABLE seo_urls (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    page_type VARCHAR(50),

    reference_id INT,

    canonical VARCHAR(255),

    robots VARCHAR(100),

    schema_json LONGTEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

$tables['redirects'] = "
CREATE TABLE redirects (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    old_slug VARCHAR(255),

    new_slug VARCHAR(255),

    status_code SMALLINT DEFAULT 301,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";


$tables['ad_units'] = "
CREATE TABLE IF NOT EXISTS ad_units (

    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(150) NOT NULL,

    slug VARCHAR(100) NOT NULL,

    ad_slot VARCHAR(50) NULL,

    ad_format VARCHAR(30) NOT NULL DEFAULT 'auto',

    full_width_responsive TINYINT(1) NOT NULL DEFAULT 1,

    status TINYINT(1) NOT NULL DEFAULT 0,

    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY slug (slug)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// Adds AdSense columns to the existing settings table (safe to run only once)
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM settings LIKE 'adsense_client'");
    if ($stmt->rowCount() === 0) {
        $pdo->exec("
            ALTER TABLE settings
            ADD COLUMN adsense_client VARCHAR(50) DEFAULT NULL AFTER google_tag_manager,
            ADD COLUMN adsense_enabled TINYINT(1) NOT NULL DEFAULT 0 AFTER adsense_client
        ");
        echo "Columns 'adsense_client' and 'adsense_enabled' added to settings\n";
    } else {
        echo "AdSense columns already exist on settings, skipped\n";
    }
} catch (PDOException $e) {
    echo "Error altering settings: " . $e->getMessage() . "\n";
}

// Execute in order (order matters because of foreign keys!)
foreach ($tables as $name => $sql) {
    try {
        $pdo->exec($sql);
        echo "Table '$name' created successfully\n";
    } catch (PDOException $e) {
        echo "Error creating '$name': " . $e->getMessage() . "\n";
    }
}