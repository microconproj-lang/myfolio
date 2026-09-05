แผนพัฒนาระบบ Web Portfolio สำหรับการประเมิน วPA ของข้าราชการครู
> โปรเจกต์: `portal.pccpl.ac.th/myfolio`
> Stack: PHP 8.x (PDO OOP) + MySQL + Tailwind CSS + Google OAuth 2.0
---
⚠️ 0. ข้อควรระวังด้านความปลอดภัยก่อนเริ่มงาน (สำคัญมาก)
ในสเปกที่ส่งมา มี Google OAuth Client Secret ปรากฏเป็นข้อความล้วน (plaintext) อยู่ในเอกสาร ซึ่งถือว่าเป็นความลับที่รั่วไหลไปแล้วในทางปฏิบัติ (ถูกพิมพ์/บันทึกไว้ในที่ที่ไม่ใช่ที่เก็บลับ) ก่อนขึ้น production จริงควรทำดังนี้:
หมุนเปลี่ยน (Rotate) Client Secret ใหม่ทันที ผ่าน Google Cloud Console → Credentials → OAuth 2.0 Client → Reset Secret
ห้าม hardcode Client ID/Secret ในไฟล์ PHP หรือใน repository ใดๆ (รวมถึงเอกสารแผนงานนี้) — ให้เก็บใน `.env` ที่ไม่ถูก commit เข้า git (`.gitignore` ต้องมี `.env`)
ใช้ไลบรารีอ่าน env เช่น `vlucas/phpdotenv` แล้วเรียกผ่าน `getenv('GOOGLE_CLIENT_SECRET')`
ตั้งค่า Authorized redirect URI ใน Google Console ให้ตรงกับ `https://portal.pccpl.ac.th/myfolio/auth/google-callback.php` เท่านั้น (จำกัด domain)
จำกัด OAuth consent screen ให้เป็น Internal (ถ้าใช้ Google Workspace for Education) เพื่อไม่ให้บัญชีนอกโดเมนล็อกอินได้
เอกสารนี้จึงจะใช้ `{{GOOGLE_CLIENT_ID}}` / `{{GOOGLE_CLIENT_SECRET}}` เป็น placeholder แทนของจริงทั้งหมด
---
1. ภาพรวมสถาปัตยกรรม (Architecture Overview)
```
Browser (Tailwind + Alpine.js/Vanilla JS + PDF.js)
        │
        ▼
   PHP 8.x (Front Controller / MVC-lite)
        │
        ├── Auth Layer (Google OAuth 2.0 + Session)
        ├── RBAC Middleware (Role Guard)
        ├── Controller Layer (CRUD, Menu, Comment)
        ├── Model Layer (PDO Prepared Statements)
        │
        ▼
   MySQL 8.x
        │
        ▼
   Storage (local /uploads หรือ S3-compatible) — เก็บไฟล์ PDF/รูปภาพ
```
แนวทางที่แนะนำ (ปรับจากสเปกเดิม):
ใช้โครงสร้างแบบ MVC-lite (ไม่ใช้ Framework ใหญ่) แต่แยก Controller/Model/View ชัดเจน เพื่อดูแลง่ายและ deploy เร็ว
ใช้ Front Controller pattern (`index.php` เป็นจุดเข้าเดียว + router array) แทนการกระจายไฟล์ .php ลอยๆ ทั่วโปรเจกต์ ลดความเสี่ยง path traversal / direct file access
แยก `public/` ออกจาก logic code — เว็บ root ชี้ไปที่ `public/` เท่านั้น ไฟล์ config/model ทั้งหมดอยู่นอก webroot
---
2. โครงสร้างโฟลเดอร์ (Project Structure)
```
myfolio/
├── public/                      ← webroot จริง (DocumentRoot ชี้มาที่นี่)
│   ├── index.php               ← Front controller
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── img/
│   └── uploads/                ← ไฟล์ที่ผู้ใช้อัปโหลด (แยกสิทธิ์การเข้าถึงแยกจาก uploads จริง)
│
├── app/
│   ├── Config/
│   │   ├── database.php
│   │   ├── oauth.php
│   │   └── app.php
│   ├── Core/
│   │   ├── Router.php
│   │   ├── Database.php        ← PDO Singleton
│   │   ├── Auth.php
│   │   ├── RBAC.php            ← Middleware ตรวจ role
│   │   └── Controller.php      ← Base controller
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProfileController.php
│   │   ├── PortfolioController.php   ← ส่วนที่ 1 (มาตรฐานตำแหน่ง)
│   │   ├── ChallengeController.php   ← ส่วนที่ 2 (ประเด็นท้าทาย)
│   │   ├── CommentController.php     ← Feedback กรรมการ
│   │   ├── MenuController.php        ← Dynamic menu (admin)
│   │   └── AdminController.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Menu.php
│   │   ├── PaItem.php
│   │   ├── PaFile.php
│   │   └── EvaluationComment.php
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── main.php
│   │   │   └── partials/ (navbar.php, sidebar.php, footer.php)
│   │   ├── auth/login.php
│   │   ├── dashboard/index.php
│   │   ├── portfolio/show.php
│   │   └── admin/menu-manage.php
│   └── Helpers/
│       ├── csrf.php
│       ├── sanitize.php
│       └── flash.php
│
├── storage/
│   └── logs/
├── database/
│   ├── schema.sql
│   └── seed.sql
├── vendor/                      ← composer packages (phpdotenv, google/apiclient เป็นต้น)
├── .env.example
├── .env                         ← (ไม่ commit)
├── .gitignore
├── composer.json
└── README.md
```
---
3. Database Schema (สรุปแนวคิด — รายละเอียดเต็มอยู่ใน `database/schema.sql`)
ตาราง	หน้าที่หลัก
`users`	เก็บผู้ใช้ที่ล็อกอินผ่าน Google (email, name, avatar, role, google_sub)
`roles`	master data: admin / committee / public (แยกจาก users เพื่อขยาย role ได้ในอนาคต)
`menus`	เมนู dynamic, รองรับ nested (parent_id), เรียงลำดับ (sort_order), เปิด/ปิดการแสดงผล
`pa_categories`	หมวดของโมดูล เช่น "ส่วนที่ 1.1", "ส่วนที่ 2.1" ผูกกับ menus
`pa_items`	ตัวเนื้อหาแต่ละชิ้น (หัวข้อ, คำอธิบาย, ลิงก์ GitHub, category_id)
`pa_files`	ไฟล์แนบของ pa_item (path, mime_type, page_count สำหรับ PDF, uploaded_by)
`evaluation_comments`	comment ของกรรมการ ผูกกับ pa_item_id + user_id (กรรมการ) + timestamp
`activity_logs`	(แนะนำเพิ่ม) log การเข้าถึง/แก้ไข เพื่อ audit trail
จุดที่ปรับจากสเปกเดิม:
แยก `roles` ออกจาก `users.role` enum แบบตายตัว → ใช้ FK ไปยังตาราง `roles` แทน เพื่อให้ admin เพิ่ม/ปรับ role ในอนาคตได้โดยไม่ต้อง ALTER TABLE
เพิ่ม `pa_categories` เป็นชั้นกลางระหว่าง `menus` กับ `pa_items` เพื่อรองรับกรณีเนื้อหาเยอะและอยากแยกแสดงผลเป็น tab/section
เพิ่ม `activity_logs` เพื่อ audit ว่าใครดู/ดาวน์โหลด/คอมเมนต์อะไรเมื่อไหร่ (สำคัญมากสำหรับระบบประเมินราชการ)
---
4. Authentication & RBAC
Flow:
ผู้ใช้กด "เข้าสู่ระบบด้วย Google" → redirect ไป Google OAuth consent
Google callback → `AuthController@googleCallback` รับ authorization code → แลก access token → ดึง email/profile
ตรวจสอบ email:
ตรงกับ `ADMIN_EMAIL` ใน `.env` → assign role `admin`
อยู่ในตาราง `committee_emails` (whitelist ที่ admin เพิ่มผ่านหน้า UI) → assign role `committee`
อื่นๆ ที่ล็อกอินสำเร็จ (หรือไม่ล็อกอินเลย) → role `public`
สร้าง session (`$_SESSION['user']`) + เก็บ role, เซ็ต CSRF token
RBAC Middleware (`app/Core/RBAC.php`):
ครอบทุก controller action ด้วย `RBAC::require('admin')` หรือ `RBAC::allow(['admin','committee'])`
ฝั่ง View ใช้ helper เช่น `can('download')` เพื่อซ่อนปุ่มดาวน์โหลด/คอมเมนต์สำหรับ public
สำคัญ: ต้องเช็คสิทธิ์ฝั่ง Backend เสมอ ห้ามพึ่งการซ่อนปุ่มฝั่ง Frontend เพียงอย่างเดียว (ป้องกันการเข้าถึงไฟล์ตรงผ่าน URL)
---
5. PDF Viewer (Canva Export หลายหน้า)
ใช้ PDF.js (Mozilla) ฝัง viewer เอง แทนการใช้ `<iframe>` ตรงไปยังไฟล์ เพื่อ:
ควบคุม UI (ธีม dark/cyber ให้ตรงกับเว็บ)
ปิดปุ่มดาวน์โหลด/พิมพ์ในตัว viewer สำหรับ role `public`
ทำ lazy-load ทีละหน้า ลด bandwidth สำหรับไฟล์ Canva ที่มักมีขนาดใหญ่
ไฟล์ PDF ไม่ควรอยู่ใต้ webroot ที่เข้าถึงตรงได้ (`/uploads/xxx.pdf` เปิดตรงได้) → ให้ serve ผ่าน controller เช่น `GET /file/view/{id}` ที่เช็ค RBAC ก่อนอ่านไฟล์แล้ว stream กลับ (`readfile()` พร้อม header ที่เหมาะสม) ป้องกันคนนอกเดา URL ไฟล์แล้วเข้าถึงตรง
---
6. UI/UX Theme
Tailwind CSS ผ่าน CDN (หรือ build เป็น production CSS ทีหลังเพื่อลดขนาด)
Dark/Cyber-Tech palette: พื้นหลัง slate-900/950, accent สี cyan-400 หรือ emerald-400, ใช้ subtle grid/circuit pattern เป็น background decoration
Lucide Icons (น้ำหนักเบากว่า FontAwesome, tree-shake ได้)
Dashboard: sidebar แบบ collapsible, การ์ดผลงานแบบ glassmorphism บางๆ (ไม่ overdo), แท็บแยกส่วนที่ 1 / ส่วนที่ 2 ชัดเจน
Responsive: mobile-first, breakpoint ตาม Tailwind default (sm/md/lg)
---
7. ลำดับการพัฒนา (Roadmap)
ตั้งค่าโครงสร้างโปรเจกต์ + composer + `.env` + git ignore
เขียน `schema.sql` + seed ข้อมูลทดสอบ
ทำระบบ Auth (Google OAuth) + RBAC middleware ให้ทำงานสมบูรณ์ก่อน
ทำ Dynamic Menu + CRUD ฝั่ง Admin
ทำหน้า Portfolio แสดงผล (ส่วนที่ 1 และ 2) + PDF Viewer
ทำระบบ Comment/Feedback สำหรับกรรมการ
ทำ Responsive/Polish UI + Audit log
ทดสอบสิทธิ์ทั้ง 3 role แบบ end-to-end ก่อนขึ้น production
---
8. ขั้นตอนถัดไปที่แนะนำ
หากต้องการ ผมสามารถเขียนโค้ดจริงตามแผนนี้ต่อได้ทีละส่วน เช่น:
`database/schema.sql` ฉบับเต็ม
`app/Core/Auth.php` + `AuthController.php` (ใช้ placeholder แทน secret จริง)
หน้า Dashboard + PDF Viewer component