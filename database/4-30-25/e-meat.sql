-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 03:47 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `e-meat`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddRider` (IN `p_rider_name` VARCHAR(255), IN `p_contact` VARCHAR(255), OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255), OUT `p_new_id` INT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        SET p_message = 'Error adding rider: Database error';
        ROLLBACK;
    END;
    
    START TRANSACTION;
    
    -- Check if inputs are valid
    IF p_rider_name = '' OR p_contact = '' THEN
        SET p_success = FALSE;
        SET p_message = 'Name and contact information are required.';
    ELSE
        -- Insert the new rider
        INSERT INTO RIDER (rider_name, contact) VALUES (p_rider_name, p_contact);
        
        -- Get the new ID
        SET p_new_id = LAST_INSERT_ID();
        
        SET p_success = TRUE;
        SET p_message = 'Rider added successfully.';
        COMMIT;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddToUserCart` (IN `p_user_id` INT, IN `p_meat_part_id` INT, IN `p_qty` DECIMAL(10,2), IN `p_unit` VARCHAR(2), OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255), OUT `p_is_update` BOOLEAN, OUT `p_new_qty` DECIMAL(10,2), OUT `p_product_name` VARCHAR(100), OUT `p_unit_price` DECIMAL(10,2))   the_proc: BEGIN
    DECLARE v_available_stock DECIMAL(10,2);
    DECLARE v_exists INT DEFAULT 0;
    DECLARE v_current_qty DECIMAL(10,2) DEFAULT 0;
    DECLARE v_cart_id INT;
    DECLARE v_check_qty DECIMAL(10,2);
    
    -- Start transaction
    START TRANSACTION;
    
    -- Initialize outputs
    SET p_success = FALSE;
    SET p_message = '';
    SET p_is_update = FALSE;
    SET p_new_qty = p_qty;
    
    -- Basic validations
    IF p_qty <= 0 THEN
        SET p_message = 'Invalid quantity.';
        ROLLBACK;
        LEAVE the_proc;
    END IF;
    
    IF p_unit = 'g' THEN
        IF p_qty < 100 THEN
            SET p_message = 'Minimum quantity for grams is 100g.';
            ROLLBACK;
            LEAVE the_proc;
        END IF;
        
        IF p_qty > 950 THEN
            SET p_message = 'Maximum quantity for grams is 950g.';
            ROLLBACK;
            LEAVE the_proc;
        END IF;
    ELSEIF p_unit = 'kg' AND p_qty < 0.1 THEN
        SET p_message = 'Minimum quantity for kilograms is 0.1kg.';
        ROLLBACK;
        LEAVE the_proc;
    END IF;
    
    -- Get product details
    SELECT QTY_AVAILABLE, MEAT_PART_NAME, UNIT_PRICE INTO v_available_stock, p_product_name, p_unit_price
    FROM MEAT_PART
    WHERE MEAT_PART_ID = p_meat_part_id;
    
    IF p_product_name IS NULL THEN
        SET p_message = 'Product not found.';
        ROLLBACK;
        LEAVE the_proc;
    END IF;
    
    -- Check available stock (convert to kg if unit is g)
    SET v_check_qty = IF(p_unit = 'g', p_qty / 1000, p_qty);
    
    IF v_check_qty > v_available_stock THEN
        SET p_message = CONCAT('Not enough stock available! Only ', FORMAT(v_available_stock, 2), ' kg left.');
        ROLLBACK;
        LEAVE the_proc;
    END IF;
    
    -- Check if item exists in cart
    SELECT COUNT(*), IFNULL(CART_ID, 0), IFNULL(QUANTITY, 0) 
    INTO v_exists, v_cart_id, v_current_qty
    FROM USER_CART
    WHERE APP_USER_ID = p_user_id 
    AND MEAT_PART_ID = p_meat_part_id
    AND UNIT_OF_MEASURE = p_unit;
    
    -- Handle existing item
    IF v_exists > 0 THEN
        SET p_is_update = TRUE;
        SET p_new_qty = v_current_qty + p_qty;
        
        -- Revalidate combined quantity
        IF p_unit = 'g' AND p_new_qty > 950 THEN
            SET p_message = CONCAT('Cannot add more. Maximum quantity for grams is 950g. You already have ', v_current_qty, 'g in cart.');
            ROLLBACK;
            LEAVE the_proc;
        END IF;
        
        -- Check stock for new total
        SET v_check_qty = IF(p_unit = 'g', p_new_qty / 1000, p_new_qty);
        
        IF v_check_qty > v_available_stock THEN
            SET p_message = 'Not enough stock available to add more of this item.';
            ROLLBACK;
            LEAVE the_proc;
        END IF;
        
        -- Update existing cart item
        UPDATE USER_CART
        SET QUANTITY = p_new_qty, ADDED_AT = CURRENT_TIMESTAMP
        WHERE CART_ID = v_cart_id;
        
        SET p_message = 'Quantity updated in cart.';
    ELSE
        -- Add new item to cart
        INSERT INTO USER_CART (APP_USER_ID, MEAT_PART_ID, QUANTITY, UNIT_OF_MEASURE, UNIT_PRICE) 
        VALUES (p_user_id, p_meat_part_id, p_qty, p_unit, p_unit_price);
        
        SET p_message = 'Item added to cart successfully.';
    END IF;
    
    -- Success
    SET p_success = TRUE;
    COMMIT;
    
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AuthenticateUser` (IN `p_username` VARCHAR(50), OUT `p_user_id` INT, OUT `p_password` VARCHAR(255), OUT `p_user_type` VARCHAR(10))   BEGIN
    SELECT APP_USER_ID, NEW_PASSWORD, USER_TYPE 
    INTO p_user_id, p_password, p_user_type
    FROM APP_USER 
    WHERE USERNAME = p_username;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckUsernameExists` (IN `p_username` VARCHAR(50), OUT `p_exists` BOOLEAN)   BEGIN
    DECLARE user_count INT;
    
    -- Count users with the given username
    SELECT COUNT(*) INTO user_count
    FROM APP_USER 
    WHERE USERNAME = p_username;
    
    -- Set the output parameter based on count
    IF user_count > 0 THEN
        SET p_exists = TRUE;
    ELSE
        SET p_exists = FALSE;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteRider` (IN `p_rider_id` INT, OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255), OUT `p_count` INT)   BEGIN
    -- Check if rider is assigned to any order
    SELECT COUNT(*) INTO p_count FROM ORDERS WHERE RIDER_ID = p_rider_id;
    
    IF p_count > 0 THEN
        -- Rider is assigned to orders, cannot delete
        SET p_success = FALSE;
        SET p_message = CONCAT('Cannot delete rider. This rider is assigned to ', p_count, ' order(s).');
    ELSE
        -- No assignments, proceed with deletion
        DELETE FROM RIDER WHERE rider_id = p_rider_id;
        
        -- Check if deletion was successful
        IF ROW_COUNT() > 0 THEN
            SET p_success = TRUE;
            SET p_message = 'Rider deleted successfully.';
        ELSE
            SET p_success = FALSE;
            SET p_message = 'Error deleting rider: Rider not found.';
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllMeatCategories` ()   BEGIN
    SELECT 
        DISTINCT MEAT_CATEGORY_ID, 
        MEAT_NAME 
    FROM 
        MEAT_CATEGORY 
    ORDER BY 
        MEAT_NAME;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllMeatProducts` ()   BEGIN
    SELECT 
        p.MEAT_PART_ID, 
        c.MEAT_NAME, 
        p.MEAT_PART_NAME, 
        p.MEAT_PART_PHOTO, 
        p.QTY_AVAILABLE, 
        p.UNIT_PRICE, 
        p.UNIT_OF_MEASURE, 
        c.MEAT_CATEGORY_ID,
        c.MEAT_NAME AS category
    FROM 
        MEAT_PART p
    JOIN 
        MEAT_CATEGORY c ON p.MEAT_CATEGORY_ID = c.MEAT_CATEGORY_ID
    ORDER BY 
        c.MEAT_NAME, p.MEAT_PART_NAME;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllOrders` ()   BEGIN
    -- Select all orders with joined information from related tables
    SELECT 
        o.ORDERS_ID,
        o.TOTAL_AMOUNT,
        o.ORDERS_DATE,
        o.LAST_UPDATE,
        o.MODIFIED_BY,
        o.RIDER_ID, -- Add this column
        
        -- Customer information
        u.USER_FNAME,
        u.USER_LNAME,
        
        -- Status information
        s.STAT_ID,
        s.STATUS_NAME,
        
        -- Admin information (who modified the order)
        admin.USER_FNAME AS ADMIN_FNAME,
        admin.USER_LNAME AS ADMIN_LNAME,
        
        -- Rider information (add these columns)
        r.rider_name,
        r.contact as rider_contact
        
    FROM 
        ORDERS o
        
    -- Join with APP_USER to get customer information
    INNER JOIN 
        APP_USER u ON o.APP_USER_ID = u.APP_USER_ID
        
    -- Join with STATUS to get status information
    LEFT JOIN 
        STATUS s ON o.STAT_ID = s.STAT_ID
        
    -- Join with APP_USER again to get admin information
    LEFT JOIN 
        APP_USER admin ON o.MODIFIED_BY = admin.APP_USER_ID
        
    -- Add this join to get rider information
    LEFT JOIN 
        RIDER r ON o.RIDER_ID = r.rider_id
        
    -- Order by most recent orders first
    ORDER BY 
        o.ORDERS_DATE ASC, o.ORDERS_ID ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllProducts` (IN `p_category_id` INT, IN `p_search_term` VARCHAR(100), IN `p_in_stock_only` BOOLEAN)   BEGIN
    SELECT 
        p.MEAT_PART_ID,
        p.MEAT_PART_NAME,
        p.MEAT_PART_PHOTO,
        p.UNIT_PRICE,
        p.UNIT_OF_MEASURE,
        p.QTY_AVAILABLE,
        c.MEAT_NAME AS category
    FROM 
        MEAT_PART p
    JOIN 
        MEAT_CATEGORY c ON p.MEAT_CATEGORY_ID = c.MEAT_CATEGORY_ID
    WHERE
        (p_category_id IS NULL OR p.MEAT_CATEGORY_ID = p_category_id)
        AND (p_search_term IS NULL OR p.MEAT_PART_NAME LIKE CONCAT('%', p_search_term, '%'))
        AND (p_in_stock_only IS NULL OR p_in_stock_only = FALSE OR p.QTY_AVAILABLE > 0)
    ORDER BY
        c.MEAT_NAME, p.MEAT_PART_NAME;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllRiders` ()   BEGIN
    SELECT rider_id, rider_name, contact
    FROM RIDER
    ORDER BY rider_id ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetAllStatusOptions` ()   BEGIN
    -- Retrieve all available status options
    SELECT STAT_ID, STATUS_NAME
    FROM STATUS;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMeatPurchaseDetailed` ()   BEGIN
    SELECT 
        CONCAT(u.USER_FNAME, ' ', u.USER_LNAME) AS customer_name,
        c.MEAT_NAME AS meat_category,
        p.MEAT_PART_NAME,
        d.QTY AS total_quantity,
        d.UNIT_OF_MEASURE,
        d.UNIT_PRICE,
        CASE 
            WHEN d.UNIT_OF_MEASURE = 'g' THEN (d.QTY / 1000) * d.UNIT_PRICE
            ELSE d.QTY * d.UNIT_PRICE
        END AS total_amount,
        o.ORDERS_DATE AS order_date
    FROM 
        ORDERS o
    JOIN 
        ORDERS_DETAIL d ON o.ORDERS_ID = d.ORDERS_ID
    JOIN 
        MEAT_PART p ON d.MEAT_PART_ID = p.MEAT_PART_ID
    JOIN 
        MEAT_CATEGORY c ON p.MEAT_CATEGORY_ID = c.MEAT_CATEGORY_ID
    JOIN 
        APP_USER u ON o.APP_USER_ID = u.APP_USER_ID
    ORDER BY 
        customer_name, o.ORDERS_DATE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetMeatStock` ()   BEGIN
    SELECT 
        mc.MEAT_NAME AS category,
        SUM(mp.QTY_AVAILABLE) AS total_stock
    FROM 
        MEAT_PART mp
    JOIN 
        MEAT_CATEGORY mc ON mp.MEAT_CATEGORY_ID = mc.MEAT_CATEGORY_ID
    GROUP BY 
        mc.MEAT_NAME;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetOrderItemsReceipt` (IN `p_order_id` INT)   BEGIN
    -- Retrieve the items for the specified order
    SELECT od.MEAT_PART_ID, mp.MEAT_PART_NAME, od.QTY, od.UNIT_OF_MEASURE, od.UNIT_PRICE
    FROM ORDERS_DETAIL od
    JOIN MEAT_PART mp ON od.MEAT_PART_ID = mp.MEAT_PART_ID
    WHERE od.ORDERS_ID = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetOrdersReceipt` (IN `p_user_id` INT, IN `p_order_id` INT)   BEGIN
    IF p_order_id IS NULL THEN
        -- Retrieve all orders for the user
        SELECT o.ORDERS_ID, o.ORDERS_DATE, o.TOTAL_AMOUNT, s.STATUS_NAME, s.STAT_ID, r.rider_name AS RIDER_NAME,
            r.contact AS RIDER_CONTACT, p.PAYMENT_METHOD
        FROM ORDERS o
        JOIN STATUS s ON o.STAT_ID = s.STAT_ID
        LEFT JOIN RIDER r ON o.RIDER_ID = r.RIDER_ID
        LEFT JOIN PAYMENT p ON o.PAYMENT_ID = p.PAYMENT_ID
        WHERE o.APP_USER_ID = p_user_id;
    ELSE
        -- Retrieve a specific order for the user
        SELECT o.ORDERS_ID, o.ORDERS_DATE, o.TOTAL_AMOUNT, s.STATUS_NAME, s.STAT_ID, r.RIDER_NAME, p.PAYMENT_METHOD
        FROM ORDERS o
        JOIN STATUS s ON o.STAT_ID = s.STAT_ID
        LEFT JOIN RIDER r ON o.RIDER_ID = r.rider_id
        LEFT JOIN PAYMENT p ON o.PAYMENT_ID = p.PAYMENT_ID
        WHERE o.APP_USER_ID = p_user_id AND o.ORDERS_ID = p_order_id;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetProcessOrderResults` (OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255), OUT `p_order_id` INT)   BEGIN
    -- Simply return the current values of session variables
    -- This is safer than direct SELECT @variable queries
    SET p_success = @success;
    SET p_message = @message;
    SET p_order_id = @order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetProductDetailsForEdit` (IN `p_meat_part_id` INT)   BEGIN
    SELECT 
        MEAT_PART_NAME, 
        QTY_AVAILABLE, 
        UNIT_PRICE, 
        UNIT_OF_MEASURE
    FROM 
        MEAT_PART 
    WHERE 
        MEAT_PART_ID = p_meat_part_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetProductName` (IN `p_meat_part_id` INT, OUT `p_product_name` VARCHAR(100))   BEGIN
    DECLARE v_name VARCHAR(100);
    
    -- Get the product name
    SELECT MEAT_PART_NAME INTO v_name
    FROM MEAT_PART
    WHERE MEAT_PART_ID = p_meat_part_id;
    
    -- If found, set the output parameter
    IF v_name IS NOT NULL THEN
        SET p_product_name = v_name;
    ELSE
        SET p_product_name = 'Unknown Product';
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRiderAll` (IN `p_sort_by` VARCHAR(50))   BEGIN
    IF p_sort_by = 'name' THEN
        SELECT * FROM RIDER ORDER BY rider_name ASC;
    ELSEIF p_sort_by = 'id_desc' THEN
        SELECT * FROM RIDER ORDER BY rider_id DESC;
    ELSE
        SELECT * FROM RIDER ORDER BY rider_id ASC;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRiderById` (IN `p_rider_id` INT)   BEGIN
    SELECT * FROM RIDER WHERE rider_id = p_rider_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetSalesOverview` ()   BEGIN
    -- Handle NULL values with COALESCE for better display
    -- Total sales for all time
    SELECT COALESCE(SUM(TOTAL_AMOUNT), 0) AS total_sales_all_time
    FROM ORDERS
    WHERE STAT_ID IS NOT NULL;

    -- Total sales for the last 1 day (24 hours)
    SELECT COALESCE(SUM(TOTAL_AMOUNT), 0) AS total_sales_last_1_day
    FROM ORDERS
    WHERE STAT_ID IS NOT NULL
      AND ORDERS_DATE >= NOW() - INTERVAL 1 DAY;

    -- Total sales for this week
    SELECT COALESCE(SUM(TOTAL_AMOUNT), 0) AS total_sales_this_week
    FROM ORDERS
    WHERE STAT_ID IS NOT NULL
      AND YEARWEEK(ORDERS_DATE, 1) = YEARWEEK(CURDATE(), 1);
      
    -- Total sales for this month
    SELECT COALESCE(SUM(TOTAL_AMOUNT), 0) AS total_sales_this_month
    FROM ORDERS
    WHERE STAT_ID IS NOT NULL
      AND YEAR(ORDERS_DATE) = YEAR(CURDATE())
      AND MONTH(ORDERS_DATE) = MONTH(CURDATE());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetStockLevelsForCart` (IN `product_ids_list` TEXT)   BEGIN
    /* This procedure fetches current stock levels for multiple products
       Accepts a comma-separated list of product IDs
       Returns meat_part_id and qty_available for each product */
    
    SET @sql = CONCAT('SELECT MEAT_PART_ID, QTY_AVAILABLE FROM MEAT_PART WHERE MEAT_PART_ID IN (', product_ids_list, ')');
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserCart` (IN `p_user_id` INT)   BEGIN
    SELECT 
        uc.MEAT_PART_ID, 
        mp.MEAT_PART_NAME, 
        mp.UNIT_PRICE, 
        uc.QUANTITY, 
        uc.UNIT_OF_MEASURE,
        uc.ADDED_AT
    FROM USER_CART uc
    JOIN MEAT_PART mp ON uc.MEAT_PART_ID = mp.MEAT_PART_ID
    WHERE uc.APP_USER_ID = p_user_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserOrders` (IN `p_user_id` INT, IN `p_order_id` INT)   BEGIN
    IF p_order_id IS NULL THEN
        -- Fetch all orders for the user
        SELECT o.ORDERS_ID, o.TOTAL_AMOUNT, o.ORDERS_DATE, s.STATUS_NAME, s.STAT_ID,
               sh.SHIP_NAME, p.PAYMENT_METHOD
        FROM ORDERS o
        JOIN STATUS s ON o.STAT_ID = s.STAT_ID
        LEFT JOIN SHIPPER sh ON o.SHIP_ID = sh.SHIP_ID
        LEFT JOIN PAYMENT p ON o.PAYMENT_ID = p.PAYMENT_ID
        WHERE o.APP_USER_ID = p_user_id
        ORDER BY o.ORDERS_ID ASC;
    ELSE
        -- Fetch specific order
        SELECT o.ORDERS_ID, o.TOTAL_AMOUNT, o.ORDERS_DATE, s.STATUS_NAME, s.STAT_ID,
               sh.SHIP_NAME, p.PAYMENT_METHOD
        FROM ORDERS o
        JOIN STATUS s ON o.STAT_ID = s.STAT_ID
        LEFT JOIN SHIPPER sh ON o.SHIP_ID = sh.SHIP_ID
        LEFT JOIN PAYMENT p ON o.PAYMENT_ID = p.PAYMENT_ID
        WHERE o.APP_USER_ID = p_user_id AND o.ORDERS_ID = p_order_id
        ORDER BY o.ORDERS_ID ASC;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertAppUser` (IN `p_user_fname` VARCHAR(20), IN `p_user_lname` VARCHAR(20), IN `p_user_role` VARCHAR(10), IN `p_address` VARCHAR(60), IN `p_phone_number` VARCHAR(24), IN `p_username` VARCHAR(30), IN `p_new_password` VARCHAR(30), OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255))   BEGIN
    DECLARE exit_handler INT DEFAULT 0;
    
    -- Use simpler error handling for MariaDB
    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        SET p_message = 'Database error occurred';
        SET exit_handler = 1;
    END;
    
    START TRANSACTION;
    
    -- Insert the user
    INSERT INTO APP_USER (
        USER_FNAME, 
        USER_LNAME, 
        USER_TYPE, 
        ADDRESS, 
        PHONE_NUMBER, 
        USERNAME, 
        NEW_PASSWORD
    ) VALUES (
        p_user_fname,
        p_user_lname,
        p_user_role,
        p_address,
        p_phone_number,
        p_username,
        p_new_password
    );
    
    -- Check if an error occurred
    IF exit_handler = 1 THEN
        ROLLBACK;
    ELSE
        -- Set success output parameters
        SET p_success = TRUE;
        SET p_message = 'User registered successfully';
        COMMIT;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `InsertMeatPart` (IN `p_app_user_id` INT, IN `p_meat_category_id` INT, IN `p_meat_part_name` VARCHAR(25), IN `p_meat_part_photo` VARCHAR(20), IN `p_qty_available` INT, IN `p_unit_of_measure` VARCHAR(10), IN `p_unit_price` DECIMAL(15,2))   BEGIN
    -- Insert the new meat part into the MEAT_PART table
    INSERT INTO MEAT_PART (
        APP_USER_ID,
        MEAT_CATEGORY_ID,
        MEAT_PART_NAME,
        MEAT_PART_PHOTO,
        QTY_AVAILABLE,
        UNIT_OF_MEASURE,
        UNIT_PRICE
    ) VALUES (
        p_app_user_id,
        p_meat_category_id,
        p_meat_part_name,
        p_meat_part_photo,
        p_qty_available,
        p_unit_of_measure,
        p_unit_price
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `OrderConfirmationPHPquery` (IN `p_user_id` INT)   BEGIN
    -- Retrieve the orders for the user with payment method
        SELECT o.ORDERS_ID, CAST(o.ORDERS_DATE AS DATETIME) AS ORDERS_DATE, 
           s.STATUS_NAME, p.PAYMENT_METHOD
        FROM ORDERS o
        JOIN STATUS s ON o.STAT_ID = s.STAT_ID
        LEFT JOIN PAYMENT p ON o.PAYMENT_ID = p.PAYMENT_ID
        WHERE o.APP_USER_ID = p_user_id;

        -- Retrieve the details of each order
        SELECT od.ORDERS_ID, mp.MEAT_PART_NAME, od.QTY, od.UNIT_OF_MEASURE, od.UNIT_PRICE
        FROM ORDERS_DETAIL od
        JOIN MEAT_PART mp ON od.MEAT_PART_ID = mp.MEAT_PART_ID
        WHERE od.ORDERS_ID IN (SELECT ORDERS_ID FROM ORDERS WHERE APP_USER_ID = p_user_id);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessOrder` (IN `p_user_id` INT, IN `p_payment_id` INT, IN `p_cart_items` JSON, OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255), OUT `p_order_id` INT)   proc_label: BEGIN
    DECLARE v_total_amount DECIMAL(10,2) DEFAULT 0;
    DECLARE v_order_id INT;
    DECLARE v_item_index INT DEFAULT 0;
    DECLARE v_items_count INT;
    DECLARE v_meat_part_id INT;
    DECLARE v_quantity DECIMAL(10,2);
    DECLARE v_unit VARCHAR(2);
    DECLARE v_unit_price DECIMAL(10,2);
    DECLARE v_line_total DECIMAL(10,2);
    DECLARE v_qty_needed DECIMAL(10,2);
    DECLARE v_qty_available DECIMAL(10,2);
    DECLARE v_product_name VARCHAR(100);
    DECLARE v_shipping_fee DECIMAL(10,2) DEFAULT 50.00;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        SET p_message = CONCAT('Database error: ', MYSQL_ERROR());
        ROLLBACK;
    END;
    
    -- Initialize output
    SET p_success = FALSE;
    SET p_message = '';
    SET p_order_id = 0;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Get number of items in the cart
    SET v_items_count = JSON_LENGTH(p_cart_items);
    
    -- Check if cart is empty
    IF v_items_count = 0 THEN
        SET p_message = 'Cart is empty';
        ROLLBACK;
        LEAVE proc_label;
    END IF;
    
    -- Validate stock availability first
    validate_stock: LOOP
        IF v_item_index >= v_items_count THEN
            LEAVE validate_stock;
        END IF;
        
        -- Extract item data
        SET v_meat_part_id = JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].meat_part_id')));
        SET v_quantity = JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].quantity')));
        SET v_unit = UPPER(JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].unit'))));
        SET v_product_name = JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].product_name')));
        
        -- Convert to kg if needed
        IF v_unit = 'G' THEN
            SET v_qty_needed = v_quantity / 1000;
        ELSE
            SET v_qty_needed = v_quantity;
        END IF;
        
        -- Check stock
        SELECT QTY_AVAILABLE INTO v_qty_available 
        FROM MEAT_PART 
        WHERE MEAT_PART_ID = v_meat_part_id;
        
        -- Check if product exists
        IF v_qty_available IS NULL THEN
            SET p_message = CONCAT('Product not found: ', v_product_name);
            ROLLBACK;
            LEAVE proc_label;
        END IF;
        
        -- Check if stock is sufficient
        IF v_qty_available < v_qty_needed THEN
            SET p_message = CONCAT('Not enough stock for: ', v_product_name);
            ROLLBACK;
            LEAVE proc_label;
        END IF;
        
        SET v_item_index = v_item_index + 1;
    END LOOP;
    
    -- Create the order - set status to 1 (pending) by default
    INSERT INTO ORDERS (APP_USER_ID, PAYMENT_ID, STAT_ID, ORDERS_DATE, TOTAL_AMOUNT)
    VALUES (p_user_id, p_payment_id, 1, NOW(), 0);
    
    -- Get the new order ID
    SET v_order_id = LAST_INSERT_ID();
    SET p_order_id = v_order_id;
    
    -- Reset index for processing items
    SET v_item_index = 0;
    
    -- Process each item in cart
    process_items: LOOP
        IF v_item_index >= v_items_count THEN
            LEAVE process_items;
        END IF;
        
        -- Extract item data
        SET v_meat_part_id = JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].meat_part_id')));
        SET v_quantity = JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].quantity')));
        SET v_unit = UPPER(JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].unit'))));
        SET v_unit_price = JSON_UNQUOTE(JSON_EXTRACT(p_cart_items, CONCAT('$[', v_item_index, '].unit_price')));
        
        -- Normalize the unit to either 'KG' or 'G'
        IF v_unit != 'KG' AND v_unit != 'G' THEN
            SET v_unit = IF(LOWER(v_unit) = 'g' OR v_unit = 'gr', 'G', 'KG');
        END IF;
        
        -- Calculate line total
        IF v_unit = 'G' THEN
            SET v_line_total = (v_unit_price * v_quantity) / 1000;
        ELSE
            SET v_line_total = v_unit_price * v_quantity;
        END IF;
        
        -- Add to order total
        SET v_total_amount = v_total_amount + v_line_total;
        
        -- Add to order details
        INSERT INTO ORDERS_DETAIL (ORDERS_ID, MEAT_PART_ID, QTY, UNIT_OF_MEASURE, UNIT_PRICE, LINE_TOTAL)
        VALUES (v_order_id, v_meat_part_id, v_quantity, v_unit, v_unit_price, v_line_total);
        
        -- Update inventory - reduce available quantity
        UPDATE MEAT_PART 
        SET QTY_AVAILABLE = QTY_AVAILABLE - IF(v_unit = 'G', v_quantity / 1000, v_quantity)
        WHERE MEAT_PART_ID = v_meat_part_id;
        
        SET v_item_index = v_item_index + 1;
    END LOOP;
    
    -- Add shipping fee to total amount
    SET v_total_amount = v_total_amount + v_shipping_fee;
    
    -- Update the order total (including shipping fee)
    UPDATE ORDERS 
    SET TOTAL_AMOUNT = v_total_amount
    WHERE ORDERS_ID = v_order_id;
    
    -- Clear the user cart
    DELETE FROM USER_CART WHERE APP_USER_ID = p_user_id;
    
    -- Commit the transaction
    COMMIT;
    
    SET p_success = TRUE;
    SET p_message = 'Order processed successfully';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `RemoveFromUserCart` (IN `p_user_id` INT, IN `p_meat_part_id` INT, IN `p_unit` VARCHAR(2), OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255), OUT `p_rows_affected` INT)   proc_label: BEGIN
    DECLARE v_count INT DEFAULT 0;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        SET p_message = 'Database error occurred';
        SET p_rows_affected = 0;
        ROLLBACK;
    END;
    
    -- Initialize output parameters
    SET p_success = FALSE;
    SET p_message = '';
    SET p_rows_affected = 0;
    
    -- Start transaction
    START TRANSACTION;
    
    -- Check if the item exists in the cart
    SELECT COUNT(*) INTO v_count
    FROM USER_CART
    WHERE APP_USER_ID = p_user_id
    AND MEAT_PART_ID = p_meat_part_id
    AND UNIT_OF_MEASURE = p_unit;
    
    IF v_count = 0 THEN
        SET p_message = 'Item not found in cart';
        ROLLBACK;
        LEAVE proc_label;
    END IF;
    
    -- Remove the item from the cart
    DELETE FROM USER_CART 
    WHERE APP_USER_ID = p_user_id
    AND MEAT_PART_ID = p_meat_part_id
    AND UNIT_OF_MEASURE = p_unit;
    
    -- Get number of affected rows
    SET p_rows_affected = ROW_COUNT();
    
    IF p_rows_affected = 0 THEN
        SET p_message = 'Failed to remove item from cart';
        ROLLBACK;
        LEAVE proc_label;
    ELSE
        SET p_message = 'Item successfully removed from cart';
        SET p_success = TRUE;
        COMMIT;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_order_checkout` (IN `p_order_id` INT, IN `p_shipper_id` INT, IN `p_payment_id` INT, IN `p_admin_id` INT)   BEGIN
    -- Update the order with the shipper, payment method, and admin ID
    UPDATE ORDERS
    SET SHIP_ID = p_shipper_id,
        PAYMENT_ID = p_payment_id,
        STAT_ID = 1, -- Assuming 1 is the status ID for completed orders
        MODIFIED_BY = p_admin_id,
        LAST_UPDATE = NOW()
    WHERE ORDERS_ID = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_order_status` (IN `p_order_id` INT, IN `p_new_status` INT, IN `p_user_id` INT, IN `p_user_type` VARCHAR(10))   BEGIN
    -- Update the status of the specified order
    UPDATE ORDERS
    SET STAT_ID = p_new_status,
        MODIFIED_BY = p_user_id,
        LAST_UPDATE = NOW()
    WHERE ORDERS_ID = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_update_rider_assigned` (IN `p_rider_id` INT, IN `p_order_id` INT)   BEGIN
    UPDATE ORDERS
    SET RIDER_ID = p_rider_id
    WHERE ORDERS_ID = p_order_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateRider` (IN `p_rider_id` INT, IN `p_rider_name` VARCHAR(255), IN `p_contact` VARCHAR(255), OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255))   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        SET p_message = 'Error updating rider: Database error';
        ROLLBACK;
    END;
    
    START TRANSACTION;
    
    -- Check if inputs are valid
    IF p_rider_name = '' OR p_contact = '' THEN
        SET p_success = FALSE;
        SET p_message = 'Name and contact information are required.';
    ELSE
        -- Update the rider
        UPDATE RIDER 
        SET rider_name = p_rider_name, contact = p_contact 
        WHERE rider_id = p_rider_id;
        
        -- Check if update was successful
        IF ROW_COUNT() > 0 THEN
            SET p_success = TRUE;
            SET p_message = 'Rider updated successfully.';
            COMMIT;
        ELSE
            SET p_success = FALSE;
            SET p_message = 'Error updating rider: Rider not found.';
            ROLLBACK;
        END IF;
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateUserCart` (IN `p_user_id` INT, IN `p_meat_part_id` INT, IN `p_quantity` DECIMAL(10,2), IN `p_unit` VARCHAR(2), OUT `p_success` BOOLEAN, OUT `p_message` VARCHAR(255))   proc_label: BEGIN
    DECLARE v_stock_available DECIMAL(10,2);
    DECLARE v_qty_in_kg DECIMAL(10,2);
    DECLARE v_rows_affected INT;
    
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET p_success = FALSE;
        SET p_message = 'Database error occurred';
        ROLLBACK;
    END;
    
    -- Initialize output
    SET p_success = FALSE;
    SET p_message = '';
    
    -- Start transaction
    START TRANSACTION;
    
    -- Perform validation on quantity
    IF p_quantity <= 0 THEN
        SET p_message = 'Quantity must be greater than zero';
        ROLLBACK;
        LEAVE proc_label;
    END IF;
    
    IF p_unit = 'g' THEN
        IF p_quantity < 100 THEN
            SET p_message = 'Minimum quantity for grams is 100g';
            ROLLBACK;
            LEAVE proc_label;
        END IF;
        
        IF p_quantity > 950 THEN
            SET p_message = 'Maximum quantity for grams is 950g';
            ROLLBACK;
            LEAVE proc_label;
        END IF;
        
        -- Convert grams to kg for stock check
        SET v_qty_in_kg = p_quantity / 1000;
    ELSE
        IF p_quantity < 0.1 THEN
            SET p_message = 'Minimum quantity for kilograms is 0.1kg';
            ROLLBACK;
            LEAVE proc_label;
        END IF;
        
        SET v_qty_in_kg = p_quantity;
    END IF;
    
    -- Check if the product exists and has sufficient stock
    SELECT QTY_AVAILABLE INTO v_stock_available
    FROM MEAT_PART
    WHERE MEAT_PART_ID = p_meat_part_id;
    
    IF v_stock_available IS NULL THEN
        SET p_message = 'Product not found';
        ROLLBACK;
        LEAVE proc_label;
    END IF;
    
    IF v_stock_available < v_qty_in_kg THEN
        SET p_message = CONCAT('Not enough stock available. Only ', FORMAT(v_stock_available, 2), ' kg left.');
        ROLLBACK;
        LEAVE proc_label;
    END IF;
    
    -- Update cart item
    UPDATE USER_CART 
    SET QUANTITY = p_quantity, 
        ADDED_AT = CURRENT_TIMESTAMP
    WHERE APP_USER_ID = p_user_id 
      AND MEAT_PART_ID = p_meat_part_id
      AND UNIT_OF_MEASURE = p_unit;
    
    SET v_rows_affected = ROW_COUNT();
    
    -- If no rows affected, insert a new item (shouldn't happen normally)
    IF v_rows_affected = 0 THEN
        INSERT INTO USER_CART (APP_USER_ID, MEAT_PART_ID, QUANTITY, UNIT_OF_MEASURE) 
        VALUES (p_user_id, p_meat_part_id, p_quantity, p_unit);
        
        SET p_message = 'Cart item created';
    ELSE
        SET p_message = 'Cart item updated';
    END IF;
    
    -- Commit changes
    COMMIT;
    
    SET p_success = TRUE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Update_MEAT_PART` (IN `p_meat_part_id` INT, IN `p_qty_available` INT, IN `p_unit_price` DECIMAL(15,2))   BEGIN
    -- Update the quantity available and unit price of the specified meat part
    UPDATE MEAT_PART
    SET QTY_AVAILABLE = p_qty_available,
        UNIT_PRICE = p_unit_price
    WHERE MEAT_PART_ID = p_meat_part_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `app_user`
