<?php
/**
 * Created by PhpStorm.
 * User: rana
 * Date: 7/1/18
 * Time: 1:04 PM
 */

/*
 * Common Constant
 */
const LANGUAGE_DEFAULT = 'en';
const TIMEZONE_DEFAULT = '+GMT';
const ITEM_PER_PAGE = 10;

/*
 * Constant user and role IDs
 */
const USER_ROLE_SUPER_ADMIN = 1;
const USER_ROLE_USER = 2;
const USER_ROLE_SELLER = 3;

const FIXED_USER_SUPER_ADMIN = 1;

/*
 * Response from services
 * used only for return
 */

const SERVICE_RESPONSE_STATUS = 'status';
const SERVICE_RESPONSE_MESSAGE = 'message';
const SERVICE_RESPONSE_DATA = 'data';

const SERVICE_RESPONSE_SUCCESS = 'success';
const SERVICE_RESPONSE_WARNING = 'warning';
const SERVICE_RESPONSE_ERROR = 'error';

/*
 * Route Configuration file
 */

const ROUTE_SECTION_USER_MANAGEMENTS = 'user_managements';
const ROUTE_SECTION_SELLER_MANAGEMENTS = 'seller_managements';
const ROUTE_SECTION_APPLICATION_MANAGEMENTS = 'application_managements';
const ROUTE_SECTION_GLOBAL_ACCESS_MANAGEMENT = 'global_access_managements';
const ROUTE_SECTION_ADMIN_AUCTION_MANAGEMENTS = 'auction_managements_(admin)';

const ROUTE_SUB_SECTION_USERS = 'users';
const ROUTE_SUB_SECTION_SELLER_PROFILE = 'sellers';
const ROUTE_SUB_SECTION_SELLER_MANAGE_PROFILE = 'store';
const ROUTE_SUB_SECTION_HOME = 'home';
const ROUTE_SUB_SECTION_USERS_AUCTION_MANAGEMENT = 'auction_management';
const ROUTE_SUB_SECTION_USERS_VERIFICATION = 'users_verifications';
const ROUTE_SUB_SECTION_SELLER_VERIFICATION = 'seller_verifications';
const ROUTE_SUB_SECTION_SELLER_ADDRESSES = 'seller_addresses';
const ROUTE_SUB_SECTION_SELLER_AUCTION = 'seller_auction';
const ROUTE_SUB_SECTION_USERS_FRONTEND_PROFILE = 'users_profile_frontend';
const ROUTE_SUB_SECTION_USERS_ADDRESSES = 'users_addresses';
const ROUTE_SUB_SECTION_USERS_DEPOSIT_MANAGEMENTS = 'deposit_managements';
const ROUTE_SUB_SECTION_USERS_TRANSACTION_MANAGEMENTS = 'transaction_managements';
const ROUTE_SUB_SECTION_USERS_WITHDRAWAL_MANAGEMENTS = 'withdrawal_managements';
const ROUTE_SUB_SECTION_USERS_CURRENCY_MANAGEMENTS = 'currency_managements';
const ROUTE_SUB_SECTION_USERS_BID_MANAGEMENTS = 'bid_managements';
const ROUTE_SUB_SECTION_USERS_COMMENT_MANAGEMENTS = 'comment_managements';
const ROUTE_SUB_SECTION_USERS_SHIPPING_DESCRIPTION_MANAGEMENTS = 'shipping_description_managements';
const ROUTE_SUB_SECTION_USERS_NOTIFICATION_MANAGEMENTS = 'notification_managements';
const ROUTE_SUB_SECTION_REPORT_CREATION = 'report_managements';
const ROUTE_SUB_SECTION_ROLE_MANAGEMENTS = 'role_managements';
const ROUTE_SUB_SECTION_APPLICATION_SETTINGS = 'settings';

