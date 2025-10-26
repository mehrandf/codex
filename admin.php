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
    <main class="admin-container">
        <header style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
            <div>
                <h1 style="margin:0;font-size:1.8rem;">پنل مدیریت لینک‌ها</h1>
                <p style="margin:0.4rem 0 0;color:#6a6f92;">لطفاً لینک‌های خود را در اینجا مدیریت کنید.</p>
            </div>
            <?php if ($isLoggedIn): ?>
                <form method="post" action="admin.php" style="margin:0;">
                    <input type="hidden" name="action" value="logout">
                    <button class="button button--secondary" type="submit">خروج</button>
                </form>
            <?php endif; ?>
        </header>

        <?php if ($flash): ?>
            <div class="flash-message" style="background: <?= $flash['type'] === 'error' ? '#ffe8e8' : '#eff6ff'; ?>;color: <?= $flash['type'] === 'error' ? '#a42323' : '#1a3c7a'; ?>;border-color: <?= $flash['type'] === 'error' ? '#ffc4c4' : '#cfe0ff'; ?>;">
                <?= htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (!$isLoggedIn): ?>
            <form method="post" class="login-form">
                <input type="hidden" name="action" value="login">
                <div class="field">
                    <label for="username">نام کاربری</label>
                    <input id="username" name="username" type="text" placeholder="نام کاربری" required>
                </div>
                <div class="field">
                    <label for="password">رمز عبور</label>
                    <input id="password" name="password" type="password" placeholder="رمز عبور" required>
                </div>
                <button class="button" type="submit">ورود</button>
            </form>
        <?php else: ?>
            <section>
                <h2 style="margin-top:0;">افزودن لینک جدید</h2>
                <form method="post" style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));align-items:end;background:#f7f8ff;padding:1.5rem;border-radius:16px;">
                    <input type="hidden" name="action" value="create">
                    <div class="field">
                        <label for="text">متن دکمه</label>
                        <input id="text" name="text" type="text" required>
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
                    </div>
                    <div class="field">
                        <label for="link_type">نوع لینک</label>
                        <select id="link_type" name="link_type">
                            <option value="follow">Follow</option>
                            <option value="nofollow">NoFollow</option>
                            <option value="301">ریدایرکت 301</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="display_order">اولویت نمایش</label>
                        <input id="display_order" name="display_order" type="number" value="0">
                    </div>
                    <button class="button" type="submit" style="grid-column:1 / -1;justify-self:flex-start;">ذخیره لینک</button>
                </form>
            </section>

            <?php if ($editingLink): ?>
                <section style="margin-top:2.5rem;">
                    <h2 style="margin-top:0;">ویرایش لینک</h2>
                    <form method="post" style="display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));align-items:end;background:#fff7f7;padding:1.5rem;border-radius:16px;border:1px solid #ffd6d6;">
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
                                <option value="follow" <?= (($editingLink['link_type'] ?? 'follow') === 'follow') ? 'selected' : ''; ?>>Follow</option>
                                <option value="nofollow" <?= (($editingLink['link_type'] ?? 'follow') === 'nofollow') ? 'selected' : ''; ?>>NoFollow</option>
                                <option value="301" <?= (($editingLink['link_type'] ?? 'follow') === '301') ? 'selected' : ''; ?>>ریدایرکت 301</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="edit-display-order">اولویت نمایش</label>
                            <input id="edit-display-order" name="display_order" type="number" value="<?= htmlspecialchars((string)($editingLink['display_order'] ?? 0), ENT_QUOTES, 'UTF-8'); ?>">
                        </div>
                        <button class="button" type="submit" style="grid-column:1 / -1;justify-self:flex-start;">بروزرسانی لینک</button>
                    </form>
                </section>
            <?php endif; ?>

            <section style="margin-top:2.5rem;">
                <h2 style="margin-top:0;">لیست لینک‌ها</h2>
                <?php if (empty($links)): ?>
                    <p style="color:#6a6f92;">هیچ لینکی ثبت نشده است.</p>
                <?php else: ?>
                    <div style="overflow-x:auto;">
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
                                    <tr>
                                        <td><?= htmlspecialchars($item['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td style="direction:ltr;">
                                            <a href="<?= htmlspecialchars($item['url'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">مشاهده</a>
                                        </td>
                                        <td><i class="<?= htmlspecialchars($item['icon'] ?? 'fa-solid fa-link', ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></td>
                                        <td><?= htmlspecialchars($item['link_type'] ?? 'follow', ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td><?= htmlspecialchars((string)($item['display_order'] ?? 0), ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td>
                                            <div class="actions">
                                                <a class="button button--secondary" href="admin.php?edit=<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>">ویرایش</a>
                                                <form method="post" onsubmit="return confirm('آیا از حذف این لینک مطمئن هستید؟');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                    <button class="button" style="background:#ff6b6b;" type="submit">حذف</button>
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
    </main>
</body>
</html>
