# Magento 2 Delete Orders
The store owners may want to remove unnecessary orders which have been canceled, closed, or created just for testing purposes. But, Magento 2 default functionalities does not allow the admin to completely remove redundant orders, invoices, shipments, or credit memos. Instead, it only allows cancelling them, which can lead to inconvenience in backend management. Well, in order to solve this problem, this extension has been developed. This extension is easy to use as it seems like the default functionality and can easily be used.

<b>For Settings: Stores > Configuration > MagePrashant > Delete Order</b>

# Installation Instruction

<b>Manual Installation</b>

- Copy the content of the repo to the <b>app/code/MagePrashant/DeleteOrder</b>
- Run command: <b>php bin/magento setup:upgrade</b>
- Run command: <b>php bin/magento setup:static-content:deploy</b>
- Now flush cache: <b>php bin/magento cache:flush</b>

# Contribution

Want to contribute to this extension? The quickest way is to <a href="https://help.github.com/articles/about-pull-requests/">open a pull request</a> on GitHub.

# Support

If you encounter any problems or bugs, please <a href="https://github.com/mageprashant/magento-2-delete-orders/issues">open an issue</a> on GitHub.
