/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
var config = {
    map: {
        '*': {
            subscribeUsers : 'Webkul_MpPushNotification/js/subscribe-user-script',
            usersList : 'Webkul_MpPushNotification/js/users-list-script',
            templatesList : 'Webkul_MpPushNotification/js/templates-list-script',
            editTemplate : 'Webkul_MpPushNotification/js/edit-template-script',
            "@firebase/app": "Webkul_MpPushNotification/js/firebase-app",
            "@firebase/messaging": "Webkul_MpPushNotification/js/firebase-messaging"
        }
    }
};