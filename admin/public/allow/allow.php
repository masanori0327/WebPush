<!doctype html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Push通知登録</title>
    
    <style>
      body {
          margin: 0;
          padding: 0;
          width: 100%;
          height: 100%;
      }
      body, html {
          height: 100%;
          overflow: hidden;
      }
      .subscribe-helper-text {
          margin-top: 30%;
          text-align: center;
          font-family: "Lato",sans-serif;
          font-size: 20px;
          padding: 20px 20px 0px 20px;
      }
      .subscribe-helper-text p {
          margin-top: 10px;
      }
      .image-wrapper {
          width: 118px;
          margin: 10px auto;
      }
      .branding-logo {
          max-width: 100%;
      }
      .hide {
        display: none;
      }
      footer {
        text-align: center;
        position: absolute;
        bottom: 10px;
        width: 100%;
        margin-top:10px;
      }
    </style>
    
    <script src="/js/jquery.min.js"></script>
    <script src="/js/fakeLoader.min.js"></script>
    
    <link rel="stylesheet" href="/css/fakeLoader.css">
    <link rel="manifest" href="manifest/<?php echo $_GET['ua']?>.json">
  </head>

  <body>
    <div class="subscribe-helper-text">
        <div id="js-text">
            <p id="js-user-customize-text"><strong>'許可'</strong> をクリックしてください</p>
            <p style="font-size: 13px;" id="js-offer-text">通知はブラウザの設定でいつでもオフにできます</p>
        </div>
        <div class="fakeloader"></div>
    </div>

    <script type="text/javascript" charset="UTF-8" src="./allow.js"></script>
    <script>
    $(document).ready(function(){
        $(".fakeloader").fakeLoader({
            timeToHide:1000000,
            zIndex:-1,
            bgColor:"#df9463",
            spinner:"spinner7"
        });
    });
    </script>
  </body>
</html>
