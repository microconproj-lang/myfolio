<nav class="border-b border-cyan-400/10 bg-slate-950/80 backdrop-blur">
    <div class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8">
        <a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>" class="brand-mark"><span class="brand-pulse"></span>MYFOLIO<span class="brand-slash">/</span>PA</a>
        <div class="flex items-center gap-4 text-sm text-slate-300">
            <a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/portfolio" class="nav-link">ผลงาน</a>
            <?php if (\MyFolio\Core\Auth::check()): ?>
                <?php if ((\MyFolio\Core\Auth::user()['role'] ?? '') === 'admin'): ?><a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/dashboard" class="nav-link">Console</a><?php endif; ?>
                <a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/logout" class="nav-logout">ออกจากระบบ</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/login" class="rounded border border-cyan-400/50 px-3 py-1.5 text-cyan-300 hover:bg-cyan-400/10">เข้าสู่ระบบ</a>
            <?php endif; ?>
        </div>
    </div>
</nav>