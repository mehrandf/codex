<?php
require __DIR__ . '/functions.php';
ensure_data_file();
$links = load_links();
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لینک‌های من</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-8uyTQG7n2AFDzy83H8XTur2qxGn8pY/+bexdFv+DE5jBqFaUG2RgxN6E466+vWXTjhBMWrMUR3pvN8F2Zp4FZw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="public-page">
    <main class="container">
        <section class="profile">
            <img src="assets/images/avatar-placeholder.svg" alt="آواتار" class="profile__avatar" loading="lazy">
            <h1 class="profile__title">شبکه‌های من</h1>
            <p class="profile__subtitle">برای مشاهده صفحات من روی دکمه‌ها کلیک کنید.</p>
        </section>
        <section class="links">
            <?php if (empty($links)): ?>
                <p class="links__empty">هنوز لینکی ثبت نشده است.</p>
            <?php else: ?>
                <?php foreach ($links as $link): ?>
                    <?php
                    $url = $link['url'];
                    $relParts = ['noopener'];
                    if (($link['link_type'] ?? 'follow') === 'nofollow') {
                        $relParts[] = 'nofollow';
                    } elseif (($link['link_type'] ?? 'follow') === '301') {
                        $url = 'redirect.php?id=' . urlencode($link['id']);
                    }
                    $relAttribute = 'rel="' . implode(' ', $relParts) . '"';
                    ?>
                    <a class="links__item" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" <?= $relAttribute; ?> style="--btn-color: <?= htmlspecialchars($link['color'] ?? '#4b5fff', ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="links__icon"><i class="<?= htmlspecialchars($link['icon'] ?? 'fa-solid fa-link', ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></span>
                        <span class="links__text"><?= htmlspecialchars($link['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
