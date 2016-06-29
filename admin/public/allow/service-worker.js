'use strict';

var webpushUrl = "https://{domain}/pf/";

var splitEndPointSubscription =  function (userAgent, subscriptionDetails) {
    var endpointURL, endpoint, subscriptionId;
    if(userAgent.indexOf('chrome') != -1){
        endpointURL = 'https://android.googleapis.com/gcm/send/';
    }else if(userAgent.indexOf('firefox') != -1){
        endpointURL = 'https://updates.push.services.mozilla.com/push/';
    }
    endpoint = subscriptionDetails.endpoint;
    
    if(endpoint.indexOf(endpointURL) === 0) {
       return subscriptionId = endpoint.replace(endpointURL , '');
    }

    return subscriptionDetails.subscriptionId;
};

var pushLogId = null;

self.addEventListener('push', function(event) {
    var userAgent = navigator.userAgent.toLowerCase();
    event.waitUntil(
        self.registration.pushManager.getSubscription().then(function(subscription) {
            var subscriptionId = splitEndPointSubscription(userAgent, subscription);
            
            return fetch(webpushUrl + 'notification/get?id=' + subscriptionId).then(function(response) {
                if (response.status !== 200) {
                    throw new Error();
                }
                
                return response.json().then(function(data) {
                    if (data.error || !data.notification) {
                        throw new Error();
                    }
                    
                    var title = data.notification.title;
                    var message = data.notification.message;
                    var icon = data.notification.icon + '?redirect=' + encodeURIComponent(data.redirect);
                    var notificationTag = data.notification.tag;
                    
                    pushLogId = data.pushLogId;
                    
                    var trackShowUrl = webpushUrl + 'notification/show?id=' + subscriptionId + '&msg=' + pushLogId;
                    fetch(trackShowUrl);
                    
                    return self.registration.showNotification(title, {
                        body: message,
                        icon: icon,
                        requireInteraction: true,
                        tag: notificationTag
                    });
                });
            }).catch(function(err) {
                var errorUrl = webpushUrl + 'notification/error?id=' + subscriptionId + '&error=' + JSON.stringify(err);
                fetch(errorUrl);
            });
        })
    );
});

self.addEventListener('notificationclick', function(event) {
    var userAgent = navigator.userAgent.toLowerCase();
    self.registration.pushManager.getSubscription().then(function(subscription) {
        var subscriptionId = splitEndPointSubscription(userAgent, subscription),
        trackClickUrl = webpushUrl + 'notification/click?id=' + subscriptionId + '&msg=' + pushLogId;
        fetch(trackClickUrl);
    });
    
    event.notification.close();
    
    function redirect () {
        var query = event.notification.icon,
        url,
        queryString;

        if(query.indexOf('?') > -1) {
            queryString = query.substring(query.indexOf('?'));
            url = decodeURIComponent(queryString.split('=')[1]);
        }
        else {
            console.error('not setting redirect');
            url = '';
        }
        return url;
    }

    // This looks to see if the current is already open and
    // focuses if it is
    event.waitUntil(clients.matchAll({ type: 'window' }).then(function(clientList) {
        for (var i = 0; i < clientList.length; i++) {
            var client = clientList[i];
            if (client.url === redirect() && 'focus' in client) {
                return client.focus();
            }
        }
        if (clients.openWindow) {
            return clients.openWindow(redirect());
        }
    }));
});
