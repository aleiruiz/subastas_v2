<?php
/**
 * Created by PhpStorm.
 * User: zahid
 * Date: 2018-07-31
 * Time: 2:24 PM
 */
if (!function_exists('maintenance_status')) {
    function maintenance_status($input = null)
    {
        $output = [
            UNDER_MAINTENANCE_MODE_ACTIVE => __('Enabled'),
            UNDER_MAINTENANCE_MODE_INACTIVE => __('Disabled')
        ];
        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('email_status')) {
    function email_status($input = null)
    {
        $output = [
            EMAIL_VERIFICATION_STATUS_ACTIVE => __('Verified'),
            EMAIL_VERIFICATION_STATUS_INACTIVE => __('Unverified')
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('financial_status')) {
    function financial_status($input = null)
    {
        $output = [
            FINANCIAL_STATUS_ACTIVE => __('Active'),
            FINANCIAL_STATUS_INACTIVE => __('Suspended')
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('maintenance_accessible_status')) {
    function maintenance_accessible_status($input = null)
    {
        $output = [
            UNDER_MAINTENANCE_ACCESS_ACTIVE => __('Active'),
            UNDER_MAINTENANCE_ACCESS_INACTIVE => __('Inactive')
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('account_status')) {
    function account_status($input = null)
    {
        $output = [
            USER_STATUS_ACTIVE => __('Active'),
            USER_STATUS_INACTIVE => __('Suspended'),
            USER_STATUS_DELETED => __('Deleted')
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('active_status')) {
    function active_status($input = null)
    {
        $output = [
            ACTIVE_STATUS_ACTIVE => __('Active'),
            ACTIVE_STATUS_INACTIVE => __('Inactive'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('is_primary')) {
    function is_primary($input = null)
    {
        $output = [
            ACTIVE_STATUS_ACTIVE => __('Primary'),
            ACTIVE_STATUS_INACTIVE => __('Inactive'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('api_permission')) {
    function api_permission($input = null)
    {
        $output = [
            ROUTE_REDIRECT_TO_UNAUTHORIZED => '401',
            ROUTE_REDIRECT_TO_UNDER_MAINTENANCE => 'under_maintenance',
            ROUTE_REDIRECT_TO_EMAIL_UNVERIFIED => 'email_unverified',
            ROUTE_REDIRECT_TO_ACCOUNT_SUSPENDED => 'account_suspension',
            ROUTE_REDIRECT_TO_FINANCIAL_ACCOUNT_SUSPENDED => 'financial_suspension',
            REDIRECT_ROUTE_TO_USER_AFTER_LOGIN => 'login_success',
            REDIRECT_ROUTE_TO_LOGIN => 'login',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('language_switcher_items')) {
    function language_switcher_items($input = null)
    {
        $output = [
            'name' => __('Name'),
            'short_code' => __('Short Code'),
            'icon' => __('Icon')
        ];
        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('layout_types')) {
    function layout_types($input = null)
    {
        $output = [
            AUCTION_LAYOUT_TYPE_RECENT_AUCTION => __('Recent Auction'),
            AUCTION_LAYOUT_TYPE_POPULAR_AUCTION => __('Popular Auction'),
            AUCTION_LAYOUT_TYPE_HIGHEST_BIDDER_AUCTION => __('Highest Bidder Auction'),
            AUCTION_LAYOUT_TYPE_BLIND_BIDDER_AUCTION => __('Blind Bidder Auction'),
            AUCTION_LAYOUT_TYPE_UNIQUE_BIDDER_AUCTION => __('Unique Bidder Auction'),
            AUCTION_LAYOUT_TYPE_VICKREY_BIDDER_AUCTION => __('Vickrey Bidder Auction'),
            AUCTION_LAYOUT_TYPE_LOWEST_PRICE_AUCTION => __('Lowest Price Auction'),
            AUCTION_LAYOUT_TYPE_HIGHEST_PRICE_AUCTION => __('Highest Price Auction'),
        ];
        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('get_layout_function')) {
    function get_layout_function($input = null)
    {
        $output = [
            AUCTION_LAYOUT_TYPE_RECENT_AUCTION => 'getRecentAuction',
            AUCTION_LAYOUT_TYPE_POPULAR_AUCTION => 'getPopularAuction',

            AUCTION_LAYOUT_TYPE_HIGHEST_BIDDER_AUCTION => 'highestBidderAuction',
            AUCTION_LAYOUT_TYPE_BLIND_BIDDER_AUCTION => 'blindBidderAuction',
            AUCTION_LAYOUT_TYPE_UNIQUE_BIDDER_AUCTION => 'uniqueBidderAuction',
            AUCTION_LAYOUT_TYPE_VICKREY_BIDDER_AUCTION => 'vickeryBidderAuction',

            AUCTION_LAYOUT_TYPE_LOWEST_PRICE_AUCTION => 'lowestPriceAuction',
            AUCTION_LAYOUT_TYPE_HIGHEST_PRICE_AUCTION => 'highestPriceAuction',
        ];
        return is_null($input) ? $output : (isset($output[$input]) ? $output[$input] : null);
    }
}

if (!function_exists('auction_fee_type')) {
    function auction_fee_type($input = null)
    {
        $output = [
            AUCTION_FEE_IN_PERCENT => __('In Percent'),
            AUCTION_FEE_IN_FIXED_AMOUNT => __('Fixed Amount'),
            AUCTION_FEE_IN_BOTH_AMOUNT => __('Both')
        ];
        return is_null($input) ? $output : $output[$input];
    }
}

/**
 * Created by PhpStorm.
 * User: dev-rob
 * Date: 2019-11-6
 * Time: 2:24 PM
 * Purpose: Pro Auction
 */

if (!function_exists('auction_type')) {
    function auction_type($input = null)
    {
        $output = [
            AUCTION_TYPE_HIGHEST_BIDDER => 'Highest Bid',
            AUCTION_TYPE_BLIND_BIDDER => 'Blind Bid',
            AUCTION_TYPE_UNIQUE_BIDDER => 'Unique Bid',
            AUCTION_TYPE_VICKREY_AUCTION => 'Vickrey Auction',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}
if (!function_exists('auction_type_slug')) {
    function auction_type_slug($input = null)
    {
        $output = [
            AUCTION_TYPE_HIGHEST_BIDDER => 'highest-bid',
            AUCTION_TYPE_BLIND_BIDDER => 'blind-bid',
            AUCTION_TYPE_UNIQUE_BIDDER => 'unique-bid',
            AUCTION_TYPE_VICKREY_AUCTION => 'vickrey-auction',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('get_auction_type_value')) {
    function get_auction_type_value($input = null)
    {
        $output = [
             'Highest Bid' => AUCTION_TYPE_HIGHEST_BIDDER,
             'Blind Bid' => AUCTION_TYPE_BLIND_BIDDER,
             'Unique Bid' => AUCTION_TYPE_UNIQUE_BIDDER,
             'Vickrey Auction' => AUCTION_TYPE_VICKREY_AUCTION,
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('auction_status')) {
    function auction_status($input = null)
    {
        $output = [
            AUCTION_STATUS_RUNNING => 'Running',
            AUCTION_STATUS_COMPLETED => 'Completed',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('shipping_type')) {
    function shipping_type($input = null)
    {
        $output = [
            SHIPPING_TYPE_FREE => 'Free',
            SHIPPING_TYPE_PAID => 'Paid',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('is_multiple_bid_allowed')) {
    function is_multiple_bid_allowed($input = null)
    {
        $output = [
            ACTIVE_STATUS_INACTIVE => 'No',
            ACTIVE_STATUS_ACTIVE => 'Yes',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('product_claim_status')) {
    function product_claim_status($input = null)
    {
        $output = [
            AUCTION_PRODUCT_CLAIM_STATUS_NOT_DELIVERED_YET => 'Not Delivered',
            AUCTION_PRODUCT_CLAIM_STATUS_ON_SHIPPING => 'On Shipping',
            AUCTION_PRODUCT_CLAIM_STATUS_DISPUTED => 'Disputed',
            AUCTION_PRODUCT_CLAIM_STATUS_PENDING => 'Pending',
            AUCTION_PRODUCT_CLAIM_STATUS_DELIVERED => 'Delivered',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('dispute_type')) {
    function dispute_type($input = null)
    {
        $output = [
            DISPUTE_TYPE_AUCTION_ISSUE => __('Auction Issue'),
            DISPUTE_TYPE_SELLER_ISSUE => __('Seller Issue'),
            DISPUTE_TYPE_TRANSACTION_ISSUE => __('Transaction issue'),
            DISPUTE_TYPE_SHIPPING_ISSUE => __('Shipping issue'),
            DISPUTE_TYPE_OTHER_ISSUE => __('Other issue'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('dispute_status')) {
    function dispute_status($input = null)
    {
        $output = [
            DISPUTE_STATUS_PENDING => __('Pending'),
            DISPUTE_STATUS_ON_INVESTIGATION => __('On Investigation'),
            DISPUTE_STATUS_SOLVED => __('Solved'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('identification_type_with_id')) {
    function identification_type_with_id($input = null)
    {
        $output = [
            IDENTIFICATION_TYPE_WITH_ID_NID => __('National ID Card'),
            IDENTIFICATION_TYPE_WITH_ID_DRIVING_LICENSE => __('Driving License'),
            IDENTIFICATION_TYPE_WITH_ID_PASSPORT => __('Passport'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}
if (!function_exists('identification_type_with_address')) {
    function identification_type_with_address($input = null)
    {
        $output = [
            IDENTIFICATION_TYPE_WITH_ADDRESS_UTILITY_BILL => __('Utility Bill'),
            IDENTIFICATION_TYPE_WITH_ADDRESS_BANK_STATEMENT => __('Bank Statement'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('verification_type')) {
    function verification_type($input = null)
    {
        $output = [
            VERIFICATION_TYPE_ADDRESS => __('With Address'),
            VERIFICATION_TYPE_ID => __('With ID'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}
if (!function_exists('verification_status')) {
    function verification_status($input = null)
    {
        $output = [
            VERIFICATION_STATUS_UNVERIFIED => __('Unverified'),
            VERIFICATION_STATUS_APPROVED => __('Approved'),
            VERIFICATION_STATUS_PENDING => __('Pending'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}


//Transactions arrays

if (!function_exists('get_paypal_mode')) {
    function get_paypal_mode($key = null)
    {
        $modes = [
            'sandbox' => 'Sandbox',
            'live' => 'Live'
        ];

        return is_null($key) ? $modes : $modes[$key];
    }
}


if (!function_exists('journal_type')) {
    function journal_type($input = null)
    {
        $output = [
            JOURNAL_TYPE_DEBIT => __('Debit'),
            JOURNAL_TYPE_CREDIT => __('Credit'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('transaction_type')) {
    function transaction_type($input = null)
    {
        $output = [
            TRANSACTION_TYPE_DEPOSIT => __('Deposit'),
            TRANSACTION_TYPE_WITHDRAWAL => __('Withdrawal'),
            TRANSACTION_TYPE_SYSTEM_FEE => __('System Fee'),
            TRANSACTION_TYPE_AUCTION_EXPENSE => __('Auction Expense'),
            TRANSACTION_TYPE_EARNING => __('Earning'),
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('payment_methods')) {
    function payment_methods($input = null)
    {
        $output = [
            PAYMENT_METHOD_PAYPAL => __('Paypal')
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('payment_status')) {
    function payment_status($input = null)
    {
        $output = [
            PAYMENT_STATUS_PENDING  => __('Pending'),
            PAYMENT_STATUS_COMPLETED  => __('Completed'),
            PAYMENT_STATUS_CANCELED  => __('Canceled'),
            PAYMENT_STATUS_FAILED => __('Failed')
        ];

        return is_null($input) ? $output : $output[$input];
    }
}


if (!function_exists('payment_method_api')) {
    function payment_method_api($input = null)
    {
        $output = [
            PAYMENT_METHOD_PAYPAL => 'PaypalRestApi',
        ];

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('user_transaction_type')) {
    function user_transaction_type($input = null)
    {
        $output = [
            INCREASED_TO_USER_WALLET_AS_DEPOSIT_CONFIRMATION => __('Wallet deposit'),

            DECREASED_FROM_USER_WALLET_AS_WITHDRAWAL => __('Wallet withdrawal request'),
            DECREASED_FROM_USER_WALLET_AS_WITHDRAWAL_FEES => __('Withdrawal Fee'),

            DECREASED_FROM_USER_WALLET_AS_AUCTION_FEES => __('Auction expense'),
            DECREASED_FROM_USER_WALLET_TO_ESCROW_ON_BIDDING => __('Bidding Cost'),
            INCREASED_TO_ESCROW_ON_BIDDING_FROM_USER_WALLET => __('Deposit to Escrow'),
            DECREASED_FROM_ESCROW_TO_SELLER_WALLET_ON_AUCTION_COMPLETION => __('Escrow Auction Reversal'),
            INCREASED_TO_SELLER_WALLET_FROM_ESCROW_ON_AUCTION_COMPLETION => __('Auction Completion'),

            INCREASED_TO_USER_WALLET_FROM_ESCROW_ON_BIDDING_REVERSAL => __('Escrow fund release'),
            DECREASED_FROM_ESCROW_TO_USER_WALLET_ON_BIDDING_REVERSAL => __('On Bidding'),
            DECREASED_FROM_USER_WALLET_TO_SYSTEM_WALLET_AS_BIDDING_FEES => __('Winner earning fee'),
        ];

        return is_null($input) ? $output : $output[$input];

    }
}

if (!function_exists('system_transaction_type')) {
    function system_transaction_type($input = null)
    {
        $output = [
            DECREASED_FROM_OUTSIDE_AS_DEPOSIT => __('Outside deposit deduction'),

            INCREASED_TO_OUTSIDE_AS_WITHDRAWAL_CONFIRMATION => __('Withdrawal confirmation outside'),
            INCREASED_TO_SYSTEM_WALLET_AS_WITHDRAWAL_FEES => __('System withdrawal fees earning'),

            INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES => __('System auction fee earning'),
            DECREASED_FROM_SELLER_WALLET_TO_SYSTEM_WALLET_AS_SELLING_FEE => __('System selling fee earning'),

            INCREASED_TO_SYSTEM_WALLET_FROM_SELLER_WALLET_AS_SELLING_FEE => __('Earning from selling fee'),
            INCREASED_TO_SYSTEM_WALLET_FROM_USER_WALLET_AS_BIDDING_FEES => __('Earning from bidding fee'),
        ];

        $output = $output + user_transaction_type();
        ksort($output);

        return is_null($input) ? $output : $output[$input];
    }
}

if (!function_exists('paypal_payout_webhook_type')) {
    function paypal_payout_webhook_type($key = null)
    {
        $output = [
            PAYPAL_PAYMENT_PAYOUTS_ITEM_BLOCKED => "PAYMENT.PAYOUTS-ITEM.BLOCKED",
            PAYPAL_PAYMENT_PAYOUTS_ITEM_CANCELED => "PAYMENT.PAYOUTS-ITEM.CANCELED",
            PAYPAL_PAYMENT_PAYOUTS_ITEM_DENIED => "PAYMENT.PAYOUTS-ITEM.DENIED",
            PAYPAL_PAYMENT_PAYOUTS_ITEM_FAILED => "PAYMENT.PAYOUTS-ITEM.FAILED",
            PAYPAL_PAYMENT_PAYOUTS_ITEM_HELD => "PAYMENT.PAYOUTS-ITEM.HELD",
            PAYPAL_PAYMENT_PAYOUTS_ITEM_REFUNDED => "PAYMENT.PAYOUTS-ITEM.REFUNDED",
            PAYPAL_PAYMENT_PAYOUTS_ITEM_RETURNED => "PAYMENT.PAYOUTS-ITEM.RETURNED",
            PAYPAL_PAYMENT_PAYOUTS_ITEM_SUCCEEDED => "PAYMENT.PAYOUTS-ITEM.SUCCEEDED",
        ];

        return is_null($key) ? $output : $output[$key];
    }
}

if (!function_exists('paypal_payment_webhook_type')) {
    function paypal_payment_webhook_type($key = null)
    {
        $output = [
            PAYPAL_PAYMENT_SALE_COMPLETED => "PAYMENT.SALE.COMPLETED",
            PAYPAL_PAYMENT_SALE_DENIED => "PAYMENT.SALE.DENIED",
            PAYPAL_PAYMENT_SALE_REFUNDED => "PAYMENT.SALE.REFUNDED",
            PAYPAL_PAYMENT_SALE_REVERSED => "PAYMENT.SALE.REVERSED",
        ];

        return is_null($key) ? $output : $output[$key];
    }
}
