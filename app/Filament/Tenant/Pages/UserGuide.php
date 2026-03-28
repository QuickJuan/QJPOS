<?php

namespace App\Filament\Tenant\Pages;

use Filament\Pages\Page as FilamentPage;

class UserGuide extends FilamentPage
{
    protected static ?string $navigationIcon  = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'User Guide';
    protected static ?string $title           = 'Admin Panel — User Guide';
    protected static ?string $navigationGroup = null; // top-level, no group
    protected static ?int    $navigationSort  = 999;
    protected static string  $view            = 'filament.tenant.pages.user-guide';

    /**
     * Structured guide content.
     * Each section maps to a panel navigation group.
     * Each topic has a 'q' (title shown in accordion header) and 'a' (HTML body).
     */
    public function getSections(): array
    {
        return [
            [
                'icon'   => 'heroicon-o-building-storefront',
                'title'  => 'Getting Started',
                'topics' => [
                    [
                        'q' => 'How do I log in?',
                        'a' => 'Go to your store URL and click <strong>Log In</strong>. Enter your email and password. You must select a <strong>Branch</strong> before logging in. If you have multiple branches, pick the one you are working at today.',
                    ],
                    [
                        'q' => 'How do I switch branches?',
                        'a' => 'After logging in, use the branch selector on the login screen. You cannot switch branches while a cashier session is open — close your shift first.',
                    ],
                    [
                        'q' => 'How do I log out?',
                        'a' => 'Click your name or avatar at the top-right of the sidebar and choose <strong>Log Out</strong>. You can also log out from the Home action hub.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-squares-2x2',
                'title'  => 'POS & Cashiering',
                'topics' => [
                    [
                        'q' => 'How do I start a cashier session (shift)?',
                        'a' => 'From the Home hub, click <strong>Start Cashiering</strong>. Enter your beginning cash and click <strong>Open Session</strong>. You must have an open session to accept payments.',
                    ],
                    [
                        'q' => 'How do I add items to a cart / bill?',
                        'a' => 'Inside the POS screen, browse categories on the left and tap a product to add it to the active bill. You can also search by barcode or product name. Use the quantity controls on each cart row to adjust amounts.',
                    ],
                    [
                        'q' => 'How do I apply a discount?',
                        'a' => 'Select a cart item and tap the <strong>Discount</strong> button. Choose a pre-configured discount or enter a manual amount. Senior/PWD discounts require the applicable prompt.',
                    ],
                    [
                        'q' => 'How do I settle a bill (accept payment)?',
                        'a' => 'With a cart open, click <strong>Settle Payment</strong>. Choose the payment method(s), enter the amount tendered, then confirm. Mixed payments (cash + e-wallet, etc.) are supported — add each payment type separately.',
                    ],
                    [
                        'q' => 'How do I close a shift (X-Reading)?',
                        'a' => 'From the POS menu, click <strong>Close Shift</strong>. Review your session summary (total sales, payments by method, cashouts). Click <strong>Close Session</strong> to confirm. A printable X-Reading report will be available.',
                    ],
                    [
                        'q' => 'How do I void an item?',
                        'a' => 'Select the cart item and tap <strong>Void</strong>. Voided items require an approver for roles that have this restriction. The item is removed from the bill and logged in the Void Items report.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-table-cells',
                'title'  => 'Table Ordering',
                'topics' => [
                    [
                        'q' => 'How do I assign an order to a table?',
                        'a' => 'From the Home hub, open <strong>Table Ordering</strong>. Select a table room, then tap a vacant table to start a new order. The table turns orange when occupied.',
                    ],
                    [
                        'q' => 'How do I transfer an order between tables?',
                        'a' => 'With an order open, use the <strong>Transfer Order</strong> action. Select the target table. All items move to the new table.',
                    ],
                    [
                        'q' => 'How do I merge tables?',
                        'a' => 'Tap a table and select <strong>Merge</strong>, then tap the table to merge with. The carts are combined into one bill.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-clock',
                'title'  => 'Attendance (Clock In / Out)',
                'topics' => [
                    [
                        'q' => 'How do I clock in or out?',
                        'a' => 'From the Home hub, tap <strong>Clock In / Out</strong>. Enter your <strong>Employee No</strong> (from your employee record). Allow camera access. The system will detect whether to clock you in or out. Take a photo, preview it, then confirm.',
                    ],
                    [
                        'q' => 'Why is the clock-out button not showing?',
                        'a' => 'You must wait at least <strong>5 minutes</strong> after clocking in before you can clock out. If the issue persists, ask a manager to check your attendance record.',
                    ],
                    [
                        'q' => 'Where do I see attendance records?',
                        'a' => 'In the admin panel, go to <strong>HR and Payroll → Employees</strong> and open an employee record to view their attendance history.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-cube',
                'title'  => 'Products & Catalog',
                'topics' => [
                    [
                        'q' => 'How do I add a new product?',
                        'a' => 'Go to <strong>Products</strong> in the sidebar. Click <strong>New Product</strong>. Fill in the name, category, price/packaging, and upload an image. Save to make it available in the POS.',
                    ],
                    [
                        'q' => 'How do I set product pricing (packaging)?',
                        'a' => 'Open a product and go to the <strong>Product Pricing</strong> tab. Each row is a packaging variant (e.g. Small, Medium, Large) with its own price. Click <strong>Add Pricing</strong> to add variants.',
                    ],
                    [
                        'q' => 'How do I add product options or add-ons?',
                        'a' => '<strong>Options</strong> (e.g. size, spice level) are found under <strong>Product Options</strong>. <strong>Add-ons</strong> (e.g. extra cheese) are under <strong>Product Add-ons</strong>. Link them to products from the product edit screen.',
                    ],
                    [
                        'q' => 'How do modifiers work?',
                        'a' => 'Modifiers let kitchen staff annotate items (e.g. "No onions"). Manage them under <strong>Modifiers</strong>. They are applied per cart item at the POS.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-archive-box',
                'title'  => 'Inventory',
                'topics' => [
                    [
                        'q' => 'How do I check stock levels?',
                        'a' => 'Go to <strong>Inventory → Low Stock Report</strong> or <strong>Overstock Report</strong>. These show items below or above your configured thresholds.',
                    ],
                    [
                        'q' => 'How do I update stock thresholds?',
                        'a' => 'Open an inventory item and set the <strong>Low Stock Threshold</strong> and <strong>Overstock Threshold</strong> fields. Items crossing these values will appear in the respective reports.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-users',
                'title'  => 'Customers & E-Wallet',
                'topics' => [
                    [
                        'q' => 'How do I register a customer?',
                        'a' => 'Go to <strong>Customer Management → Customers</strong>. Click <strong>New Customer</strong>, enter their name, contact, and any loyalty/points details.',
                    ],
                    [
                        'q' => 'How does the e-wallet work?',
                        'a' => 'Customers have an e-wallet balance that can be loaded via <strong>Load Change</strong> during payment. When settling a bill, choose <strong>E-Wallet</strong> as a payment method to deduct from their balance.',
                    ],
                    [
                        'q' => 'How do loyalty points work?',
                        'a' => 'Points are earned automatically based on the purchase amount and the points earning rate set in <strong>Management → Settings</strong>. Points are tracked per customer and can be redeemed during checkout.',
                    ],
                    [
                        'q' => 'Where do I see contact form submissions?',
                        'a' => 'Go to <strong>Customer Management → Contact Inquiries</strong>. New inquiries are listed here and can be marked as resolved.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-chart-bar',
                'title'  => 'Sales Reports',
                'topics' => [
                    [
                        'q' => 'What reports are available?',
                        'a' => 'Under <strong>Sales Report</strong> you have: Sales Invoice Report, Detailed Sales Item Report, Void Items Report, Refund Orders Report, and more. Each can be filtered by date range and branch.',
                    ],
                    [
                        'q' => 'How do I print a report?',
                        'a' => 'Open a report, apply filters, then click the <strong>Print</strong> button in the header. A print-optimised view will open in a new tab.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-banknotes',
                'title'  => 'Finance & Expenses',
                'topics' => [
                    [
                        'q' => 'How do I record an expense?',
                        'a' => 'Go to <strong>Finance → Expenses</strong>. Click <strong>New Expense</strong>. Select the category, enter the amount, attach a receipt if needed, and save.',
                    ],
                    [
                        'q' => 'How do I manage expense categories?',
                        'a' => 'Go to <strong>Finance → Expense Categories</strong>. Add, edit, or deactivate categories from here.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-user-group',
                'title'  => 'HR & Payroll',
                'topics' => [
                    [
                        'q' => 'How do I add an employee?',
                        'a' => 'Go to <strong>HR and Payroll → Employees</strong>. Click <strong>New Employee</strong>. Fill in personal details, assign a branch, set a schedule, and link to a user account if the employee also logs into the POS.',
                    ],
                    [
                        'q' => 'How do I manage careers / job postings?',
                        'a' => 'Go to <strong>HR and Payroll → Careers</strong>. Create a job posting with title, description, and requirements. Published postings appear on the public careers page at <code>/careers/{slug}</code>.',
                    ],
                    [
                        'q' => 'How do I handle leaves?',
                        'a' => 'Leave types are configured under <strong>HR and Payroll → Leave Types</strong>. Employees can submit leave requests from the tenant portal, which then appear in the HR leave management section.',
                    ],
                    [
                        'q' => 'How do cash advances work?',
                        'a' => 'Go to <strong>HR and Payroll → Cash Advances</strong>. Record an advance for an employee. The amount is tracked against their compensation.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-shield-check',
                'title'  => 'Roles & Permissions',
                'topics' => [
                    [
                        'q' => 'How do I create a user account?',
                        'a' => 'Go to <strong>Roles and Permissions → Users</strong>. Click <strong>New User</strong>. Set the name, email, password, role, and assign them to the correct branch(es).',
                    ],
                    [
                        'q' => 'What roles are available?',
                        'a' => 'Roles control what areas of the POS and admin panel a user can access. Common roles include <strong>Admin</strong>, <strong>Cashier</strong>, <strong>Order Taking</strong>, and <strong>Waiter</strong>. Roles are assigned per user.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-document',
                'title'  => 'Page Builder',
                'topics' => [
                    [
                        'q' => 'How do I create a new web page?',
                        'a' => 'Go to <strong>Page Builder → Pages</strong>. Click <strong>New Page</strong>. Choose a page type (Page, Blog Post, FAQ, Product, or Landing Page), enter a title and slug, then save. Add content blocks from the <strong>Blocks</strong> tab inside the page.',
                    ],
                    [
                        'q' => 'How do I add sections/blocks to a page?',
                        'a' => 'Open a page and go to the <strong>Blocks</strong> tab. Click <strong>Add Block</strong>, choose a block type (Banner, Blog, Careers, Text, etc.), configure its content, and save. Blocks render in the order they are listed.',
                    ],
                    [
                        'q' => 'How do I set up SEO for a page?',
                        'a' => 'Open a page and go to the <strong>SEO</strong> tab inside the page edit form. Set the SEO Title, Meta Description, Open Graph image, and structured data (JSON-LD). Leave Canonical URL blank to auto-generate it.',
                    ],
                    [
                        'q' => 'How does the Navigation menu work?',
                        'a' => 'Go to <strong>Page Builder → Navigation</strong>. Add navigation items with a label, link, and optional parent for dropdowns. The order determines the menu sequence on the public site.',
                    ],
                    [
                        'q' => 'How do I create a URL redirect?',
                        'a' => 'Go to <strong>Page Builder → Redirects</strong>. Click <strong>New Redirect</strong>. Enter the old path (e.g. <code>/old-about</code>) and the destination URL or path. Choose 301 (permanent) or 302 (temporary). The redirect is applied immediately.',
                    ],
                    [
                        'q' => 'How do I update the sitemap?',
                        'a' => 'Go to <strong>Page Builder → Sitemap</strong>. The sitemap is auto-generated and cached. Click <strong>Regenerate Sitemap</strong> to flush the cache after major page changes. Copy the sitemap URL and submit it to Google Search Console.',
                    ],
                ],
            ],
            [
                'icon'   => 'heroicon-o-cog-6-tooth',
                'title'  => 'Management & Settings',
                'topics' => [
                    [
                        'q' => 'How do I update store settings?',
                        'a' => 'Go to <strong>Management → Settings</strong>. Here you can update the company name, address, contact, logo, timezone, and contact form recipient emails.',
                    ],
                    [
                        'q' => 'How do I configure payment methods?',
                        'a' => 'Go to <strong>Store → Payment Methods</strong>. Enable or disable payment options (Cash, GCash, Card, etc.) and set their display names.',
                    ],
                    [
                        'q' => 'How do I manage branches?',
                        'a' => 'Go to <strong>Store → Branches</strong>. Each branch has its own name, address, receipt configuration, and bill number. Employees and cashier sessions are tied to specific branches.',
                    ],
                    [
                        'q' => 'How do I back up the database?',
                        'a' => 'Go to <strong>Management → Backups</strong>. Click <strong>Create Backup</strong>. Backups are stored locally or off-site depending on your backup configuration.',
                    ],
                ],
            ],
        ];
    }
}
