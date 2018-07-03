<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="utf-8">
</head>
<body>
<div>
    <p> {{$name}}様、</p>
    <br>
    <div>
        <p> {{$userName}} Coin-alertアカウントのパスワードをリセットするプロセスを開始するには、以下ののリンクをクリックしてください。 このリンクは送信{{$expireHour}}時間後で期限切れとなります。。
            <br>
            <a href="{{$link}}">パースワードをリセットします。</a></p>
    </div>
    <p>上記のリンクをクリックしても効かない場合は、URLをコピーして新ブラウザウィンドウに貼り付けてください。</p>
    <br>
    <div>
        <p>敬具、 </p>
        <p>Coin-alert チーム</p>
    </div>
</div>
</body>
</html>