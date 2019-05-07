<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?= asset_link('css/bootstrap.min.css'); ?>">
    <script src="<?= asset_link('js/bootstrap.min.js') ?>"></script>
    <title>Welcome, <?= check_cookies() ?></title>
</head>
<body>
<div class="container">
    <h1>Welcome, <?= check_cookies() ?></h1>
    <h3>List of secrets</h3>
    <table class="table table-striped">
        <thead>
        <th>Name</th>
        <th>Vieweable by</th>
        </thead>
        <?php foreach (list_secrets() as $secret) { ?>
            <tr>
                <td><?= $secret['name'] ?></td>
                <td><?= $secret['users'] ?: 'Everybody' ?></td>
                <td><a href="<?= page_link("secrets/{$secret['name']}") ?>">View</a></td>
            </tr>
        <?php } ?>
    </table>
    <a class="btn btn-info" href="<?= page_link('logout') ?>">Logout</a>
</div>
</body>
</html>
