-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 05, 2024 at 06:55 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharmacy_ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `account_type_id` bigint UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `serial` int NOT NULL DEFAULT '1',
  `is_deletable` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `account_type_id`, `name`, `status`, `serial`, `is_deletable`, `created_at`, `updated_at`) VALUES
(1, 3, 'Cost of Sales', 'active', 1, 0, '2024-07-05 05:15:26', '2024-07-05 05:15:26'),
(2, 5, 'Sales', 'active', 2, 0, '2024-07-05 05:15:26', '2024-07-05 05:15:26'),
(3, 4, 'Accounts Payable', 'active', 3, 0, '2024-07-05 05:15:26', '2024-07-05 05:15:26'),
(4, 1, 'Accounts Receivable', 'active', 4, 0, '2024-07-05 05:15:26', '2024-07-05 05:15:26');

-- --------------------------------------------------------

--
-- Table structure for table `account_types`
--

CREATE TABLE `account_types` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `serial` int NOT NULL DEFAULT '1',
  `is_deletable` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_types`
--

INSERT INTO `account_types` (`id`, `name`, `status`, `serial`, `is_deletable`, `created_at`, `updated_at`) VALUES
(1, 'Asset', 'active', 1, 0, '2024-07-05 05:15:05', '2024-07-05 05:15:05'),
(2, 'Equity', 'active', 2, 0, '2024-07-05 05:15:05', '2024-07-05 05:15:05'),
(3, 'Expense', 'active', 3, 0, '2024-07-05 05:15:05', '2024-07-05 05:15:05'),
(4, 'Liability', 'active', 4, 0, '2024-07-05 05:15:05', '2024-07-05 05:15:05'),
(5, 'Revenue', 'active', 5, 0, '2024-07-05 05:15:05', '2024-07-05 05:15:05'),
(6, 'Withdrawal', 'active', 6, 0, '2024-07-05 05:15:05', '2024-07-05 05:15:05');

-- --------------------------------------------------------

--
-- Table structure for table `balances`
--

CREATE TABLE `balances` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int NOT NULL DEFAULT '0',
  `supplier_id` int NOT NULL DEFAULT '0',
  `due` double(20,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `medicine_id` int DEFAULT NULL,
  `emergency_stock_id` int NOT NULL,
  `shop_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `purchase_qty` int NOT NULL,
  `expire` date DEFAULT NULL,
  `leaf_id` int DEFAULT NULL,
  `inv_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `price` double(20,8) NOT NULL DEFAULT '0.00000000',
  `buy_price` double(20,8) NOT NULL DEFAULT '0.00000000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purchase_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint UNSIGNED NOT NULL,
  `medicine_id` int DEFAULT NULL,
  `shop_id` int NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `user_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `global` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `changes`
--

