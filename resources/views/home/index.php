<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Assignment Homepage</title>
    <link rel="stylesheet" href="/styles/main.css" />
</head>
<body>
    <div class="container" style="padding-top: 40px;">
        <form action="/fetch-data" method="POST">
            <div class="form-group">
                <label for="user_id">Twitter User ID</label>
                <input type="text" id="user_id" class="form-control" name="user_id" />
            </div><div class="form-group">
                <label for="screen_name">Twitter Username</label>
                <input type="text" id="screen_name" class="form-control" name="screen_name" />
            </div><div class="form-group">
                <input type="submit" class="btn btn-primary">
            </div>
        </form>
    </div>
</body>
</html>