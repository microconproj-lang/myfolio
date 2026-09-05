# MyFolio

เว็บ portfolio สำหรับการประเมิน วPA ของข้าราชการครู ตามแผนใน `plan.md` ใช้ PHP 8.x, PDO, MySQL และ MVC-lite โดยให้ `public/` เป็น webroot เท่านั้น

## ติดตั้ง

1. ติดตั้ง PHP 8.1+, MySQL 8+ และ Composer
2. รัน `composer install`
3. คัดลอก `.env.example` เป็น `.env` แล้วเติมค่า database และ Google OAuth ที่หมุน secret ใหม่แล้ว
4. ตั้ง web server ให้ DocumentRoot ชี้ไปที่ `myfolio/public`
5. รัน `mysql -uUSER -p < database/schema.sql` และ `mysql -uUSER -p < database/seed.sql`

ถ้าต้องการสร้าง database/user และ import schema ในครั้งเดียว ให้รัน `sudo bash deploy/setup-database.sh` แล้วกรอกรหัสผ่านของ user `myfolio` จากนั้นต้องใส่รหัสเดียวกันใน `DB_PASSWORD` ของ `.env` ห้ามใช้ค่า `change-me`

## phpMyAdmin

phpMyAdmin ต้องถูกเปิดผ่าน web server แยกจาก MyFolio โดยต้องมี PHP-FPM หรือ Apache PHP module ก่อน ปัจจุบันโปรเจกต์ใช้ `public/` เป็น webroot และไม่ควรเปิด `storage/` หรือ `.env` ผ่านเว็บโดยตรง

ตัวอย่าง location สำหรับ Nginx อยู่ที่ `deploy/nginx-phpmyadmin.conf.example` โดยต้องติดตั้ง `php8.3-fpm`, include location ใน TLS server block, รัน `nginx -t` และ reload Nginx ด้วยสิทธิ์ root ก่อนเข้า `https://portal.pccpl.ac.th/phpmyadmin/`

สำหรับ 404 ที่ `https://portal.pccpl.ac.th/myfolio/` ให้ include `deploy/nginx-myfolio.conf.example` ใน TLS server block เดียวกัน แล้วรัน:

```bash
sudo apt install -y php8.3-fpm
sudo systemctl enable --now php8.3-fpm
sudo nginx -t
sudo systemctl reload nginx
```

หรือให้สคริปต์ทำการสำรองไฟล์, แทรก include, ตรวจ config และ reload ให้อัตโนมัติด้วย `sudo bash deploy/enable-nginx-myfolio.sh`

การอัปโหลดหลักฐาน: คัดลอก `deploy/myfolio-upload.ini.example` ไปที่ `/etc/php/8.3/fpm/conf.d/99-myfolio-upload.ini` แล้วรัน `sudo systemctl restart php8.3-fpm` โดยระบบรองรับ PDF/JPG/PNG/WebP สูงสุด 10 ไฟล์ ไฟล์ละ 20 MB

ก่อนอัปโหลดครั้งแรก ให้ตั้งสิทธิ์ private storage ด้วย `sudo bash deploy/setup-storage.sh` เพื่อให้ PHP-FPM (`www-data`) เขียนไฟล์ได้ โดยไม่เปิด directory นี้ผ่านเว็บ

Google redirect URI ต้องตรงกับ `GOOGLE_REDIRECT_URI` แบบ exact และไม่ควรเก็บ `.env` ใน git

สร้าง OAuth Web application ใน Google Cloud Console แล้วใส่ค่าจริงใน `.env`:

```dotenv
GOOGLE_CLIENT_ID=ตัวเลขและรหัสจาก Google Cloud
GOOGLE_CLIENT_SECRET=รหัสลับจาก Google Cloud
GOOGLE_REDIRECT_URI=https://portal.pccpl.ac.th/myfolio/auth/google-callback.php
```

ใน Google Cloud ต้องเพิ่ม URL เดียวกันนี้ไว้ใน Authorized redirect URIs และเปิดใช้งาน Google People API หรือ OAuth consent configuration ตามนโยบายของ Workspace ก่อนทดสอบล็อกอิน

ระบบจะรับเฉพาะบัญชี Google ที่ยืนยันอีเมลแล้วและอยู่ในโดเมน `ALLOWED_EMAIL_DOMAIN` เท่านั้น

## ตรวจสอบก่อน deploy

รัน `find app public -type f -name '*.php' -print0 | xargs -0 -n1 php -l` เพื่อตรวจ syntax