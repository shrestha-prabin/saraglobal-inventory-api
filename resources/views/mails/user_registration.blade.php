<div>
    Your email has been registered as {{ $mail->role }} at SaraGlobal by <br>
    {{ $mail->registeredBy }}

    <div>
        <p>Please login using given credentials at&nbsp;{{ $url }}</p>
    </div>

    <p>
        <b>Username:</b>&nbsp;{{ $mail->receiverName }}<br>
        <b>Email:</b>&nbsp;{{ $mail->receiverEmail }}<br>
        <b>Password:</b>&nbsp;{{ $mail->password }}<br>
    </p>
</div>

Thank You,
<br />
<i>SaraGlobal</i>