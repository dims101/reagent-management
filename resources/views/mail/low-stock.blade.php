<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Low Stock Alert</title>
</head>

<body>
    <h1>Low Stock Alert</h1>
    <p>Hello {{ $name }},</p>
    <p>
        The reagent <strong>{{ $reagent_name }}</strong> is running low on stock.
        Only <strong>{{ $remaining_qty }}</strong> left.
    </p>
    <p>
        <a href="{{ $link }}"
            style="display:inline-block;padding:10px 20px;background:#3490dc;color:#fff;text-decoration:none;border-radius:5px;">Review
            Reagent</a>
    </p>
    <p>Thanks,<br>
        {{ config('app.name') }}</p>
</body>

</html>