const ROUTE_SUB_SECTION_AUCTION_MANAGEMENTS = 'auction_managements';
const ROUTE_SUB_SECTION_TRANSACTION_MANAGEMENTS = 'transaction_managements';
const ROUTE_SUB_SECTION_CATEGORY_MANAGEMENTS = 'category_managements';
const ROUTE_SUB_SECTION_CURRENCY_MANAGEMENTS = 'currency_managements';
const ROUTE_SUB_SECTION_PAYMENT_METHOD_MANAGEMENTS = 'payment_method_managements';
const ROUTE_SUB_SECTION_KNOW_YOUR_CUSTOMER_MANAGEMENTS = 'know_your_customer_managements';
const ROUTE_SUB_SECTION_REPORT_MANAGEMENTS = 'report_managements';
const ROUTE_SUB_SECTION_SLIDER_MANAGEMENTS = 'slider_managements';
const ROUTE_SUB_SECTION_LAYOUT_MANAGEMENTS = 'layout_managements';

const ROUTE_GROUP_READER_ACCESS = 'reader_access';
const ROUTE_GROUP_CREATION_ACCESS = 'creation_access';
const ROUTE_GROUP_MODIFIER_ACCESS = 'modifier_access';
const ROUTE_GROUP_DELETION_ACCESS = 'deletion_access';
const ROUTE_GROUP_FULL_ACCESS = 'full_access';

const ROUTE_TYPE_AVOIDABLE_MAINTENANCE = 'avoidable_maintenance_routes';
const ROUTE_TYPE_AVOIDABLE_UNVERIFIED = 'avoidable_unverified_routes';
const ROUTE_TYPE_AVOIDABLE_SUSPENDED = 'avoidable_suspended_routes';
const ROUTE_TYPE_FINANCIAL = 'financial_routes';
const ROUTE_TYPE_PRIVATE = 'private_routes';

const ROUTE_CONSTANT_PERMISSION = 'route_constant_permission';
const ROUTE_MUST_ACCESSIBLE = 'route_must_accessible';
const ROUTE_NOT_ACCESSIBLE = 'route_not_accessible';

/*
 * All route redirection
 */

const ROUTE_REDIRECT_TO_UNAUTHORIZED = '401';
const ROUTE_REDIRECT_TO_UNDER_MAINTENANCE = 'under_maintenance';
const ROUTE_REDIRECT_TO_EMAIL_UNVERIFIED = 'email_unverified';
const ROUTE_REDIRECT_TO_ACCOUNT_SUSPENDED = 'account_suspended';
const ROUTE_REDIRECT_TO_FINANCIAL_ACCOUNT_SUSPENDED = 'financial_account_suspended';
const REDIRECT_ROUTE_TO_USER_AFTER_LOGIN = 'profile.index';
const REDIRECT_ROUTE_TO_FRONTEND_USER_AFTER_LOGIN = 'user-profile.index';
const REDIRECT_ROUTE_TO_LOGIN = 'login';

/*
 * All Status
 */
const UNDER_MAINTENANCE_MODE_ACTIVE = 1;
const UNDER_MAINTENANCE_MODE_INACTIVE = 0;

const UNDER_MAINTENANCE_ACCESS_ACTIVE = 1;
const UNDER_MAINTENANCE_ACCESS_INACTIVE = 0;

const ACTIVE_STATUS_ACTIVE = 1;
const ACTIVE_STATUS_INACTIVE = 0;

const FINANCIAL_STATUS_ACTIVE = 1;
const FINANCIAL_STATUS_INACTIVE = 0;

const EMAIL_VERIFICATION_STATUS_ACTIVE = 1;
const EMAIL_VERIFICATION_STATUS_INACTIVE = 0;

const USER_STATUS_INACTIVE = 0;
const USER_STATUS_ACTIVE = 1;
const USER_STATUS_DELETED = -1;

/*
 * Auction related constants start here
 */

const AUCTION_FEE_IN_PERCENT = 1;
const AUCTION_FEE_IN_FIXED_AMOUNT = 2;
const AUCTION_FEE_IN_BOTH_AMOUNT = 3;

