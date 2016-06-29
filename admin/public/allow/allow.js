/**
 * push通知を容認するjs
 */
   var registerUrl = '//{domain}/push/api/regist-subscription-id';
   var browser;
   
   // userAgentから端末の判別
   var _ua = (function(u){
       return {
         Tablet:(u.indexOf("windows") != -1 && u.indexOf("touch") != -1 && u.indexOf("tablet pc") == -1)
           || u.indexOf("ipad") != -1
           || (u.indexOf("android") != -1 && u.indexOf("mobile") == -1)
           || (u.indexOf("firefox") != -1 && u.indexOf("tablet") != -1)
           || u.indexOf("kindle") != -1
           || u.indexOf("silk") != -1
           || u.indexOf("playbook") != -1,
         Mobile:(u.indexOf("windows") != -1 && u.indexOf("phone") != -1)
           || u.indexOf("iphone") != -1
           || u.indexOf("ipod") != -1
           || (u.indexOf("android") != -1 && u.indexOf("mobile") != -1)
           || (u.indexOf("firefox") != -1 && u.indexOf("mobile") != -1)
           || u.indexOf("blackberry") != -1
       }
     })(window.navigator.userAgent.toLowerCase());

   var splitEndPointSubscription =  function (subscriptionDetails) {
       var userAgent = window.navigator.userAgent.toLowerCase();
       var endpointURL, endpoint, subscriptionId;
       if(userAgent.indexOf('chrome') != -1){
           endpointURL = 'https://android.googleapis.com/gcm/send/';
           browser = 'chrome';
       }else if(userAgent.indexOf('firefox') != -1){
           endpointURL = 'https://updates.push.services.mozilla.com/push/';
           browser = 'firefox';
       }
       endpoint = subscriptionDetails.endpoint;
       
       if(endpoint.indexOf(endpointURL) === 0) {
          return subscriptionId = endpoint.replace(endpointURL , '');
       }

       return subscriptionDetails.subscriptionId;
   };

   var sendSubscriptionToServer = function (subscription) {
       var subscriptionId = splitEndPointSubscription(subscription);
       
       // 緯度・経度を取得する
       // https://developer.mozilla.org/ja/docs/WebAPI/Using_geolocation#Handling_errors
       if(navigator.geolocation){
           navigator.geolocation.getCurrentPosition(
               function(position){
                   var data = position.coords;
                   sendSubscriptionId(subscriptionId, data.latitude, data.longitude, null);
               },
               function(error){
                   sendSubscriptionId(subscriptionId, null, null, error.code);
               },
               {
                   "enableHighAccuracy": true,
               }
           );
       }else{
           sendSubscriptionId(subscriptionId, null, null, 9);
       }
   };

   var sendSubscriptionId = function (subscriptionId, lat, lon, geoError) {
       var url = registerUrl + '?id=' + subscriptionId + '&ua=' + ua + '&b=' + browser;
       // 位置情報
       if(geoError == null){
           url += '&lat=' + lat + '&lon=' + lon;
       }else{
           url += '&geoError=' + geoError;
       }
       // 端末判定
       if(_ua.Mobile){
           url += '&t=m';
       }else if(_ua.Tablet){
           url += '&t=t';
       }else{
           url += '&t=p';
       }
       fetch(url).then(function(response) {
           if (response.status !== 200) {
               console.log('Looks like there was a problem. Status Code: ' + response.status);
               unsubscribe();
               window.close();
           }
           
           response.json().then(function(data) {
               if (data.error) {
                   console.error('The API returned an error.', data.error);
                   unsubscribe();
                   window.close();
               }
               console.debug(data);
               window.close();
           });
       }).catch(function(err) {
           console.error('Unable to retrieve data', err);
           window.close();
       })
   };

   var unsubscribe = function () {
       navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
           serviceWorkerRegistration.pushManager.getSubscription().then(
               function(pushSubscription) {
                   if (!pushSubscription) {
                       return;
                   }
                   pushSubscription.unsubscribe().then(function() {
                       console.log('Unsubscription ok !');
                   }).catch(function(e) {
                       console.error('Unsubscription error: ', e);
                   });
               }
           ).catch(function(e) {
               console.error('Error thrown while unsubscribing from push messaging.', e);
           });
       });
   };

   var subscribe = function() {
       navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
           serviceWorkerRegistration.pushManager.subscribe({userVisibleOnly: true}).then(
               function(subscription) {
                   return sendSubscriptionToServer(subscription);
               }
           ).catch(function(e) {
               if (Notification.permission === 'denied') {
                   console.error('Permission for Notifications was denied');
               } else {
                   console.error('Unable to subscribe to push.', e);
               }
               window.close();
           });
       });
   };

   var initialiseState = function () {
       if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
           console.error('Notifications aren\'t supported.');
           window.close();
       }
       
       if (Notification.permission === 'denied') {
           console.error('The user has blocked notifications.');
           window.close();
       }

       if (!('PushManager' in window)) {
           console.error('Push messaging isn\'t supported.');
           window.close();
       }

       navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
           serviceWorkerRegistration.pushManager.getSubscription().then(
               function(subscription) {
                   if (!subscription) {
                       subscribe();
                   }else{
                       sendSubscriptionToServer(subscription);
                   }
               }
           ).catch(function(e) {
               console.error('Error during getSubscription()', e);
               window.close();
           });
       });
   };
   
   (function() {
       if ('serviceWorker' in navigator) {
           navigator.serviceWorker.register('./service-worker.js').then(initialiseState);
       } else {
           console.error('Service workers aren\'t supported in this browser.');
           window.close();
       }
   })();