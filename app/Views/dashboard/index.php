<section class="console-shell">
    <div class="console-hero">
        <div>
            <p class="eyebrow">MYFOLIO / CREATOR CONSOLE</p>
            <h1>สร้างหลักฐานให้เห็น <span>ระบบคิด</span></h1>
            <p class="hero-copy">พื้นที่จัดการผลงาน Programming, IoT innovation และผลลัพธ์การพัฒนางานในมุมที่กรรมการอ่านแล้วเข้าใจได้ทันที</p>
        </div>
        <?php if (($user['role'] ?? '') === 'admin'): ?><a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/portfolio/new" class="primary-action"><span>+</span> เพิ่มผลงานใหม่</a><?php endif; ?>
    </div>

    <div class="stats-grid">
        <article class="stat-card stat-cyan"><span>ผลงานทั้งหมด</span><strong><?= (int) $stats['items'] ?></strong><small>items in portfolio</small></article>
        <article class="stat-card stat-lime"><span>เผยแพร่แล้ว</span><strong><?= (int) $stats['published'] ?></strong><small>visible to reviewers</small></article>
        <article class="stat-card stat-amber"><span>หมวดหลักฐาน</span><strong><?= (int) $stats['categories'] ?></strong><small>evaluation tracks</small></article>
    </div>

    <div class="workspace-grid">
        <section class="panel-block">
            <div class="section-heading"><div><p class="eyebrow">LATEST BUILD LOG</p><h2>ผลงานล่าสุด</h2></div><span class="live-dot">LIVE</span></div>
            <?php if ($items === []): ?>
                <div class="empty-state"><div class="empty-icon">⌁</div><h3>เริ่มบันทึกชิ้นแรกของคุณ</h3><p>เล่าโจทย์ วิธีคิด เทคโนโลยี และผลลัพธ์ให้กลายเป็นหลักฐานที่จับต้องได้</p><?php if (($user['role'] ?? '') === 'admin'): ?><a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/portfolio/new" class="text-action">สร้าง portfolio item →</a><?php endif; ?></div>
            <?php else: ?>
                <div class="item-list">
                    <?php foreach ($items as $item): ?>
                        <article class="item-row"><div class="item-index">#<?= str_pad((string) $item['id'], 3, '0', STR_PAD_LEFT) ?></div><div class="item-content"><div class="item-meta"><span>พ.ศ. <?= (int) $item['evaluation_year'] ?> · <?= $item['evaluation_round'] === 'salary_march' ? 'มีนาคม' : 'กันยายน' ?></span><b class="status-<?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($item['status'], ENT_QUOTES, 'UTF-8') ?></b></div><h3><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($item['category_name'], ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($item['description'] ?: 'ยังไม่มีคำอธิบาย', ENT_QUOTES, 'UTF-8') ?></p><div class="item-actions"><a href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/portfolio/<?= (int) $item['id'] ?>/edit" class="text-action">แก้ไขผลงาน ↗</a><form method="post" action="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/portfolio/<?= (int) $item['id'] ?>/delete" onsubmit="return confirm('ลบผลงานนี้และไฟล์แนบทั้งหมดหรือไม่?')"><?= csrf_field() ?><button type="submit" class="danger-action">ลบ</button></form></div></div></article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <aside class="panel-block signal-panel"><p class="eyebrow">SYSTEM SIGNAL</p><div class="signal-graphic"><span></span><span></span><span></span><span></span><span></span></div><h2>Portfolio health</h2><p>โครงสร้างพร้อมสำหรับการเติมหลักฐานและต่อยอดเป็น public showcase</p><div class="signal-line"><span>CONTENT PIPELINE</span><b>READY</b></div><div class="signal-line"><span>REVIEW ACCESS</span><b>RBAC ON</b></div></aside>
    </div>
</section>