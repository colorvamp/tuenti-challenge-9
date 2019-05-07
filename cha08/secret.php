<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?= asset_link('css/bootstrap.min.css'); ?>">
    <script src="<?= asset_link('js/bootstrap.min.js') ?>"></script>
    <title><?= $secret['name'] ?> secret</title>
</head>
<body>
<div class="container">
    <h3><?= $secret['name'] ?> is <?= $secret['content'] ?></h3>
    <button class="btn btn-info" onclick="history.back()">Go back</button>
</div>
</body>
</html>
