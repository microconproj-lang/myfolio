<section class="form-page">
    <?php $isEdit = $item !== null; $formAction = $isEdit ? $app['url'] . '/portfolio/' . (int) $item['id'] : $app['url'] . '/portfolio'; ?>
    <a class="back-link" href="<?= htmlspecialchars($app['url'], ENT_QUOTES, 'UTF-8') ?>/dashboard">← กลับไป Console</a>
    <div class="form-intro"><p class="eyebrow"><?= $isEdit ? 'EDIT PORTFOLIO ITEM / ' . str_pad((string) $item['id'], 3, '0', STR_PAD_LEFT) : 'NEW PORTFOLIO ITEM / 01' ?></p><h1><?= $isEdit ? 'ปรับหลักฐานให้คมขึ้น' : 'เปลี่ยนงานที่ทำให้เป็นหลักฐาน' ?></h1><p>บันทึกทั้งโจทย์ วิธีแก้ และผลลัพธ์ เพื่อให้ผลงานของคุณมี narrative ที่ชัดเจนกว่าการแนบไฟล์อย่างเดียว</p></div>
    <?php $selectedPart = $item['assessment_part'] ?? 'part_1'; ?>
    <form method="post" action="<?= htmlspecialchars($formAction, ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="portfolio-form">
        <?= csrf_field() ?>
        <div class="form-main">
            <label>คำอธิบาย / ผลลัพธ์<textarea name="description" rows="7" placeholder="โจทย์คืออะไร คุณออกแบบระบบอย่างไร ใช้เทคโนโลยีอะไร และเกิดผลลัพธ์อะไร"><?= htmlspecialchars($item['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
            <label>ลิงก์ repository หรือ demo<input type="url" name="github_url" value="<?= htmlspecialchars($item['github_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="https://github.com/your-name/project"></label>
            <label>ไฟล์หลักฐาน <span>(สูงสุด 10 ไฟล์, ไฟล์ละไม่เกิน 20 MB)</span><input type="file" name="evidence[]" multiple accept="application/pdf,image/jpeg,image/png,image/webp"><small class="field-hint">รองรับ PDF, JPG, PNG และ WebP ระบบจะเก็บไฟล์ในพื้นที่ private</small></label>
        </div>
        <aside class="form-side">
            <label>ปี พ.ศ. <span>*</span><input name="evaluation_year" type="number" min="2500" max="2700" value="<?= (int) ($item['evaluation_year'] ?? date('Y') + 543) ?>" required></label>
            <label>รอบประเมิน <span>*</span><select name="evaluation_round" required><option value="vpa_september" <?= ($item['evaluation_round'] ?? '') === 'vpa_september' ? 'selected' : '' ?>>ประเมิน วPA · กันยายน</option><option value="salary_march" <?= ($item['evaluation_round'] ?? '') === 'salary_march' ? 'selected' : '' ?>>ประเมินเลื่อนขั้นเงินเดือน · มีนาคม</option></select></label>
            <label>ส่วนประเมิน <span>*</span><select name="assessment_part" id="assessment-part" required><option value="part_1" <?= $selectedPart === 'part_1' ? 'selected' : '' ?>>ส่วนที่ 1 ข้อตกลงในการพัฒนางานตามมาตรฐานตำแหน่ง</option><option value="part_2" <?= $selectedPart === 'part_2' ? 'selected' : '' ?>>ส่วนที่ 2 ข้อตกลงในการพัฒนางาน</option></select></label>
            <label>ส่วนงาน <span>*</span><select name="category_id" id="work-section" required><option value="">เลือกส่วนงาน</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" data-part="<?= htmlspecialchars($category['assessment_part'], ENT_QUOTES, 'UTF-8') ?>" <?= (int) ($item['category_id'] ?? 0) === (int) $category['id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></label>
            <label>สถานะ<select name="status"><option value="draft" <?= ($item['status'] ?? '') === 'draft' ? 'selected' : '' ?>>เก็บเป็นฉบับร่าง</option><option value="published" <?= ($item['status'] ?? '') === 'published' ? 'selected' : '' ?>>เผยแพร่ทันที</option></select></label>
            <div class="form-note"><strong>TIP / STORY ARC</strong><p>ผลงานที่ดีควรตอบ 3 คำถาม: ปัญหาคืออะไร? คุณสร้างอะไร? ผลลัพธ์เปลี่ยนแปลงอย่างไร?</p></div>
            <button class="primary-action form-submit" type="submit"><span>↗</span> <?= $isEdit ? 'บันทึกการแก้ไข' : 'บันทึกผลงาน' ?></button>
        </aside>
    </form>
</section>
<script>
    (() => {
        const partSelect = document.getElementById('assessment-part');
        const workSelect = document.getElementById('work-section');
        const syncWorkSections = () => {
            const part = partSelect.value;
            let selectedVisible = false;
            [...workSelect.options].forEach((option, index) => {
                if (index === 0) return;
                const visible = option.dataset.part === part;
                option.hidden = !visible;
                if (!visible && option.selected) option.selected = false;
                if (visible && option.selected) selectedVisible = true;
            });
            if (!selectedVisible) workSelect.value = '';
        };
        partSelect.addEventListener('change', syncWorkSections);
        syncWorkSections();
    })();
</script>