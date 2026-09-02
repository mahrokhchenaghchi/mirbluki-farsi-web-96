-- =============================================================================
-- JOMA ADD-ONLY PATCH
-- ACT046 تا ACT080 | دسته: زوج درمانی | گروه: ACT_CPL
-- =============================================================================
-- این فایل ADD-ONLY است.
-- ندارد: DROP / DELETE / UPDATE / ALTER / TRUNCATE / REPLACE / CREATE TABLE
-- دست نمی‌زند به:
--   joma_users
--   joma_plans
--   joma_plan_activities     (Snapshot برنامه‌های فعلی)
--   joma_performance_events  (ثبت عملکرد روزانه/هفتگی/ماهانه)
--   joma_mood_records
--   ACT001 تا ACT045
--
-- فقط اگر کد ACT046–ACT080 هنوز نباشد، ردیف جدید در joma_activities می‌گذارد.
-- این فعالیت‌ها وارد برنامهٔ در حال اجرا نمی‌شوند مگر کاربر بعداً خودش اضافه کند.
-- اجرای دوباره امن است (NOT EXISTS).
-- قبل از اجرا از phpMyAdmin یک Export بگیرید.
-- joma.sql کامل را Import نکنید.
-- =============================================================================

START TRANSACTION;

SET NAMES utf8mb4;

-- ۱) قالب سراسری (user_id خالی) — برای نصب‌های بعدی / یکپارچگی Seed
INSERT INTO joma_activities
(user_id, code, group_code, name, category, data_type, track_mode, unit, daily_target, weekly_target, monthly_target, weight, frequency, sticker, color, status, is_seed, created_at, updated_at)
SELECT NULL, s.code, s.group_code, s.name, s.category, s.data_type, s.track_mode, s.unit, s.daily_target, s.weekly_target, s.monthly_target, s.weight, s.frequency, s.sticker, s.color, s.status, 1, NOW(), NOW()
FROM (
SELECT 'ACT046' AS code,'ACT_CPL' AS group_code,'بازسازی صمیمیت عاطفی' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💗' AS sticker,'#C45B8A' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT047' AS code,'ACT_CPL' AS group_code,'تمرین گفت‌وگوی بدون دعوا' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🗣️' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT048' AS code,'ACT_CPL' AS group_code,'هنر شنیدن همسر' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,5 AS weight,'DAILY' AS frequency,'👂' AS sticker,'#9A6B8C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT049' AS code,'ACT_CPL' AS group_code,'تمرین همدلی با شریک عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'🤲' AS sticker,'#C08497' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT050' AS code,'ACT_CPL' AS group_code,'بیان نیازها بدون سرزنش' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🗨️' AS sticker,'#B565A7' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT051' AS code,'ACT_CPL' AS group_code,'مدیریت خشم و جلوگیری از انفجار رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🧯' AS sticker,'#C0392B' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT052' AS code,'ACT_CPL' AS group_code,'حل تعارض و اختلاف‌نظر' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,30 AS weekly_target,120 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'⚖️' AS sticker,'#6B7AA1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT053' AS code,'ACT_CPL' AS group_code,'ترمیم رابطه بعد از دعوا' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🩹' AS sticker,'#E07A5F' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT054' AS code,'ACT_CPL' AS group_code,'بازسازی اعتماد آسیب‌دیده' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🔐' AS sticker,'#5C4B6C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT055' AS code,'ACT_CPL' AS group_code,'مرزبندی سالم در رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'🧱' AS sticker,'#8D6E63' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT056' AS code,'ACT_CPL' AS group_code,'شناخت الگوهای تکراری رابطه' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,15 AS weekly_target,60 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🔁' AS sticker,'#7C6CE7' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT057' AS code,'ACT_CPL' AS group_code,'شناخت طرحواره‌های مؤثر بر رابطه' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧩' AS sticker,'#6D75B8' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT058' AS code,'ACT_CPL' AS group_code,'تقویت محبت و توجه روزانه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💝' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT059' AS code,'ACT_CPL' AS group_code,'بازگرداندن عشق و هیجان به رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'💘' AS sticker,'#E85D75' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT060' AS code,'ACT_CPL' AS group_code,'تمرین دوستی دوباره با همسر' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,30 AS weekly_target,120 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🤝' AS sticker,'#C06C84' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT061' AS code,'ACT_CPL' AS group_code,'هم‌راستا کردن اهداف زوجین' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🏁' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT062' AS code,'ACT_CPL' AS group_code,'مدیریت اختلافات مالی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'💰' AS sticker,'#D9A441' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT063' AS code,'ACT_CPL' AS group_code,'مدیریت دخالت خانواده‌ها' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🛡️' AS sticker,'#B97855' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT064' AS code,'ACT_CPL' AS group_code,'مدیریت موبایل و فضای مجازی در رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'📱' AS sticker,'#5B8E7D' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT065' AS code,'ACT_CPL' AS group_code,'افزایش صمیمیت و نزدیکی زوجین' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💞' AS sticker,'#C45B8A' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT066' AS code,'ACT_CPL' AS group_code,'مقابله با حسادت و ناامنی عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💚' AS sticker,'#6A994E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT067' AS code,'ACT_CPL' AS group_code,'مدیریت افکار منفی درباره همسر' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'☁️' AS sticker,'#6B7AA1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT068' AS code,'ACT_CPL' AS group_code,'تقسیم مسئولیت‌های زندگی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧺' AS sticker,'#A86F52' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT069' AS code,'ACT_CPL' AS group_code,'کنار آمدن با سردی عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🌨️' AS sticker,'#769FCD' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT070' AS code,'ACT_CPL' AS group_code,'ترمیم رابطه پس از خیانت' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,30 AS weekly_target,120 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🕊️' AS sticker,'#9A8C98' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT071' AS code,'ACT_CPL' AS group_code,'تمرین امنیت عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🏡' AS sticker,'#7A9E7E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT072' AS code,'ACT_CPL' AS group_code,'شناخت نقش خودم در مشکلات رابطه' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,4 AS weight,'DAILY' AS frequency,'👤' AS sticker,'#8064A2' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT073' AS code,'ACT_CPL' AS group_code,'شکستن چرخه دعواهای تکراری' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🌀' AS sticker,'#BC6C25' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT074' AS code,'ACT_CPL' AS group_code,'تقویت نقاط قوت رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,3 AS weight,'WEEKLY' AS frequency,'💪' AS sticker,'#5AA469' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT075' AS code,'ACT_CPL' AS group_code,'بازسازی تعهد و آینده مشترک' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,0 AS weekly_target,1 AS monthly_target,5 AS weight,'MONTHLY' AS frequency,'💍' AS sticker,'#8C6BB1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT076' AS code,'ACT_CPL' AS group_code,'تمرین قدردانی از همسر' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'💐' AS sticker,'#C7D4A0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT077' AS code,'ACT_CPL' AS group_code,'دفترچه گفت‌وگوی زوجین' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,4 AS weight,'DAILY' AS frequency,'📒' AS sticker,'#B07D3C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT078' AS code,'ACT_CPL' AS group_code,'قرار هفتگی زوجین' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_HOUR' AS unit,0 AS daily_target,2 AS weekly_target,8 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🍷' AS sticker,'#C06C84' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT079' AS code,'ACT_CPL' AS group_code,'تمرین روزانه اتصال عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🔗' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT080' AS code,'ACT_CPL' AS group_code,'ارزیابی ماهانه سلامت رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,0 AS weekly_target,1 AS monthly_target,5 AS weight,'MONTHLY' AS frequency,'📋' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
) AS s
WHERE NOT EXISTS (
  SELECT 1 FROM joma_activities a WHERE a.user_id IS NULL AND a.code = s.code
);

