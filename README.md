# ZiNi Pay Payment Gateway Plugin for SMM Panel

Integrate the ZiNi Pay payment gateway into your SMM panel seamlessly with this plugin. Accept payments easily and securely, providing your customers with a fast and reliable payment experience. This plugin is designed for easy installation and configuration.

https://zinipay.com

## Features

- **Easy Integration:** Simple step-by-step installation process.
- **Secure Payments:** Leverages ZiNi Pay's secure infrastructure for all transactions.
- **Admin Configuration:** Easily manage API keys, payment limits, and other settings from your SMM admin panel.
- **Automatic Callbacks:** Handles payment verification and updates user balances automatically.
- **Currency Conversion:** Set a custom exchange rate for USD to your local currency.

---

## Installation Guide

Follow these three simple steps to integrate the ZiNi Pay payment gateway into your SMM panel.

### Step 1: Upload Plugin Files

1.  Log in to your website's **cPanel**.
2.  Navigate to **File Manager**.
3.  Go to the root directory of your SMM panel (e.g., `public_html` or a subdirectory).
4.  Upload the plugin's `.zip` file that contains the `app` and `admin` directories.
5.  Once uploaded, **extract** the `.zip` file. The plugin files will be automatically placed in the correct directories.



### Step 2: Update the Database

1.  In your cPanel, open **phpMyAdmin** and select your SMM panel's database.
2.  Click on the **SQL** tab.
3.  Copy the entire content from the `payment_methods.sql` file and paste it into the SQL query box.
4.  Click the **Go** button to execute the query. This will add 'ZiNi Pay' to your list of available payment methods.

```sql
INSERT INTO `payment_methods` (`id`, `method_name`, `method_get`, `method_min`, `method_max`, `method_type`, `method_extras`, `method_line`, `nouse`) VALUES
(70, 'zinipay', 'zinipay', 1, 1000, '2', '{\"method_type\":\"2\",\"name\":\"ZiNi Pay\",\"min\":\"1\",\"max\":\"1000\",\"api_key\":\"YOUR_API_KEY_HERE\",\"api_url\":\"https:\\/\\/api.zinipay.com\",\"exchange_rate\":\"110\"}', 1, '2');
```



### Step 3: Configure the Gateway

1.  Log in to your SMM panel's **admin area**.
2.  Navigate to **Settings** -> **Payment Methods**.
3.  Find **ZiNi Pay** in the list and click the **Edit** button.
4.  In the settings modal, fill in the following details:
    -   **API Key:** Enter your unique ZiNi Pay API Key.
    -   **API URL:** This should be `https://api.zinipay.com`.
    -   **USD Exchange Rate:** Set the conversion rate from USD to your site's primary currency (e.g., `110`).
    -   **Minimum/Maximum Payment:** Define the payment limits.
    -   **Visibility:** Set to **Active** to enable the gateway for your users.
5.  Click **Update** to save your changes.

Your ZiNi Pay payment gateway is now ready to use!

---

## License

This plugin is released under the MIT License. See the LICENSE.md file for details.

Copyright (c) 2025 ZiNi Pay