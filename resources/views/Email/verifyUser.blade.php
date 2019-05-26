<!DOCTYPE >
<html>
    <body>
        <h5>Hi dear,</h5>
        <p>{{ $user->username }} please verify this e-mail by clicking the verify link.</p>
        <a href="{{ $url }}">Verify</a>
    </body>
</html>