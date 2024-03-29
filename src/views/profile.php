<!DOCTYPE html>
<html lang="en" style="height: 100%">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="/public/css/global.css">
    <link rel="stylesheet" type="text/css" href="/public/css/profile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=DM Sans' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'>
    <script src="/public/js/common.js"></script>
    <script src="/public/js/profile.js"></script>

    <title>Profile</title>
</head>

<body>
    <nav>
        <div class="menu-hamburger">
            <input id="menu__toggle" type="checkbox" />
            <label class="menu__btn" for="menu__toggle">
                <span></span>
            </label>

            <ul class="menu__box">
                <li><a class="menu__item" href="/">Home</a></li>
                <li><a class="menu__item" href="/top">Top</a></li>
                <li><a class="menu__item" href="/profile">Profile</a></li>
                <?php if ($isAdmin): ?>
                    <li><a class="menu__item" href="/admin">Admin</a></li>
                <?php endif; ?>

                <li class="divider"></li>
                <li><a class="secondary_menu__item" href="/create">Add book</a></li>
                <li><a class="secondary_menu__item" href="/favorites">Favorites</a></li>
                <?php if (!$isLogged): ?>
                    <li><a class="secondary_menu__item" href="/login">Log in</a></li>
                    <li><a class="secondary_menu__item" href="/register">Sign up</a></li>

                <?php else: ?>
                    <li>
                        <a class="secondary_menu__item">
                            <button onclick="logout()">Log out</button>
                        </a>
                    </li>

                <?php endif; ?>
            </ul>
        </div>
        <div class="header-one-side">
            <div id="small-logo">Flipbook</div>
            <ul class="menu">
                <li><a href="/">Home</a></li>
                <li><a href="/top">Top</a></li>
                <li><a class="selected" href="/profile">Profile</a></li>
                <?php if ($isAdmin): ?>
                    <li><a href="/admin">Admin</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="header-one-side">
            <div class="text-button">
                <i class="material-icons">menu_book</i>
                <a class="text-header" href="/create">Create book</a>
            </div>
            <div class="text-button">
                <i class="material-icons">favorite</i>
                <a class="text-header" href="/favorites">Favorites</a>
            </div>
            <div class="header-form">
                <?php if (!$isLogged): ?>
                    <button class="secondary-button" onclick="routeToLogin()">Log
                        in</button>
                    <button onclick="routeToRegistration()">Sign up</button>
                    </form>

                <?php else: ?>
                    <button class="secondary-button" onclick="logout()">Log out</button>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <main>
        <div class="profile-content">
            <?php if (!$isLogged): ?>
                <div class="header">
                    You need to be logged in to see your profile.
                </div>
            <?php else: ?>
                <div class="image-section">
                    <div id="imagePreview">
                        <?php if (empty($avatar)): ?>
                            <div class="placeholder-image">
                            </div>
                        <?php else: ?>
                            <img class="avatar-image" src=<?= '/public/uploads/' . $avatar ?> alt="News Image 1">
                        <?php endif; ?>
                    </div>
                    <label for="imageUpload">
                        <i class="material-icons">edit</i>
                    </label>

                    <form id="avatarForm" action="changeAvatar" method="post" enctype="multipart/form-data">
                        <input type="file" id="imageUpload" name="image" accept="image/*" onchange="previewImage()">
                    </form>


                </div>
                <div class="headline-h1-semibold">
                    Hello,
                    <?= $username ?>!
                </div>
                <div class="profile-menu">
                    <div class="profile-menu-item selected-profile-menu-item" onclick="toggleMenuItem(this, 'reviews')">
                        <div class="icon-background">
                            <i class="material-icons custom-icon">menu_book</i>
                        </div>
                        <div class="inter-semibold-16">
                            Your Reviews
                        </div>
                    </div>
                    <div class="profile-menu-item not-selected-profile-menu-item" onclick="toggleMenuItem(this, 'books')">
                        <div class="icon-background">
                            <i class="material-icons custom-icon">menu_book</i>
                        </div>
                        <div class="inter-semibold-16">
                            Your Books
                        </div>
                    </div>
                </div>
                <div class="list" id="reviewsList">
                    <?php foreach ($reviews as $review): ?>
                        <div class="card" onclick="routeToDetails('<?= $review->getBookId() ?>')">
                            <div class="card-content">
                                <div class="card-header">
                                    <div class="inter-semibold-16">
                                        <?= $review->getBookTitle() ?>
                                    </div>
                                    <div class="inter-regular-12">
                                        <?= $review->getBookAuthors() ?>
                                    </div>
                                    <div class="dmsans-regular-14">
                                        <?= $review->getContent() ?>
                                    </div>

                                </div>
                            </div>
                            <div class="profile-card-right-side">
                                <div class="stars">
                                    <i class="material-icons">star_border</i>
                                    <div class="inter-light-14">
                                        <?= $review->getRate() ?>/5
                                    </div>
                                </div>
                                <?php
                                // Determine the status based on accept_date and reject_date
                                if ($review->getAcceptDate() !== null) {
                                    echo '<div class="status-accepted">Accepted</div>';
                                } elseif ($review->getRejectDate() !== null) {
                                    echo '<div class="status-rejected">Rejected</div>';
                                } else {
                                    echo '<div class="status-awaiting">Awaiting</div>';
                                }
                                ?>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="list" id="booksList" style="display: none;">
                    <?php foreach ($books as $book): ?>
                        <div class="card" onclick="routeToDetails('<?= $book->id ?>')">
                            <div class="card-content">
                                <div class="card-header">
                                    <div class="inter-semibold-16">
                                        <?= $book->title ?>
                                    </div>
                                    <div class="inter-regular-12">
                                        <?= $book->authorsString ?>
                                    </div>
                                    <div class="dmsans-regular-14">
                                        Description:
                                        <?= $book->description ?>
                                    </div>
                                    <div class="dmsans-regular-14">
                                        Date of publication:
                                        <?= $book->getDateOfPublication() ?>
                                    </div>
                                    <div class="dmsans-regular-14">
                                        Page count:
                                        <?= $book->pageCount ?>
                                    </div>
                                    <div class="dmsans-regular-14">
                                        ISBN number:
                                        <?= $book->isbnNumber ?>
                                    </div>

                                </div>
                            </div>
                            <div class="profile-card-right-side">
                                <?php
                                if ($book->accept_date !== null) {
                                    echo ' <div class="stars">
                                    <i class="material-icons">star_border</i>
                                    <div class="inter-light-14">
                                        ' . round($book->average_rate, 1) . '/5
                                    </div>
                                </div>';
                                }
                                ?>

                                <?php
                                // Determine the status based on accept_date and reject_date
                                if ($book->accept_date !== null) {
                                    echo '<div class="status-accepted">Accepted</div>';
                                } elseif ($book->reject_date !== null) {
                                    echo '<div class="status-rejected">Rejected</div>';
                                } else {
                                    echo '<div class="status-awaiting">Awaiting</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>

</html>