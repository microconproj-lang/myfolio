<?php
$grouped = [];
foreach ($items as $item) {
    $grouped[$item['evaluation_year']][$item['evaluation_round']][$item['category_name']][] = $item;
}
$years = array_keys($grouped);
rsort($years, SORT_NUMERIC);
?>
<section class="portfolio-page portfolio-hero">
    <div class="hero-orbit" aria-hidden="true"><span></span><span></span><span></span></div>
    <div class="hero-copy-block">
        <p class="eyebrow">PUBLIC PORTFOLIO / FIELD NOTES</p>
        <h1>ผลงานที่ทำให้<br><span>การประเมินเห็นระบบคิด</span></h1>
        <p class="hero-copy">พื้นที่แสดงหลักฐาน ผลลัพธ์ และนวัตกรรมจากการลงมือทำจริง ตั้งแต่โค้ดบน GitHub ไปจนถึงระบบ IoT ที่ทำงานในห้องเรียน</p>
        <div class="hero-metrics"><span><b><?= count($items) ?></b> published items</span><span><b><?= count($years) ?></b> evaluation years</span></div>
    </div>
    <figure class="hero-portrait"><img src="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/assets/img/admin.jpg" alt="ผู้จัดทำ MyFolio" loading="eager"><figcaption>PROFILE / CREATIVE PRACTICE</figcaption></figure>
</section>

<section class="public-work" id="published-work">
    <div class="section-heading work-heading"><div><p class="eyebrow">PUBLISHED WORK / BY EVALUATION CYCLE</p><h2>บันทึกการสร้าง</h2></div><span class="live-dot"><?= count($items) ?> ITEMS</span></div>
    <?php if ($items === []): ?>
        <p class="muted-note">ยังไม่มีผลงานที่เผยแพร่</p>
    <?php else: ?>
        <div class="year-filter" role="group" aria-label="กรองตามปี พ.ศ."><button type="button" class="filter-chip is-active" data-year-filter="all">ทั้งหมด</button><?php foreach ($years as $year): ?><button type="button" class="filter-chip" data-year-filter="<?= (int) $year ?>">พ.ศ. <?= (int) $year ?></button><?php endforeach; ?></div>
        <div class="year-controls"><button type="button" class="text-action" data-expand-all>ขยายทุกปี</button><span>/</span><button type="button" class="text-action" data-collapse-all>ยุบทุกปี</button></div>
        <div class="year-list">
            <?php foreach ($years as $year): ?>
                <section class="year-group<?= $year === $years[0] ? ' is-open' : '' ?>" data-year="<?= (int) $year ?>">
                    <?php $yearCount = 0; foreach ($grouped[$year] as $yearRound) { foreach ($yearRound as $yearCategoryItems) { $yearCount += count($yearCategoryItems); } } ?>
                    <button type="button" class="year-toggle" aria-expanded="<?= $year === $years[0] ? 'true' : 'false' ?>"><span class="year-badge">พ.ศ. <?= (int) $year ?></span><span class="year-summary"><?= $yearCount ?> รายการ</span><span class="year-chevron" aria-hidden="true">+</span></button>
                    <div class="year-content">
                        <?php foreach ($grouped[$year] as $round => $categoryItems): ?>
                            <div class="round-group"><div class="round-heading"><span><?= $round === 'salary_march' ? 'ประเมินเลื่อนขั้นเงินเดือน' : 'ประเมิน วPA' ?></span><b><?= $round === 'salary_march' ? 'มีนาคม' : 'กันยายน' ?></b></div>
                                <?php foreach ($categoryItems as $categoryName => $categoryWorks): ?>
                                    <section class="part-group"><h3><?= htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8') ?></h3><div class="public-grid">
                                        <?php foreach ($categoryWorks as $item): ?>
                                            <article class="public-card">
                                                <?php if (!empty($item['thumbnail_file_id'])): ?><img class="work-thumbnail" src="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/file/view/<?= (int) $item['thumbnail_file_id'] ?>" alt="Thumbnail: <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy"><?php else: ?><div class="work-thumbnail placeholder-thumb"><span>MYFOLIO / <?= str_pad((string) $item['id'], 3, '0', STR_PAD_LEFT) ?></span><i></i><i></i><i></i></div><?php endif; ?>
                                                <span class="card-kicker">พ.ศ. <?= (int) $item['evaluation_year'] ?> · <?= $round === 'salary_march' ? 'มีนาคม' : 'กันยายน' ?></span><h4><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></h4><p><?= htmlspecialchars($item['description'] ?: 'สำรวจรายละเอียดผลงานและผลลัพธ์จากการพัฒนา', ENT_QUOTES, 'UTF-8') ?></p><?php if ($item['github_url']): ?><a href="<?= htmlspecialchars($item['github_url'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener" class="text-action">ดู repository ↗</a><?php endif; ?>
                                            </article>
                                        <?php endforeach; ?>
                                    </div></section>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
<button type="button" class="go-top" data-go-top aria-label="กลับขึ้นด้านบน" title="กลับขึ้นด้านบน">↑</button>
<script>
(() => {
    const groups = [...document.querySelectorAll('[data-year]')];
    const chips = [...document.querySelectorAll('[data-year-filter]')];
    const setOpen = (group, open) => {
        group.classList.toggle('is-open', open);
        group.querySelector('.year-toggle').setAttribute('aria-expanded', open ? 'true' : 'false');
    };
    groups.forEach((group) => group.querySelector('.year-toggle').addEventListener('click', () => setOpen(group, !group.classList.contains('is-open'))));
    chips.forEach((chip) => chip.addEventListener('click', () => {
        chips.forEach((item) => item.classList.remove('is-active'));
        chip.classList.add('is-active');
        const selected = chip.dataset.yearFilter;
        groups.forEach((group) => {
            const visible = selected === 'all' || group.dataset.year === selected;
            group.hidden = !visible;
            if (visible && selected !== 'all') setOpen(group, true);
        });
    }));
    document.querySelector('[data-expand-all]')?.addEventListener('click', () => groups.filter((group) => !group.hidden).forEach((group) => setOpen(group, true)));
    document.querySelector('[data-collapse-all]')?.addEventListener('click', () => groups.filter((group) => !group.hidden).forEach((group) => setOpen(group, false)));
    const topButton = document.querySelector('[data-go-top]');
    window.addEventListener('scroll', () => topButton.classList.toggle('is-visible', window.scrollY > 480), { passive: true });
    topButton.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
})();
</script>