const AUCTION_TYPE_HIGHEST_BIDDER = 1;
const AUCTION_TYPE_BLIND_BIDDER = 2;
const AUCTION_TYPE_UNIQUE_BIDDER = 3;
const AUCTION_TYPE_VICKREY_AUCTION = 4;

const SHIPPING_TYPE_PAID = 0;
const SHIPPING_TYPE_FREE = 1;

const AUCTION_STATUS_RUNNING = 1;
const AUCTION_STATUS_COMPLETED = 2;

const AUCTION_WINNER_STATUS_LOSE = 0;
const AUCTION_WINNER_STATUS_WIN = 1;

const AUCTION_PRODUCT_CLAIM_STATUS_NOT_DELIVERED_YET = 0;
const AUCTION_PRODUCT_CLAIM_STATUS_ON_SHIPPING = 1;
const AUCTION_PRODUCT_CLAIM_STATUS_DISPUTED = 2;
const AUCTION_PRODUCT_CLAIM_STATUS_PENDING = 3;
const AUCTION_PRODUCT_CLAIM_STATUS_DELIVERED = 4;

const DISPUTE_TYPE_AUCTION_ISSUE = 1;
const DISPUTE_TYPE_SELLER_ISSUE = 2;
const DISPUTE_TYPE_TRANSACTION_ISSUE = 3;
const DISPUTE_TYPE_SHIPPING_ISSUE = 4;
const DISPUTE_TYPE_OTHER_ISSUE = 5;

const DISPUTE_STATUS_PENDING = 0;
const DISPUTE_STATUS_ON_INVESTIGATION = 1;
const DISPUTE_STATUS_SOLVED = 2;

const IDENTIFICATION_TYPE_WITH_ID_NID = 1;
const IDENTIFICATION_TYPE_WITH_ID_DRIVING_LICENSE = 2;
const IDENTIFICATION_TYPE_WITH_ID_PASSPORT = 3;

const IDENTIFICATION_TYPE_WITH_ADDRESS_UTILITY_BILL = 4;
const IDENTIFICATION_TYPE_WITH_ADDRESS_BANK_STATEMENT = 5;

const VERIFICATION_STATUS_UNVERIFIED = 0;
const VERIFICATION_STATUS_APPROVED = 1;
const VERIFICATION_STATUS_PENDING = 2;

const VERIFICATION_TYPE_ADDRESS = 1;
const VERIFICATION_TYPE_ID = 2;


//Transaction constant

const PAYMENT_METHOD_PAYPAL = 1;

const TRANSACTION_TYPE_DEPOSIT = 1;
const TRANSACTION_TYPE_WITHDRAWAL = 2;
const TRANSACTION_TYPE_SYSTEM_FEE = 3;
const TRANSACTION_TYPE_AUCTION_EXPENSE = 4;
const TRANSACTION_TYPE_EARNING = 5;

const PAYMENT_STATUS_CANCELED = 1;
const PAYMENT_STATUS_FAILED = 2;
const PAYMENT_STATUS_PENDING = 3;
const PAYMENT_STATUS_COMPLETED = 4;

const CURRENCY_TYPE_USD = 'USD';

const JOURNAL_TYPE_DEBIT = 1;
const JOURNAL_TYPE_CREDIT = 2;

//Deposit
const DECREASED_FROM_OUTSIDE_AS_DEPOSIT = 1;
const INCREASED_TO_USER_WALLET_AS_DEPOSIT_CONFIRMATION = 2;

//Withdrawal
const DECREASED_FROM_USER_WALLET_AS_WITHDRAWAL = 3;
const INCREASED_TO_OUTSIDE_AS_WITHDRAWAL_CONFIRMATION = 4;

const DECREASED_FROM_USER_WALLET_AS_WITHDRAWAL_FEES = 5;
const INCREASED_TO_SYSTEM_WALLET_AS_WITHDRAWAL_FEES = 6;

//Auctions fees
const DECREASED_FROM_USER_WALLET_AS_AUCTION_FEES = 7;
const INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES = 8;