CREATE TABLE `changes` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `old_name` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `medicine_id` int DEFAULT NULL,
  `shop_id` int NOT NULL,
  `image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `category_id` int DEFAULT NULL,
  `oldcat_id` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `phone` varchar(15) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'setup_fee',
  `amount` double(20,2) NOT NULL DEFAULT '0.00',
  `used` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due` double(20,2) NOT NULL DEFAULT '0.00',
  `address` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `upazilla_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `union_id` int DEFAULT NULL,
  `thana_id` int DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `gender` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'Male',
  `age` int NOT NULL DEFAULT '0',
  `uid` int DEFAULT NULL,
  `password` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '$2y$10$yIfOU1IVxZpywg1PAul34.h9WwIShV1HGrSIjSdk4EvINYCfsV9sq',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hospital` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `speciality` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incomes`
--

CREATE TABLE `incomes` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `amount` double(20,2) NOT NULL,
  `date` date DEFAULT NULL,
  `method_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `duration` int DEFAULT '1',
  `package_id` int DEFAULT NULL,
  `inv_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` int DEFAULT NULL,
  `address` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `upazilla_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `union_id` int DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `subtotal` double(20,2) NOT NULL DEFAULT '0.00',
  `total_price` double(20,2) NOT NULL,
  `paid_amount` int NOT NULL DEFAULT '0',
  `returned_amount` int NOT NULL DEFAULT '0',
  `due_price` double(20,2) NOT NULL,
  `date` date DEFAULT NULL,
  `customer_id` int DEFAULT NULL,
  `medicine_id` int DEFAULT NULL,
  `batch_id` int DEFAULT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `method_id` int NOT NULL,
  `inv_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `medicines` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `discount` double(20,2) DEFAULT '0.00',
  `tax` double(20,2) NOT NULL DEFAULT '0.00',
  `type` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'pos',
  `thana_id` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `shops` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_medicines`
--

CREATE TABLE `invoice_medicines` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `amount` double(20,2) NOT NULL DEFAULT '0.00',
  `invoice_id` int NOT NULL DEFAULT '0',
  `customer_id` int NOT NULL DEFAULT '0',
  `medicine_id` int NOT NULL,
  `batch_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_pays`
--

CREATE TABLE `invoice_pays` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `amount` double(20,2) NOT NULL DEFAULT '0.00',
  `invoice_id` int NOT NULL DEFAULT '0',
  `customer_id` int NOT NULL DEFAULT '0',
  `method_id` int NOT NULL,
  `date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE `languages` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iso` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('1','0') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `global` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `qr_code` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `strength` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `leaf_id` int DEFAULT NULL,
  `shelf` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `category_id` int DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `vendor_id` int DEFAULT NULL,
  `vat` double(20,2) NOT NULL DEFAULT '0.00',
  `status` int NOT NULL DEFAULT '0',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `generic_name` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `unit_id` int DEFAULT NULL,
  `des` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `price` double(20,2) NOT NULL DEFAULT '0.00',
  `image` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `buy_price` double(20,2) NOT NULL DEFAULT '0.00',
  `igta` double(20,0) NOT NULL DEFAULT '0',
  `hns_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hot` int NOT NULL DEFAULT '0',
  `global` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `methods`
--

CREATE TABLE `methods` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shop_id` int NOT NULL,
  `balance` double(20,2) NOT NULL DEFAULT '0.00',
  `global` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_04_19_231021_create_permission_tables', 1),
