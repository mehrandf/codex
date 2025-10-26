<?php
require __DIR__ . '/functions.php';
ensure_data_file();
start_session();
$config = get_config();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === $config['admin_username'] && password_verify($password, $config['admin_password_hash'])) {
            $_SESSION['logged_in'] = true;
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'با موفقیت وارد شدید.'];
            header('Location: admin.php');
            exit;
        }
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'نام کاربری یا رمز عبور اشتباه است.'];
        header('Location: admin.php');
        exit;
    }

    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: admin.php');
        exit;
    }

    require_login();
    $links = load_links();

    if ($action === 'create' || $action === 'update') {
        $text = sanitize_text($_POST['text'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $color = sanitize_color($_POST['color'] ?? '#4b5fff');
        $icon = sanitize_text($_POST['icon'] ?? 'fa-solid fa-link');
        $linkType = $_POST['link_type'] ?? 'follow';
        $displayOrder = (int) ($_POST['display_order'] ?? 0);

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'آدرس لینک معتبر نیست.'];
            header('Location: admin.php');
            exit;
        }

        $allowedTypes = ['follow', 'nofollow', '301'];
        if (!in_array($linkType, $allowedTypes, true)) {
            $linkType = 'follow';
        }

        $data = [
            'text' => $text,
            'url' => $url,
            'color' => $color,
            'icon' => $icon ?: 'fa-solid fa-link',
            'link_type' => $linkType,
            'display_order' => $displayOrder,
            'updated_at' => date('c'),
        ];

        if ($action === 'create') {
            $data['id'] = generate_id();
            $data['created_at'] = date('c');
            $links[] = $data;
            save_links($links);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'لینک جدید با موفقیت اضافه شد.'];
        } else {
            $id = $_POST['id'] ?? '';
            if (!$id) {
                $_SESSION['flash'] = ['type' => 'error', 'message' => 'شناسه لینک نامعتبر است.'];
                header('Location: admin.php');
                exit;
            }
            $links = update_link($links, $id, $data);
            save_links($links);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'لینک با موفقیت بروزرسانی شد.'];
        }

        header('Location: admin.php');
        exit;
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $links = delete_link($links, $id);
            save_links($links);
            $_SESSION['flash'] = ['type' => 'success', 'message' => 'لینک حذف شد.'];
        }
        header('Location: admin.php');
        exit;
    }
}

$links = load_links();
$isLoggedIn = !empty($_SESSION['logged_in']);
$editingId = $_GET['edit'] ?? '';
$editingLink = $editingId ? find_link_by_id($links, $editingId) : null;

$linkTypeCounts = [
    'follow' => 0,
    'nofollow' => 0,
    '301' => 0,
];
$latestUpdateTimestamp = null;