//Bidding amount
const DECREASED_FROM_USER_WALLET_TO_ESCROW_ON_BIDDING = 9;
const INCREASED_TO_ESCROW_ON_BIDDING_FROM_USER_WALLET = 10;

//Auction completion amount
const DECREASED_FROM_ESCROW_TO_SELLER_WALLET_ON_AUCTION_COMPLETION = 11;
const INCREASED_TO_SELLER_WALLET_FROM_ESCROW_ON_AUCTION_COMPLETION = 12;

//Auction completion fee
const DECREASED_FROM_SELLER_WALLET_TO_SYSTEM_WALLET_AS_SELLING_FEE = 13;
const INCREASED_TO_SYSTEM_WALLET_FROM_SELLER_WALLET_AS_SELLING_FEE = 14;

//Bidding amount reversal
const INCREASED_TO_USER_WALLET_FROM_ESCROW_ON_BIDDING_REVERSAL = 15;
const DECREASED_FROM_ESCROW_TO_USER_WALLET_ON_BIDDING_REVERSAL = 16;

//Bidding fees
const DECREASED_FROM_USER_WALLET_TO_SYSTEM_WALLET_AS_BIDDING_FEES = 17;
const INCREASED_TO_SYSTEM_WALLET_FROM_USER_WALLET_AS_BIDDING_FEES = 18;

//auction layout types
const AUCTION_LAYOUT_TYPE_RECENT_AUCTION = 1;
const AUCTION_LAYOUT_TYPE_POPULAR_AUCTION = 2;
const AUCTION_LAYOUT_TYPE_HIGHEST_BIDDER_AUCTION = 3;
const AUCTION_LAYOUT_TYPE_BLIND_BIDDER_AUCTION = 4;
const AUCTION_LAYOUT_TYPE_UNIQUE_BIDDER_AUCTION = 5;
const AUCTION_LAYOUT_TYPE_VICKREY_BIDDER_AUCTION = 6;
const AUCTION_LAYOUT_TYPE_LOWEST_PRICE_AUCTION = 7;
const AUCTION_LAYOUT_TYPE_HIGHEST_PRICE_AUCTION = 8;

// paypal webhook event types
const PAYPAL_PAYMENT_PAYOUTS_ITEM_BLOCKED = "PAYMENT.PAYOUTS-ITEM.BLOCKED";
const PAYPAL_PAYMENT_PAYOUTS_ITEM_CANCELED = "PAYMENT.PAYOUTS-ITEM.CANCELED";
const PAYPAL_PAYMENT_PAYOUTS_ITEM_DENIED = "PAYMENT.PAYOUTS-ITEM.DENIED";
const PAYPAL_PAYMENT_PAYOUTS_ITEM_FAILED = "PAYMENT.PAYOUTS-ITEM.FAILED";
const PAYPAL_PAYMENT_PAYOUTS_ITEM_HELD = "PAYMENT.PAYOUTS-ITEM.HELD";
const PAYPAL_PAYMENT_PAYOUTS_ITEM_REFUNDED = "PAYMENT.PAYOUTS-ITEM.REFUNDED";
const PAYPAL_PAYMENT_PAYOUTS_ITEM_RETURNED = "PAYMENT.PAYOUTS-ITEM.RETURNED";
const PAYPAL_PAYMENT_PAYOUTS_ITEM_SUCCEEDED = "PAYMENT.PAYOUTS-ITEM.SUCCEEDED";
const PAYPAL_PAYMENT_SALE_COMPLETED = "PAYMENT.SALE.COMPLETED";
const PAYPAL_PAYMENT_SALE_DENIED = "PAYMENT.SALE.DENIED";
const PAYPAL_PAYMENT_SALE_REFUNDED = "PAYMENT.SALE.REFUNDED";
const PAYPAL_PAYMENT_SALE_REVERSED = "PAYMENT.SALE.REVERSED";

const PAYPAL_WEBHOOK_VALIDATION_SUCCESS = "SUCCESS";
const PAYPAL_WEBHOOK_VALIDATION_FAILUR = "FAILURE";
