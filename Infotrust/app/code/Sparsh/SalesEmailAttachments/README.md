##Sparsh Sales Email Attachments Extension
This extension allows to automatically attach PDF documents, terms & conditions or policy documents to sales emails like invoices, shipments and credit memos.

##Support: 
version - 2.3.x, 2.4.x

##How to install Extension

1. Download the archive file.
2. Unzip the file
3. Create a folder [Magento_Root]/app/code/Sparsh/SalesEmailAttachments
4. Drop/move the unzipped files to directory '[Magento_Root]/app/code/Sparsh/SalesEmailAttachments'

#Enable Extension:
- php bin/magento module:enable Sparsh_SalesEmailAttachments
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush

#Disable Extension:
- php bin/magento module:disable Sparsh_SalesEmailAttachments
- php bin/magento setup:upgrade
- php bin/magento setup:di:compile
- php bin/magento setup:static-content:deploy
- php bin/magento cache:flush