--

CREATE TABLE `app_user` (
  `APP_USER_ID` int(11) NOT NULL,
  `USER_FNAME` varchar(20) NOT NULL,
  `USER_LNAME` varchar(20) NOT NULL,
  `USER_TYPE` varchar(10) NOT NULL,
  `ADDRESS` varchar(60) NOT NULL,
  `PHONE_NUMBER` varchar(24) NOT NULL,
  `USERNAME` varchar(30) NOT NULL,
  `NEW_PASSWORD` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `app_user`
--

INSERT INTO `app_user` (`APP_USER_ID`, `USER_FNAME`, `USER_LNAME`, `USER_TYPE`, `ADDRESS`, `PHONE_NUMBER`, `USERNAME`, `NEW_PASSWORD`) VALUES
(1, 'Tine ', 'Just', 'user', 'Cebu, Boston', '9895434', 'tin@gmail.com', '123'),
(2, 'john', 'bron', 'admin', 'Cali, Manila', '95126', 'Adminbron@gmail.com', '123'),
(3, 'Lebron', 'John', 'user', 'Purok 2', '0956416163', 'leb@gmail.com', '123'),
(4, 'David', 'Bourn', 'admin', 'Australia, Cali', '1656484', 'bournAdmin@gmail.com', '123');

-- --------------------------------------------------------

--
-- Table structure for table `meat_category`
--

CREATE TABLE `meat_category` (
  `MEAT_CATEGORY_ID` int(11) NOT NULL,
  `MEAT_NAME` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meat_category`
--

INSERT INTO `meat_category` (`MEAT_CATEGORY_ID`, `MEAT_NAME`) VALUES
(1, 'BEEF'),
(2, 'PORK'),
(3, 'CHICKEN');

-- --------------------------------------------------------

--
-- Table structure for table `meat_part`
--

CREATE TABLE `meat_part` (
  `MEAT_PART_ID` int(11) NOT NULL,
  `APP_USER_ID` int(11) NOT NULL,
  `MEAT_CATEGORY_ID` int(11) NOT NULL,
  `MEAT_PART_NAME` varchar(25) NOT NULL,
  `MEAT_PART_PHOTO` varchar(20) NOT NULL,
  `QTY_AVAILABLE` decimal(10,2) NOT NULL,
  `UNIT_OF_MEASURE` varchar(10) NOT NULL,
  `UNIT_PRICE` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meat_part`
--

INSERT INTO `meat_part` (`MEAT_PART_ID`, `APP_USER_ID`, `MEAT_CATEGORY_ID`, `MEAT_PART_NAME`, `MEAT_PART_PHOTO`, `QTY_AVAILABLE`, `UNIT_OF_MEASURE`, `UNIT_PRICE`) VALUES
(1, 2, 2, 'Pork Belly', 'Porks Belly.png', 198.50, 'kg', 158.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `ORDERS_ID` int(11) NOT NULL,
  `APP_USER_ID` int(11) NOT NULL,
  `PAYMENT_ID` int(11) NOT NULL,
  `RIDER_ID` int(11) DEFAULT NULL,
  `STAT_ID` int(11) NOT NULL,
  `ORDERS_DATE` datetime NOT NULL,
  `TOTAL_AMOUNT` decimal(15,2) NOT NULL,
  `LAST_UPDATE` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `MODIFIED_BY` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`ORDERS_ID`, `APP_USER_ID`, `PAYMENT_ID`, `RIDER_ID`, `STAT_ID`, `ORDERS_DATE`, `TOTAL_AMOUNT`, `LAST_UPDATE`, `MODIFIED_BY`) VALUES
(1, 3, 1, NULL, 1, '2025-04-30 09:42:38', 287.00, '2025-04-30 01:42:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders_detail`
--

CREATE TABLE `orders_detail` (
  `DETAIL_ID` int(11) NOT NULL,
  `ORDERS_ID` int(11) NOT NULL,
  `MEAT_PART_ID` int(11) NOT NULL,
  `QTY` decimal(10,2) NOT NULL,
  `UNIT_OF_MEASURE` varchar(10) NOT NULL,
  `UNIT_PRICE` decimal(15,2) NOT NULL,
  `LINE_TOTAL` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders_detail`
--

INSERT INTO `orders_detail` (`DETAIL_ID`, `ORDERS_ID`, `MEAT_PART_ID`, `QTY`, `UNIT_OF_MEASURE`, `UNIT_PRICE`, `LINE_TOTAL`) VALUES
(1, 1, 1, 1.30, 'KG', 158.00, 205.40),
(2, 1, 1, 200.00, 'G', 158.00, 31.60);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PAYMENT_ID` int(11) NOT NULL,
  `PAYMENT_METHOD` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PAYMENT_ID`, `PAYMENT_METHOD`) VALUES
(1, 'COD'),
(2, 'GCASH');

-- --------------------------------------------------------

--
-- Table structure for table `rider`
--

CREATE TABLE `rider` (
  `rider_id` int(11) NOT NULL,
  `rider_name` varchar(100) NOT NULL,
  `contact` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rider`
--

INSERT INTO `rider` (`rider_id`, `rider_name`, `contact`) VALUES
(1, 'Pedros Lumayag', '09451234567'),
(2, 'Grace Tanudtanud', '09162345678'),
(3, 'Marvin Lapitan', '09341234567');

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `STAT_ID` int(11) NOT NULL,
  `STATUS_NAME` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`STAT_ID`, `STATUS_NAME`) VALUES
(1, 'PENDING'),
(2, 'PROCESSING'),
(3, 'INTRANSIT'),
(4, 'DELIVERED'),
(5, 'RECEIVED');

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `CART_ID` int(11) NOT NULL,
  `APP_USER_ID` int(11) NOT NULL,
  `MEAT_PART_ID` int(11) NOT NULL,
  `QUANTITY` decimal(10,2) NOT NULL,
  `UNIT_OF_MEASURE` varchar(10) NOT NULL,
  `UNIT_PRICE` decimal(15,2) NOT NULL,
  `ADDED_AT` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_user`
--
ALTER TABLE `app_user`
  ADD PRIMARY KEY (`APP_USER_ID`);

--
-- Indexes for table `meat_category`
--
ALTER TABLE `meat_category`
  ADD PRIMARY KEY (`MEAT_CATEGORY_ID`);

--
-- Indexes for table `meat_part`
--
ALTER TABLE `meat_part`
  ADD PRIMARY KEY (`MEAT_PART_ID`),
  ADD KEY `APP_USER_ID` (`APP_USER_ID`),
  ADD KEY `MEAT_CATEGORY_ID` (`MEAT_CATEGORY_ID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`ORDERS_ID`),
  ADD KEY `APP_USER_ID` (`APP_USER_ID`),
  ADD KEY `PAYMENT_ID` (`PAYMENT_ID`),
  ADD KEY `RIDER_ID` (`RIDER_ID`),
  ADD KEY `STAT_ID` (`STAT_ID`);

--
-- Indexes for table `orders_detail`
--
ALTER TABLE `orders_detail`
  ADD PRIMARY KEY (`DETAIL_ID`),
  ADD KEY `ORDERS_ID` (`ORDERS_ID`),
  ADD KEY `MEAT_PART_ID` (`MEAT_PART_ID`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PAYMENT_ID`);

--
-- Indexes for table `rider`
--
ALTER TABLE `rider`
  ADD PRIMARY KEY (`rider_id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`STAT_ID`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`CART_ID`),
  ADD KEY `APP_USER_ID` (`APP_USER_ID`),
  ADD KEY `MEAT_PART_ID` (`MEAT_PART_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `app_user`
--
ALTER TABLE `app_user`
  MODIFY `APP_USER_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `meat_category`
--
ALTER TABLE `meat_category`
  MODIFY `MEAT_CATEGORY_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `meat_part`
--
ALTER TABLE `meat_part`
  MODIFY `MEAT_PART_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `ORDERS_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders_detail`
--
ALTER TABLE `orders_detail`
  MODIFY `DETAIL_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PAYMENT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rider`
--
ALTER TABLE `rider`
  MODIFY `rider_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `STAT_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `CART_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meat_part`
--
ALTER TABLE `meat_part`
  ADD CONSTRAINT `meat_part_ibfk_1` FOREIGN KEY (`APP_USER_ID`) REFERENCES `app_user` (`APP_USER_ID`),
  ADD CONSTRAINT `meat_part_ibfk_2` FOREIGN KEY (`MEAT_CATEGORY_ID`) REFERENCES `meat_category` (`MEAT_CATEGORY_ID`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`APP_USER_ID`) REFERENCES `app_user` (`APP_USER_ID`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`PAYMENT_ID`) REFERENCES `payment` (`PAYMENT_ID`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`RIDER_ID`) REFERENCES `rider` (`rider_id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`STAT_ID`) REFERENCES `status` (`STAT_ID`);

--
-- Constraints for table `orders_detail`
--
ALTER TABLE `orders_detail`
  ADD CONSTRAINT `orders_detail_ibfk_1` FOREIGN KEY (`ORDERS_ID`) REFERENCES `orders` (`ORDERS_ID`),
  ADD CONSTRAINT `orders_detail_ibfk_2` FOREIGN KEY (`MEAT_PART_ID`) REFERENCES `meat_part` (`MEAT_PART_ID`);

--
-- Constraints for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD CONSTRAINT `user_cart_ibfk_1` FOREIGN KEY (`APP_USER_ID`) REFERENCES `app_user` (`APP_USER_ID`),
  ADD CONSTRAINT `user_cart_ibfk_2` FOREIGN KEY (`MEAT_PART_ID`) REFERENCES `meat_part` (`MEAT_PART_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