-- ۲) کپی برای کاربران فعلی که این کد را ندارند
-- بدون این بخش، کتابخانهٔ حساب‌های موجود عوض نمی‌شود (کتابخانه per-user است).
INSERT INTO joma_activities
(user_id, code, group_code, name, category, data_type, track_mode, unit, daily_target, weekly_target, monthly_target, weight, frequency, sticker, color, status, is_seed, created_at, updated_at)
SELECT u.id, s.code, s.group_code, s.name, s.category, s.data_type, s.track_mode, s.unit, s.daily_target, s.weekly_target, s.monthly_target, s.weight, s.frequency, s.sticker, s.color, s.status, 1, NOW(), NOW()
FROM joma_users u
CROSS JOIN (
SELECT 'ACT046' AS code,'ACT_CPL' AS group_code,'بازسازی صمیمیت عاطفی' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💗' AS sticker,'#C45B8A' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT047' AS code,'ACT_CPL' AS group_code,'تمرین گفت‌وگوی بدون دعوا' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🗣️' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT048' AS code,'ACT_CPL' AS group_code,'هنر شنیدن همسر' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,5 AS weight,'DAILY' AS frequency,'👂' AS sticker,'#9A6B8C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT049' AS code,'ACT_CPL' AS group_code,'تمرین همدلی با شریک عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'🤲' AS sticker,'#C08497' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT050' AS code,'ACT_CPL' AS group_code,'بیان نیازها بدون سرزنش' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🗨️' AS sticker,'#B565A7' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT051' AS code,'ACT_CPL' AS group_code,'مدیریت خشم و جلوگیری از انفجار رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🧯' AS sticker,'#C0392B' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT052' AS code,'ACT_CPL' AS group_code,'حل تعارض و اختلاف‌نظر' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,30 AS weekly_target,120 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'⚖️' AS sticker,'#6B7AA1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT053' AS code,'ACT_CPL' AS group_code,'ترمیم رابطه بعد از دعوا' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🩹' AS sticker,'#E07A5F' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT054' AS code,'ACT_CPL' AS group_code,'بازسازی اعتماد آسیب‌دیده' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🔐' AS sticker,'#5C4B6C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT055' AS code,'ACT_CPL' AS group_code,'مرزبندی سالم در رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'🧱' AS sticker,'#8D6E63' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT056' AS code,'ACT_CPL' AS group_code,'شناخت الگوهای تکراری رابطه' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,15 AS weekly_target,60 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🔁' AS sticker,'#7C6CE7' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT057' AS code,'ACT_CPL' AS group_code,'شناخت طرحواره‌های مؤثر بر رابطه' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧩' AS sticker,'#6D75B8' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT058' AS code,'ACT_CPL' AS group_code,'تقویت محبت و توجه روزانه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💝' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT059' AS code,'ACT_CPL' AS group_code,'بازگرداندن عشق و هیجان به رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'💘' AS sticker,'#E85D75' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT060' AS code,'ACT_CPL' AS group_code,'تمرین دوستی دوباره با همسر' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,30 AS weekly_target,120 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🤝' AS sticker,'#C06C84' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT061' AS code,'ACT_CPL' AS group_code,'هم‌راستا کردن اهداف زوجین' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🏁' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT062' AS code,'ACT_CPL' AS group_code,'مدیریت اختلافات مالی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'💰' AS sticker,'#D9A441' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT063' AS code,'ACT_CPL' AS group_code,'مدیریت دخالت خانواده‌ها' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🛡️' AS sticker,'#B97855' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT064' AS code,'ACT_CPL' AS group_code,'مدیریت موبایل و فضای مجازی در رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'📱' AS sticker,'#5B8E7D' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT065' AS code,'ACT_CPL' AS group_code,'افزایش صمیمیت و نزدیکی زوجین' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💞' AS sticker,'#C45B8A' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT066' AS code,'ACT_CPL' AS group_code,'مقابله با حسادت و ناامنی عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'💚' AS sticker,'#6A994E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT067' AS code,'ACT_CPL' AS group_code,'مدیریت افکار منفی درباره همسر' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'☁️' AS sticker,'#6B7AA1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT068' AS code,'ACT_CPL' AS group_code,'تقسیم مسئولیت‌های زندگی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧺' AS sticker,'#A86F52' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT069' AS code,'ACT_CPL' AS group_code,'کنار آمدن با سردی عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🌨️' AS sticker,'#769FCD' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT070' AS code,'ACT_CPL' AS group_code,'ترمیم رابطه پس از خیانت' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,30 AS weekly_target,120 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🕊️' AS sticker,'#9A8C98' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT071' AS code,'ACT_CPL' AS group_code,'تمرین امنیت عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🏡' AS sticker,'#7A9E7E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT072' AS code,'ACT_CPL' AS group_code,'شناخت نقش خودم در مشکلات رابطه' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,4 AS weight,'DAILY' AS frequency,'👤' AS sticker,'#8064A2' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT073' AS code,'ACT_CPL' AS group_code,'شکستن چرخه دعواهای تکراری' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🌀' AS sticker,'#BC6C25' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT074' AS code,'ACT_CPL' AS group_code,'تقویت نقاط قوت رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,1 AS weekly_target,4 AS monthly_target,3 AS weight,'WEEKLY' AS frequency,'💪' AS sticker,'#5AA469' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT075' AS code,'ACT_CPL' AS group_code,'بازسازی تعهد و آینده مشترک' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,0 AS weekly_target,1 AS monthly_target,5 AS weight,'MONTHLY' AS frequency,'💍' AS sticker,'#8C6BB1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT076' AS code,'ACT_CPL' AS group_code,'تمرین قدردانی از همسر' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'💐' AS sticker,'#C7D4A0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT077' AS code,'ACT_CPL' AS group_code,'دفترچه گفت‌وگوی زوجین' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,4 AS weight,'DAILY' AS frequency,'📒' AS sticker,'#B07D3C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT078' AS code,'ACT_CPL' AS group_code,'قرار هفتگی زوجین' AS name,'زوج درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_HOUR' AS unit,0 AS daily_target,2 AS weekly_target,8 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🍷' AS sticker,'#C06C84' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT079' AS code,'ACT_CPL' AS group_code,'تمرین روزانه اتصال عاطفی' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🔗' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT080' AS code,'ACT_CPL' AS group_code,'ارزیابی ماهانه سلامت رابطه' AS name,'زوج درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,0 AS daily_target,0 AS weekly_target,1 AS monthly_target,5 AS weight,'MONTHLY' AS frequency,'📋' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
) AS s
WHERE NOT EXISTS (
  SELECT 1 FROM joma_activities a WHERE a.user_id = u.id AND a.code = s.code
);

COMMIT;