foreach ($links as $link) {
    $type = $link['link_type'] ?? 'follow';
    if (!array_key_exists($type, $linkTypeCounts)) {
        $linkTypeCounts[$type] = 0;
    }
    $linkTypeCounts[$type]++;

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
$previewLinks = array_slice($links, 0, 6);
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت لینک‌ها</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-8uyTQG7n2AFDzy83H8XTur2qxGn8pY/+bexdFv+DE5jBqFaUG2RgxN6E466+vWXTjhBMWrMUR3pvN8F2Zp4FZw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="admin-page">
    <div class="admin-shell">
        <header class="admin-header">
            <div class="admin-brand">
                <span class="admin-brand__icon"><i class="fa-solid fa-sparkles" aria-hidden="true"></i></span>
                <div class="admin-brand__text">
                    <h1 class="admin-title">پنل مدیریت لینک‌ها</h1>
                    <p class="admin-subtitle">تمامی لینک‌های صفحه شخصی خود را از اینجا کنترل کنید.</p>
                </div>
            </div>
            <div class="admin-actions">
                <a class="button button--ghost" href="index.php" target="_blank" rel="noopener">
                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                    <span>مشاهده صفحه عمومی</span>
                </a>
                <?php if ($isLoggedIn): ?>
                    <form method="post" action="admin.php" class="logout-form">
                        <input type="hidden" name="action" value="logout">
                        <button class="button button--secondary" type="submit">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i>
                            <span>خروج</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="flash-message <?= $flash['type'] === 'error' ? 'flash-message--error' : 'flash-message--success'; ?>">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!$isLoggedIn): ?>
            <section class="card login-card">
                <div class="card__header">
                    <h2 class="card__title">ورود به حساب کاربری</h2>
                    <p class="card__subtitle">برای مدیریت لینک‌ها، اطلاعات ورود خود را وارد کنید.</p>
                </div>
                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="login">
                    <div class="field">
                        <label for="username">نام کاربری</label>
                        <input id="username" name="username" type="text" placeholder="نام کاربری" required>
                    </div>
                    <div class="field">
                        <label for="password">رمز عبور</label>
                        <input id="password" name="password" type="password" placeholder="رمز عبور" required>
                    </div>
                    <button class="button" type="submit">
                        <i class="fa-solid fa-arrow-right-to-bracket" aria-hidden="true"></i>
                        <span>ورود</span>
                    </button>
                </form>
                <p class="login-card__hint">نام کاربری <code>mehrandfme</code> · رمز عبور <code>Mm@123qweasd</code></p>
            </section>
        <?php else: ?>
            <section class="stats-grid">
                <article class="stat-card">
                    <span class="stat-card__icon"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span>
                    <span class="stat-card__label">تعداد کل لینک‌ها</span>
                    <span class="stat-card__value"><?= htmlspecialchars((string) count($links), ENT_QUOTES, 'UTF-8'); ?></span>
                </article>
                <article class="stat-card">
                    <span class="stat-card__icon"><i class="fa-solid fa-link" aria-hidden="true"></i></span>
                    <span class="stat-card__label">لینک‌های دوفالو</span>
                    <span class="stat-card__value"><?= htmlspecialchars((string) ($linkTypeCounts['follow'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></span>
                </article>
                <article class="stat-card">
                    <span class="stat-card__icon"><i class="fa-solid fa-ban" aria-hidden="true"></i></span>
                    <span class="stat-card__label">لینک‌های نوفالو</span>
                    <span class="stat-card__value"><?= htmlspecialchars((string) ($linkTypeCounts['nofollow'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></span>
                </article>
                <article class="stat-card">
                    <span class="stat-card__icon"><i class="fa-solid fa-share" aria-hidden="true"></i></span>
                    <span class="stat-card__label">ریدایرکت 301</span>
                    <span class="stat-card__value"><?= htmlspecialchars((string) ($linkTypeCounts['301'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></span>
                </article>
                <?php if ($lastUpdatedLabel !== null): ?>
                    <article class="stat-card stat-card--accent">
                        <span class="stat-card__icon"><i class="fa-solid fa-clock" aria-hidden="true"></i></span>
                        <span class="stat-card__label">آخرین بروزرسانی</span>
                        <span class="stat-card__value"><?= htmlspecialchars($lastUpdatedLabel, ENT_QUOTES, 'UTF-8'); ?></span>
                    </article>
                <?php endif; ?>
            </section>

            <div class="admin-grid">
                <section class="card">
                    <div class="card__header">
                        <h2 class="card__title">افزودن لینک جدید</h2>
                        <p class="card__subtitle">برای افزودن لینک جدید فرم زیر را تکمیل و ذخیره کنید.</p>
                    </div>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="create">
                        <div class="field">
                            <label for="text">متن دکمه</label>
                            <input id="text" name="text" type="text" placeholder="مثلاً اینستاگرام" required>
                        </div>
                        <div class="field">
                            <label for="url">آدرس لینک</label>
                            <input id="url" name="url" type="url" placeholder="https://example.com" required>
                        </div>
                        <div class="field">
                            <label for="color">رنگ دکمه</label>
                            <input id="color" name="color" type="color" value="#4b5fff">
                        </div>
                        <div class="field">
                            <label for="icon">کلاس آیکون فانت‌آوسام</label>
                            <input id="icon" name="icon" type="text" placeholder="fa-brands fa-instagram">
                            <span class="field__hint"><a href="https://fontawesome.com/search" target="_blank" rel="noopener">لیست آیکون‌ها</a></span>
                        </div>
                        <div class="field">
                            <label for="link_type">نوع لینک</label>
                            <select id="link_type" name="link_type">
                                <option value="follow">دوفالو</option>
                                <option value="nofollow">نوفالو</option>
                                <option value="301">ریدایرکت 301</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="display_order">اولویت نمایش</label>
                            <input id="display_order" name="display_order" type="number" value="0">
                        </div>
                        <button class="button" type="submit">
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            <span>ذخیره لینک</span>
                        </button>
                    </form>
                </section>

                <aside class="card card--preview">
                    <div class="card__header">
                        <h2 class="card__title">پیش‌نمایش لینک‌ها</h2>
                        <p class="card__subtitle">ظاهر دکمه‌ها در صفحه عمومی</p>
                    </div>
                    <?php if (empty($previewLinks)): ?>
                        <div class="empty-state">
                            <i class="fa-regular fa-face-smile" aria-hidden="true"></i>
                            <p>هنوز لینکی ثبت نشده است.</p>
                        </div>
                    <?php else: ?>
                        <div class="link-preview">
                            <?php foreach ($previewLinks as $previewLink): ?>
                                <?php
                                $previewColor = $previewLink['color'] ?? '#4b5fff';
                                $previewIcon = $previewLink['icon'] ?? 'fa-solid fa-link';
                                $previewText = $previewLink['text'] ?? '';
                                $previewType = $previewLink['link_type'] ?? 'follow';
                                $previewBadge = 'دوفالو';
                                if ($previewType === 'nofollow') {
                                    $previewBadge = 'نوفالو';
                                } elseif ($previewType === '301') {
                                    $previewBadge = 'ریدایرکت 301';
                                }
                                ?>
                                <div class="link-preview__item" style="--btn-color: <?= htmlspecialchars($previewColor, ENT_QUOTES, 'UTF-8'); ?>">
                                    <span class="link-preview__icon"><i class="<?= htmlspecialchars($previewIcon, ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></span>
                                    <span class="link-preview__text"><?= htmlspecialchars($previewText, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <span class="link-preview__badge"><?= htmlspecialchars($previewBadge, ENT_QUOTES, 'UTF-8'); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($links) > count($previewLinks)): ?>
                            <p class="link-preview__more">و <?= htmlspecialchars((string) (count($links) - count($previewLinks)), ENT_QUOTES, 'UTF-8'); ?> لینک دیگر…</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </aside>
            </div>

            <?php if ($editingLink): ?>
                <section class="card card--accent">
                    <div class="card__header">
                        <h2 class="card__title">ویرایش لینک منتخب</h2>
                        <p class="card__subtitle">در حال ویرایش «<?= htmlspecialchars($editingLink['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>» هستید.</p>
                    </div>
                    <form method="post" class="form-grid">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= htmlspecialchars($editingLink['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="field">
                            <label for="edit-text">متن دکمه</label>
                            <input id="edit-text" name="text" type="text" value="<?= htmlspecialchars($editingLink['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="edit-url">آدرس لینک</label>
                            <input id="edit-url" name="url" type="url" value="<?= htmlspecialchars($editingLink['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                        </div>
                        <div class="field">
                            <label for="edit-color">رنگ دکمه</label>
                            <input id="edit-color" name="color" type="color" value="<?= htmlspecialchars($editingLink['color'] ?? '#4b5fff', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field">
                            <label for="edit-icon">کلاس آیکون فانت‌آوسام</label>
                            <input id="edit-icon" name="icon" type="text" value="<?= htmlspecialchars($editingLink['icon'] ?? 'fa-solid fa-link', ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <div class="field">
                            <label for="edit-link-type">نوع لینک</label>
                            <select id="edit-link-type" name="link_type">
                                <option value="follow" <?= (($editingLink['link_type'] ?? 'follow') === 'follow') ? 'selected' : ''; ?>>دوفالو</option>
                                <option value="nofollow" <?= (($editingLink['link_type'] ?? 'follow') === 'nofollow') ? 'selected' : ''; ?>>نوفالو</option>
                                <option value="301" <?= (($editingLink['link_type'] ?? 'follow') === '301') ? 'selected' : ''; ?>>ریدایرکت 301</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="edit-display-order">اولویت نمایش</label>
                            <input id="edit-display-order" name="display_order" type="number" value="<?= htmlspecialchars((string)($editingLink['display_order'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <button class="button" type="submit">
                            <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                            <span>بروزرسانی لینک</span>
                        </button>
                    </form>
                </section>
            <?php endif; ?>

            <section class="card card--table">
                <div class="card__header">
                    <h2 class="card__title">لیست لینک‌های ذخیره شده</h2>
                    <p class="card__subtitle">لینک‌ها را مرتب، ویرایش یا حذف کنید.</p>
                </div>
                <?php if (empty($links)): ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-circle-plus" aria-hidden="true"></i>
                        <p>هیچ لینکی ثبت نشده است.</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table class="links-table">
                            <thead>
                                <tr>
                                    <th>متن</th>
                                    <th>آدرس</th>
                                    <th>آیکون</th>
                                    <th>نوع لینک</th>
                                    <th>اولویت</th>
                                    <th>اقدامات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($links as $item): ?>
                                    <?php
                                    $typeLabel = 'دوفالو';
                                    $typeClass = 'badge--follow';
                                    $linkType = $item['link_type'] ?? 'follow';
                                    if ($linkType === 'nofollow') {
                                        $typeLabel = 'نوفالو';
                                        $typeClass = 'badge--nofollow';
                                    } elseif ($linkType === '301') {
                                        $typeLabel = 'ریدایرکت 301';
                                        $typeClass = 'badge--redirect';
                                    }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="table-url">
                                            <a href="<?= htmlspecialchars($item['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">مشاهده</a>
                                        </td>
                                        <td><i class="<?= htmlspecialchars($item['icon'] ?? 'fa-solid fa-link', ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></td>
                                        <td><span class="badge <?= $typeClass; ?>"><?= htmlspecialchars($typeLabel, ENT_QUOTES, 'UTF-8'); ?></span></td>
                                        <td><?= htmlspecialchars((string)($item['display_order'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a class="button button--secondary" href="admin.php?edit=<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                                    <span>ویرایش</span>
                                                </a>
                                                <form method="post" class="inline-form" onsubmit="return confirm('آیا از حذف این لینک مطمئن هستید؟');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button class="button button--danger" type="submit">
                                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                        <span>حذف</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </div>
</body>
</html>
