<?php
// English translations for Budgie

return [
    // Navigation
    'nav' => [
        'dashboard' => 'Dashboard',
        'accounts' => 'Accounts',
        'expenses' => 'Expenses',
        'incomes' => 'Incomes',
        'forecasts' => 'Forecasts',
        'sharing' => 'Sharing',
        'subscriptions' => 'Subscriptions',
        'profile' => 'Profile',
        'administration' => 'Administration',
        'logout' => 'Logout',
    ],

    // Dashboard
    'dashboard' => [
        'title' => 'Dashboard',
        'welcome' => 'Welcome',
        'overview' => 'Here\'s an overview of your financial situation.',
        'total_balance' => 'Total Balance',
        'monthly_expenses' => 'Monthly Expenses',
        'monthly_income' => 'Monthly Income',
        'monthly_savings' => 'Monthly Savings',
        'financial_evolution' => 'Financial Evolution',
        'account_distribution' => 'Account Distribution',
        'quick_actions' => 'Quick Actions',
        'recent_activity' => 'Recent Activity',
        'manage_accounts' => 'Manage Accounts',
        'add_expense' => 'Add Expense',
        'add_income' => 'Add Income',
        'view_forecasts' => 'View Forecasts',
        'type' => 'Type',
        'account' => 'Account',
        'expense' => 'Expense',
        'income' => 'Income',
        'current_account' => 'Current Account',
        'groceries' => 'Groceries',
        'salary' => 'Salary',
        'gas' => 'Gas',
        'restaurant' => 'Restaurant',
    ],

    // Accounts
    'accounts' => [
        'title' => 'Accounts',
        'add_account' => 'Add Account',
        'edit_account' => 'Edit Account',
        'delete_account' => 'Delete Account',
        'account_name' => 'Account Name',
        'account_type' => 'Account Type',
        'balance' => 'Balance',
        'actions' => 'Actions',
        'types' => [
            'bank' => 'Bank',
            'cash' => 'Cash',
            'credit' => 'Credit Card',
            'savings' => 'Savings',
            'investment' => 'Investment',
        ],
    ],

    // Expenses
    'expenses' => [
        'title' => 'Expenses',
        'add_expense' => 'Add Expense',
        'edit_expense' => 'Edit Expense',
        'delete_expense' => 'Delete Expense',
        'expense_name' => 'Expense Name',
        'description' => 'Description',
        'amount' => 'Amount',
        'frequency' => 'Frequency',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'account' => 'Account',
        'select_account' => 'Select an account',
        'select_frequency' => 'Select a frequency',
        'frequencies' => [
            'one_time' => 'One Time',
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
        ],
    ],

    // Incomes
    'incomes' => [
        'title' => 'Incomes',
        'add_income' => 'Add Income',
        'edit_income' => 'Edit Income',
        'delete_income' => 'Delete Income',
        'income_name' => 'Income Name',
        'description' => 'Description',
        'amount' => 'Amount',
        'frequency' => 'Frequency',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'account' => 'Account',
        'delete_confirm' => 'Are you sure you want to delete this income?',
    ],

    // Forecasts
    'forecasts' => [
        'title' => 'Forecasts',
        'period' => 'Period',
        'next_3_months' => 'Next 3 Months',
        'next_6_months' => 'Next 6 Months',
        'next_year' => 'Next Year',
        'projected_balance' => 'Projected Balance',
        'projected_expenses' => 'Projected Expenses',
        'projected_income' => 'Projected Income',
    ],

    // Sharing
    'sharing' => [
        'title' => 'Sharing',
        'share_account' => 'Share an Account',
        'account_to_share' => 'Account to Share',
        'select_account' => 'Select an account',
        'person_email' => 'Person\'s Email',
        'invitation_message' => 'Invitation Message (optional)',
        'message_placeholder' => 'Hello, I invite you to view my account...',
        'send_invitation' => 'Send Invitation',

        // Tables
        'accounts_i_share' => 'Accounts I\'m Sharing',
        'accounts_shared_with_me' => 'Accounts Shared With Me',
        'account' => 'Account',
        'shared_with' => 'Shared With',
        'owner' => 'Owner',
        'access_type' => 'Access Type',
        'share_date' => 'Share Date',
        'revoke' => 'Revoke',
        'revoke_confirm' => 'Are you sure you want to revoke access?',
        'view' => 'View',

        // Sharing Rules
        'sharing_rules' => 'Sharing Rules',
        'what_is_allowed' => 'What is Allowed',
        'what_is_prohibited' => 'What is Prohibited',
        'security' => 'Security',

        // Allowed
        'view_balances' => 'View Balances',
        'view_transactions' => 'View Transactions',
        'access_forecasts' => 'Access to Forecasts',
        'email_notifications' => 'Email Notifications',

        // Prohibited
        'modify_data' => 'Modify Data',
        'add_transactions' => 'Add Transactions',
        'delete_elements' => 'Delete Elements',
        'modify_settings' => 'Modify Settings',

        // Security
        'readonly_access' => 'Read-only Access Only',
        'email_invitation_required' => 'Email Invitation Required',
        'revocable_anytime' => 'Revocable Anytime',
        'access_traceability' => 'Access Traceability',

        // Recent Activity
        'recent_activity' => 'Recent Activity',
        'invitation_sent' => 'Invitation Sent',
        'access_revoked' => 'Access Revoked',
        'new_access_received' => 'New Access Received',
        'current_account' => 'Current Account',
        'family_account' => 'Family Account',
        'hours_ago' => ':hours hours ago',
        'days_ago' => ':days days ago',

        // Edit Modal
        'edit_share' => 'Edit Share',
        'readonly' => 'Read Only',
        'readwrite_coming' => 'Read/Write (coming soon)',
        'message_optional' => 'Message (optional)',
        'update' => 'Update',
        'access_revoked_success' => 'Access revoked successfully',
    ],

    // Subscriptions
    'subscriptions' => [
        'title' => 'Subscriptions',
        'current_subscription' => 'Current Subscription',
        'current_plan' => 'Current Plan',
        'monthly_price' => 'Monthly Price',
        'accounts_allowed' => 'Authorized Accounts',
        'expenses_per_account' => 'Expenses per Account',
        'next_billing' => 'Next Billing',
        'choose_plan' => 'Choose a Plan',
        'plan_free' => 'Free',
        'plan_gratuit' => 'Free',
        'plan_premium' => 'Premium',
        'per_month' => 'per month',
        'button_current' => 'Current Plan',
        'button_upgrade' => 'Upgrade to Premium',
        'already_using' => 'You are already using this plan',

        // Features
        'feature_free_0' => '2 accounts maximum',
        'feature_free_1' => '7 expenses per account',
        'feature_free_2' => '2 incomes per account',
        'feature_free_3' => 'Basic forecasts',
        'feature_free_4' => 'Email support',
        'feature_premium_0' => 'Unlimited accounts',
        'feature_premium_1' => 'Unlimited expenses',
        'feature_premium_2' => 'Unlimited incomes',
        'feature_premium_3' => 'Advanced forecasts',
        'feature_premium_4' => 'Account sharing',
        'feature_premium_5' => 'Priority support',
        'feature_premium_6' => 'Excel/PDF exports',
        'feature_premium_7' => 'API access',

        // Feature Comparison
        'feature_comparison' => 'Feature Comparison',
        'feature' => 'Feature',
        'number_of_accounts' => 'Number of Accounts',
        'incomes_per_account' => 'Incomes per Account',
        'forecasts' => 'Forecasts',
        'account_sharing' => 'Account Sharing',
        'exports' => 'Exports',
        'support' => 'Support',
        'api_access' => 'API Access',
        'unlimited' => 'Unlimited',
        'basic' => 'Basic',
        'advanced' => 'Advanced',
        'email' => 'Email',
        'priority' => 'Priority',

        // Billing
        'billing_history' => 'Billing History',
        'status' => 'Status',
        'paid' => 'Paid',
        'download' => 'Download',
        'month_january' => 'January',
        'month_december' => 'December',

        // Payment Method
        'payment_method' => 'Payment Method',
        'expires' => 'Expires',
        'update_payment_method' => 'Update Payment Method',
        'card_number' => 'Card Number',
        'expiry_date' => 'Expiry Date',
        'cvv' => 'CVV',
        'cardholder_name' => 'Cardholder Name',
        'update' => 'Update',

        // FAQ
        'faq' => 'Frequently Asked Questions',
        'faq_change_plan_q' => 'Can I change plans at any time?',
        'faq_change_plan_a' => 'Yes, you can switch from Free to Premium or vice versa at any time. Changes take effect immediately.',
        'faq_exceed_limits_q' => 'What happens if I exceed the Free plan limits?',
        'faq_exceed_limits_a' => 'You will receive a notification inviting you to upgrade to Premium to continue using all features.',
        'faq_cancel_q' => 'Can I cancel my subscription?',
        'faq_cancel_a' => 'Yes, you can cancel your subscription at any time. You will retain Premium access until the end of the billing period.',
        'faq_hidden_fees_q' => 'Are there any hidden fees?',
        'faq_hidden_fees_a' => 'No, the displayed price is the final price. No hidden or additional fees.',

        // Confirmation
        'confirm_upgrade' => 'Are you sure you want to upgrade to ',
    ],

    // Profile
    'profile' => [
        'title' => 'Profile',
        'personal_info' => 'Personal Information',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'change_password' => 'Change Password',
        'current_password' => 'Current Password',
        'new_password' => 'New Password',
        'confirm_password' => 'Confirm Password',
        'save_changes' => 'Save Changes',

        // Subscription
        'manage_subscription' => 'Manage Subscription',
        'upgrade_to_premium' => 'Upgrade to Premium',
        'unlock_features' => 'Unlock all features with an unlimited subscription!',
        'view_plans' => 'View Plans',

        // Statistics
        'account_stats' => 'Account Statistics',
        'accounts_created' => 'Accounts Created',
        'active_expenses' => 'Active Expenses',
        'active_incomes' => 'Active Incomes',
        'last_login' => 'Last Login',
        'first_login' => 'First login',
        'at' => 'at',

        // Activity & Security
        'activity_security' => 'Activity & Security',
        'recent_activity' => 'Recent Activity',
        'ip_address' => 'IP Address',
        'active_sessions' => 'Active Sessions',
        'manage_sessions_desc' => 'Manage active sessions on your different devices.',
        'view_sessions' => 'View Sessions',
        'sessions_alert' => 'You can view all your active sessions and revoke them if necessary.',

        // Danger Zone
        'danger_zone' => 'Danger Zone',
        'delete_account' => 'Delete Account',
        'delete_warning' => 'This action is irreversible. All your data will be permanently deleted.',
        'delete_confirm' => 'Are you ABSOLUTELY sure you want to delete your account? This action is irreversible!',
        'coming_soon' => 'Feature coming soon',
    ],

    // Authentication
    'auth' => [
        'login' => 'Login',
        'signup' => 'Sign Up',
        'logout' => 'Logout',
        'forgot_password' => 'Forgot Password?',
        'reset_password' => 'Reset Password',
        'remember_me' => 'Remember me',
        'no_account' => 'Don\'t have an account?',
        'already_account' => 'Already have an account?',
        'email' => 'Email',
        'password' => 'Password',
        'confirm_password' => 'Confirm Password',
        'name' => 'Name',
    ],

    // Common
    'common' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'edit' => 'Edit',
        'add' => 'Add',
        'close' => 'Close',
        'confirm' => 'Confirm',
        'yes' => 'Yes',
        'no' => 'No',
        'search' => 'Search',
        'filter' => 'Filter',
        'actions' => 'Actions',
        'date' => 'Date',
        'amount' => 'Amount',
        'description' => 'Description',
        'name' => 'Name',
        'status' => 'Status',
        'active' => 'Active',
        'inactive' => 'Inactive',
        'loading' => 'Loading...',
        'user' => 'User',
    ],

    // Messages
    'messages' => [
        'success' => 'Operation successful!',
        'error' => 'An error occurred.',
        'delete_confirm' => 'Are you sure you want to delete this item?',
        'no_data' => 'No data available.',
        'limit_reached' => 'You have reached the limit for your free subscription. Upgrade to Premium to create more items.',
    ],

    // Validation
    'validation' => [
        'required' => 'This field is required.',
        'email' => 'Please enter a valid email address.',
        'min_length' => 'Must be at least :min characters.',
        'max_length' => 'Must not exceed :max characters.',
        'match' => 'Passwords do not match.',
        'invalid' => 'Invalid value.',
        'greater_than' => 'Must be greater than :value.',
    ],
];
