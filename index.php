<?php
require __DIR__ . '/functions.php';
ensure_data_file();
$links = load_links();
$activeCount = count($links);
$latestUpdateTimestamp = null;

foreach ($links as $link) {
    $rawTimestamp = $link['updated_at'] ?? $link['created_at'] ?? null;
    if ($rawTimestamp === null) {
        continue;
    }

    $timestamp = strtotime($rawTimestamp);
    if ($timestamp === false) {
        continue;
    }

    if ($latestUpdateTimestamp === null || $timestamp > $latestUpdateTimestamp) {
        $latestUpdateTimestamp = $timestamp;
    }
}

$lastUpdatedLabel = $latestUpdateTimestamp ? date('Y/m/d', $latestUpdateTimestamp) : null;
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
            <div class="profile__avatar-wrapper">
                <img src="assets/images/avatar-placeholder.svg" alt="آواتار" class="profile__avatar" loading="lazy">
            </div>
            <div class="profile__info">
                <h1 class="profile__title">شبکه‌های من</h1>
                <p class="profile__subtitle">از طریق لینک‌های زیر می‌توانید من را در شبکه‌های اجتماعی و پروژه‌هایم دنبال کنید.</p>
            </div>
            <div class="profile__stats">
                <div class="profile__stat">
                    <span class="profile__stat-number"><?= htmlspecialchars((string) $activeCount, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="profile__stat-label">لینک فعال</span>
                </div>
                <?php if ($lastUpdatedLabel !== null): ?>
                    <div class="profile__stat">
                        <span class="profile__stat-number"><?= htmlspecialchars($lastUpdatedLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                        <span class="profile__stat-label">آخرین بروزرسانی</span>
                    </div>
                <?php endif; ?>
            </div>
        </section>
        <section class="links">
            <header class="links__header">
                <h2 class="links__title">همین حالا متصل شوید</h2>
                <p class="links__description">برای مشاهده صفحات و شبکه‌های اجتماعی من روی دکمه‌های زیر کلیک کنید.</p>
            </header>
            <?php if (empty($links)): ?>
                <p class="links__empty"><i class="fa-regular fa-circle-plus" aria-hidden="true"></i> هنوز لینکی ثبت نشده است.</p>
            <?php else: ?>
                <?php foreach ($links as $link): ?>
                    <?php
                    $url = $link['url'];
                    $relParts = ['noopener'];
                    $badgeText = 'دوفالو';
                    $linkType = $link['link_type'] ?? 'follow';

                    if ($linkType === 'nofollow') {
                        $relParts[] = 'nofollow';
                        $badgeText = 'نوفالو';
                    } elseif ($linkType === '301') {
                        $url = 'redirect.php?id=' . urlencode($link['id']);
                        $badgeText = 'ریدایرکت 301';
                    }

                    $relAttribute = 'rel="' . implode(' ', $relParts) . '"';
                    ?>
                    <a class="links__item" href="<?= htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" <?= $relAttribute; ?> style="--btn-color: <?= htmlspecialchars($link['color'] ?? '#4b5fff', ENT_QUOTES, 'UTF-8'); ?>">
                        <span class="links__icon"><i class="<?= htmlspecialchars($link['icon'] ?? 'fa-solid fa-link', ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></span>
                        <span class="links__content">
                            <span class="links__text"><?= htmlspecialchars($link['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php if (!empty($badgeText)): ?>
                                <span class="links__badge"><?= htmlspecialchars($badgeText, ENT_QUOTES, 'UTF-8'); ?></span>
                            <?php endif; ?>
                        </span>
                        <span class="links__arrow" aria-hidden="true"><i class="fa-solid fa-arrow-left"></i></span>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
        <footer class="page-footer">
            <a class="page-footer__link" href="admin.php">ورود به پنل مدیریت</a>
        </footer>
    </main>
</body>
</html>