(2, '2024_04_20_024415_create_languages_table', 2),
(3, '2024_04_01_005849_create_notifications_table', 3),
(4, '2024_04_27_225340_create_expense_categories_table', 4),
(5, '2024_04_28_121321_create_pharmacy_expenses_table', 5),
(6, '2024_05_05_013044_add_remember_token_field_to_customer_table', 6),
(7, '2024_06_11_111738_create_account_types_table', 7),
(8, '2024_06_11_112020_create_accounts_table', 8),
(9, '2024_06_11_112131_create_transactions_table', 9),
(10, '2024_06_30_005808_add_account_id_to_expense_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `sender_id` int DEFAULT NULL,
  `receiver_id` int DEFAULT NULL,
  `title` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `seen` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` enum('male','female','other') COLLATE utf8mb4_unicode_ci NOT NULL,
  `age` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `module` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `module`, `label`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'Role', 'Index', 'role.index', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(2, 'Role', 'Create', 'role.create', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(3, 'Role', 'Store', 'role.store', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(4, 'Role', 'Edit', 'role.edit', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(5, 'Role', 'Update', 'role.update', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(6, 'Role', 'Delete', 'role.destroy', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(7, 'User', 'Index', 'user.index', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(8, 'User', 'Create', 'user.create', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(9, 'User', 'Store', 'user.store', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(10, 'User', 'Edit', 'user.edit', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(11, 'User', 'Update', 'user.update', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(12, 'User', 'Delete', 'user.destroy', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(13, 'Customer', 'Index', 'customer.index', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(14, 'Customer', 'Create', 'customer.create', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(15, 'Customer', 'Store', 'customer.store', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(16, 'Customer', 'Edit', 'customer.edit', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(17, 'Customer', 'Update', 'customer.update', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(18, 'Customer', 'Delete', 'customer.destroy', 'web', '2024-05-10 04:01:54', '2024-05-10 04:01:54'),
(19, 'Supplier', 'Index', 'supplier.list', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(20, 'Supplier', 'Create', 'supplier.add', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(21, 'Supplier', 'Edit', 'supplier.edit', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(22, 'Supplier', 'show', 'supplier.view', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(23, 'Supplier', 'Update', 'supplier.update', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(24, 'Supplier', 'Delete', 'supplier.delete', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(25, 'Supplier', 'Due pay', 'supplier.paydue', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(26, 'Vendor', 'Index', 'vendor.index', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(27, 'Vendor', 'Create', 'vendor.create', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(28, 'Vendor', 'Store', 'vendor.store', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(29, 'Vendor', 'Edit', 'vendor.edit', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(30, 'Vendor', 'show', 'vendor.show', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(31, 'Vendor', 'Update', 'vendor.update', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(32, 'Vendor', 'Delete', 'vendor.destroy', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(33, 'Category', 'Index', 'category.index', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(34, 'Category', 'Create', 'category.create', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(35, 'Category', 'Store', 'category.store', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(36, 'Category', 'Edit', 'category.edit', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(37, 'Category', 'Update', 'category.update', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(38, 'Category', 'Delete', 'category.destroy', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(39, 'Medicine', 'Index', 'medicine.list', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(40, 'Medicine', 'Create', 'medicine.create', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(41, 'Medicine', 'Store', 'medicine.store', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(42, 'Medicine', 'Show', 'medicine.show', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(43, 'Medicine', 'Edit', 'medicine.edit', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(44, 'Medicine', 'Update', 'medicine.update', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(45, 'Medicine', 'Delete', 'medicine.destroy', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(46, 'Medicine', 'Import', 'medicine.import', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(47, 'Medicine', 'CSV Export', 'medicine.csv.export', 'web', '2024-05-10 04:01:55', '2024-05-10 04:01:55'),
(48, 'Purchase', 'Index', 'purchase.index', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(49, 'Purchase', 'Create', 'purchase.create', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(50, 'Purchase', 'Store', 'purchase.store', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(51, 'Purchase', 'Show', 'purchase.show', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(52, 'Purchase', 'Edit', 'purchase.edit', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(53, 'Purchase', 'Update', 'purchase.update', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(54, 'Purchase', 'Delete', 'purchase.destroy', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(55, 'Sale', 'Index', 'sale.index', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(56, 'Sale', 'Create', 'sale.create', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(57, 'Sale', 'Store', 'sale.store', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(58, 'Sale', 'Show', 'sale.show', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(59, 'Sale', 'Edit', 'sale.edit', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(60, 'Sale', 'Update', 'sale.update', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(61, 'Sale', 'Delete', 'sale.destroy', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(62, 'Payment Method', 'Index', 'paymentmethod.index', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(63, 'Payment Method', 'Create', 'paymentmethod.create', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(64, 'Payment Method', 'Store', 'paymentmethod.store', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(65, 'Payment Method', 'Edit', 'paymentmethod.edit', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(66, 'Payment Method', 'Update', 'paymentmethod.update', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(67, 'Payment Method', 'Delete', 'paymentmethod.destroy', 'web', '2024-05-10 04:01:56', '2024-05-10 04:01:56'),
(68, 'Medicine Stock', 'Instock', 'report.instock', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(69, 'Medicine Stock', 'Low Stock', 'report.low_stock', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(70, 'Medicine Stock', 'Stockout', 'report.stockout', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(71, 'Medicine Stock', 'Upcoming Expired', 'report.upcoming_expire', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(72, 'Medicine Stock', 'Already Expired', 'report.already_expire', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(73, 'Reports', 'Due Customer', 'report.due_customer', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(74, 'Reports', 'Payable Manufacturer', 'report.payable_manufacturer', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(75, 'Reports', 'Sale Purchase', 'report.sale_purchase', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(76, 'Reports', 'Profit Loss', 'report.profit_loss', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(77, 'Doctor', 'Index', 'doctor.index', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(78, 'Doctor', 'Create', 'doctor.create', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(79, 'Doctor', 'Store', 'doctor.store', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(80, 'Doctor', 'Edit', 'doctor.edit', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(81, 'Doctor', 'Update', 'doctor.update', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(82, 'Doctor', 'Delete', 'doctor.destroy', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(83, 'Patient', 'Index', 'patient.index', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(84, 'Patient', 'Create', 'patient.create', 'web', '2024-05-10 04:01:57', '2024-05-10 04:01:57'),
(85, 'Patient', 'Store', 'patient.store', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(86, 'Patient', 'Edit', 'patient.edit', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(87, 'Patient', 'Update', 'patient.update', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(88, 'Patient', 'Delete', 'patient.destroy', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(89, 'Test', 'Index', 'test.index', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(90, 'Test', 'Create', 'test.create', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(91, 'Test', 'Store', 'test.store', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(92, 'Test', 'Edit', 'test.edit', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(93, 'Test', 'Update', 'test.update', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(94, 'Test', 'Delete', 'test.destroy', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(95, 'Prescription', 'Index', 'prescription.index', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(96, 'Prescription', 'Create', 'prescription.create', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(97, 'Prescription', 'Store', 'prescription.store', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(98, 'Prescription', 'Show', 'prescription.show', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(99, 'Prescription', 'Delete', 'prescription.destroy', 'web', '2024-05-10 04:01:58', '2024-05-10 04:01:58'),
(100, 'Language', 'Index', 'language.index', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(101, 'Language', 'Create', 'language.create', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(102, 'Language', 'Store', 'language.store', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(103, 'Language', 'Delete', 'language.destroy', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(104, 'Expense Category', 'Index', 'expense-categories.index', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(105, 'Expense Category', 'Create', 'expense-categories.create', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(106, 'Expense Category', 'Store', 'expense-categories.store', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(107, 'Expense Category', 'Update', 'expense-categories.update', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(108, 'Expense Category', 'Delete', 'expense-categories.destroy', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(109, 'Expense', 'Index', 'expenses.index', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(110, 'Expense', 'Create', 'expenses.create', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(111, 'Expense', 'Store', 'expenses.store', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(112, 'Expense', 'Update', 'expenses.update', 'web', '2024-05-10 04:01:59', '2024-05-10 04:01:59'),
(113, 'Expense', 'Delete', 'expenses.destroy', 'web', '2024-05-10 04:02:00', '2024-05-10 04:02:00'),
(114, 'Setting', 'General Setting', 'setting.generalSetting', 'web', '2024-05-10 04:02:00', '2024-05-10 04:02:00'),
(115, 'Setting', 'Email Setting', 'email.update', 'web', '2024-05-10 04:02:00', '2024-05-10 04:02:00');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pharmacy_expenses`
--

CREATE TABLE `pharmacy_expenses` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `title` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `account_id` bigint UNSIGNED DEFAULT NULL,
  `amount` decimal(8,2) NOT NULL DEFAULT '0.00',
  `reference` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` bigint UNSIGNED NOT NULL,
  `prescription_no` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `visit_no` int NOT NULL,
  `referred_to` bigint UNSIGNED NOT NULL,
  `visit_fees` decimal(8,2) NOT NULL DEFAULT '0.00',
  `tests` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `medicines` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `advice` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `prescribed_by` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `upazilla_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `union_id` int DEFAULT NULL,
  `thana_id` int DEFAULT NULL,
  `total_price` double(20,2) NOT NULL,
  `due_price` double(20,2) NOT NULL,
  `date` date DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `qty` int NOT NULL,
  `inv_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `shops` int DEFAULT NULL,
  `subtotal` double(20,2) NOT NULL DEFAULT '0.00',
  `discount` double(20,2) NOT NULL DEFAULT '0.00',
  `medicines` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `method_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_medicines`
--

CREATE TABLE `purchase_medicines` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `amount` double(20,2) NOT NULL DEFAULT '0.00',
  `purchase_id` int NOT NULL DEFAULT '0',
  `supplier_id` int NOT NULL DEFAULT '0',
  `medicine_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_pays`
--

CREATE TABLE `purchase_pays` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date DEFAULT NULL,
  `shop_id` int NOT NULL,
  `amount` double(20,2) NOT NULL DEFAULT '0.00',
  `invoice_id` int NOT NULL DEFAULT '0',
  `supplier_id` int NOT NULL DEFAULT '0',
  `method_id` int NOT NULL,
  `purchase_id` int DEFAULT NULL,
  `medicines` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date DEFAULT NULL,
  `purchae_id` varchar(191) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `batch_id` int NOT NULL,
  `medicnes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `amount` double(20,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL,
  `shop_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` bigint UNSIGNED NOT NULL,
  `date` date DEFAULT NULL,
  `inv_id` varchar(191) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `batch_id` int NOT NULL,
  `medicnes` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `amount` double(20,2) NOT NULL DEFAULT '0.00',
  `quantity` int NOT NULL,
  `shop_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_name` varchar(125) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(125) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'Super Admin', 'web', '2024-05-10 15:02:34', '2024-05-10 15:02:34');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `shop_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shops`
--

CREATE TABLE `shops` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `site_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf32 COLLATE utf32_german2_ci,
  `currency` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL DEFAULT 'Taka',
  `division_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `upazilla_id` int DEFAULT NULL,
  `union_id` int DEFAULT NULL,
  `thana_id` int DEFAULT NULL,
  `package_id` int NOT NULL DEFAULT '0',
  `next_pay` date DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `prefix` varchar(11) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `theme` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'dark',
  `language` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `copyright_text` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `currency_model` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `shop_id` int DEFAULT NULL,
  `email` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `username` varchar(5000) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `currency_symbol_position` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `system_default_currency` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `image` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `status` int NOT NULL DEFAULT '0',
  `tawk` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `last_renew` date DEFAULT NULL,
  `upcoming_expire_alert` int NOT NULL DEFAULT '7',
  `low_stock_alert` int NOT NULL DEFAULT '0',
  `time_zone` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shops`
--

INSERT INTO `shops` (`id`, `name`, `site_title`, `site_logo`, `favicon`, `address`, `currency`, `division_id`, `district_id`, `upazilla_id`, `union_id`, `thana_id`, `package_id`, `next_pay`, `phone`, `prefix`, `theme`, `language`, `copyright_text`, `currency_model`, `shop_id`, `email`, `username`, `currency_symbol_position`, `system_default_currency`, `image`, `status`, `tawk`, `last_renew`, `upcoming_expire_alert`, `low_stock_alert`, `time_zone`, `created_at`, `updated_at`) VALUES
(1, 'Your Pharmacy', 'Pharmacy Software Solutions', '2022-12-14-6399babed3167.png', '2022-12-14-6399baca12a3a.png', 'Dhaka Bangladesh Ban', '$', 1, 1, 1, 1, 5, 4, '2030-02-24', '01973198574', 'INV', 'light', NULL, 'All Rights Reserved @Ayaan Pharma', NULL, NULL, 'admin@ayaantec.com', 'ayaantech', NULL, NULL, NULL, 1, NULL, NULL, 7, 0, NULL, NULL, '2022-12-14 12:00:10');

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `id` bigint UNSIGNED NOT NULL,
  `qty` int NOT NULL DEFAULT '0',
  `medicine_id` int NOT NULL,
  `shop_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due` double(20,2) NOT NULL DEFAULT '0.00',
  `address` text CHARACTER SET latin1 COLLATE latin1_swedish_ci,
  `upazilla_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `thana_id` int DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `union_id` int DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `global` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tests`
--

CREATE TABLE `tests` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `center` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tranactions`
--

CREATE TABLE `tranactions` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `upazilla_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `union_id` int DEFAULT NULL,
  `total_price` double(20,2) NOT NULL,
  `due_price` double(20,2) NOT NULL,
  `date` date DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `method_id` int DEFAULT NULL,
  `purchase_id` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `tran_id` varchar(199) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `debit_account_id` bigint UNSIGNED DEFAULT NULL,
  `credit_account_id` bigint UNSIGNED DEFAULT NULL,
  `amount` double NOT NULL,
  `invoice_type` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_id` varchar(199) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `particular` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `global` int DEFAULT '0',
  `status` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int NOT NULL DEFAULT '0',
  `global` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shop_id` int NOT NULL,
  `role_id` int NOT NULL DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `image` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `shop_id`, `role_id`, `last_login`, `image`, `created_at`, `updated_at`) VALUES
(3, 'Your Name', 'admin@pharmacyms.com', NULL, '$2y$10$izMVIs2eqDbd5TMc7n5WJ.HK/6xA57rOWcIVzZQyKvCW5Cp4Dy5SO', '9VR3EDZmQWHr7cAK4fYihuewgTtwTz0FIopyX4GZjKDqWZzmj9KHfFGMJ372', 1, 1, NULL, '2022-12-14-6399bb1ced091.jpg', '2022-02-07 20:44:06', '2022-12-14 12:01:33');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` bigint UNSIGNED NOT NULL,
  `shop_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `upazilla_id` int DEFAULT NULL,
  `district_id` int DEFAULT NULL,
  `thana_id` int DEFAULT NULL,
  `division_id` int DEFAULT NULL,
  `union_id` int DEFAULT NULL,
  `global` int NOT NULL DEFAULT '0',
  `due` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payable` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `accounts_account_type_id_foreign` (`account_type_id`);

--
-- Indexes for table `account_types`
--
ALTER TABLE `account_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `balances`
--
ALTER TABLE `balances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `changes`
--
ALTER TABLE `changes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `incomes`
--
ALTER TABLE `incomes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_medicines`
--
ALTER TABLE `invoice_medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoice_pays`
--
ALTER TABLE `invoice_pays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `languages`
--
ALTER TABLE `languages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `methods`
--
ALTER TABLE `methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pharmacy_expenses`
--
ALTER TABLE `pharmacy_expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pharmacy_expenses_category_id_foreign` (`category_id`),
  ADD KEY `pharmacy_expenses_account_id_foreign` (`account_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prescriptions_prescription_no_unique` (`prescription_no`),
  ADD KEY `prescriptions_patient_id_foreign` (`patient_id`),
  ADD KEY `prescriptions_referred_to_foreign` (`referred_to`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_medicines`
--
ALTER TABLE `purchase_medicines`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_pays`
--
ALTER TABLE `purchase_pays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shops`
--
ALTER TABLE `shops`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tests`
--
ALTER TABLE `tests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tests_name_unique` (`name`);

--
-- Indexes for table `tranactions`
--
ALTER TABLE `tranactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transactions_tran_id_unique` (`tran_id`),
  ADD KEY `transactions_debit_account_id_foreign` (`debit_account_id`),
  ADD KEY `transactions_credit_account_id_foreign` (`credit_account_id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `account_types`
--
ALTER TABLE `account_types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `balances`
--
ALTER TABLE `balances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `changes`
--
ALTER TABLE `changes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `incomes`
--
ALTER TABLE `incomes`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_medicines`
--
ALTER TABLE `invoice_medicines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoice_pays`
--
ALTER TABLE `invoice_pays`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `languages`
--
ALTER TABLE `languages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `methods`
--
ALTER TABLE `methods`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=116;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pharmacy_expenses`
--
ALTER TABLE `pharmacy_expenses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_medicines`
--
ALTER TABLE `purchase_medicines`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_pays`
--
ALTER TABLE `purchase_pays`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_returns`
--
ALTER TABLE `purchase_returns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shops`
--
ALTER TABLE `shops`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `stocks`
--
ALTER TABLE `stocks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tests`
--
ALTER TABLE `tests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tranactions`
--
ALTER TABLE `tranactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_account_type_id_foreign` FOREIGN KEY (`account_type_id`) REFERENCES `account_types` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_credit_account_id_foreign` FOREIGN KEY (`credit_account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `transactions_debit_account_id_foreign` FOREIGN KEY (`debit_account_id`) REFERENCES `accounts` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
