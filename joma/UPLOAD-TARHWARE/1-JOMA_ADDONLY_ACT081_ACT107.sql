-- =============================================================================
-- JOMA ADD-ONLY PATCH
-- ACT081 تا ACT107 | دسته: طرحواره درمانی | گروه: ACT_SCH
-- =============================================================================
-- این فایل ADD-ONLY است.
-- ندارد: DROP / DELETE / UPDATE / ALTER / TRUNCATE / REPLACE / CREATE TABLE
-- دست نمی‌زند به:
--   joma_users
--   joma_plans
--   joma_plan_activities
--   joma_performance_events
--   joma_mood_records
--   ACT001 تا ACT080
--
-- فقط اگر کد ACT081–ACT107 هنوز نباشد، ردیف جدید در joma_activities می‌گذارد.
-- این فعالیت‌ها وارد برنامهٔ در حال اجرا نمی‌شوند مگر کاربر بعداً خودش اضافه کند.
-- اجرای دوباره امن است (NOT EXISTS).
-- قبل از اجرا از phpMyAdmin یک Export بگیرید.
-- joma.sql کامل را Import نکنید.
-- =============================================================================

START TRANSACTION;

SET NAMES utf8mb4;

-- ۱) قالب سراسری (user_id خالی)
INSERT INTO joma_activities
(user_id, code, group_code, name, category, data_type, track_mode, unit, daily_target, weekly_target, monthly_target, weight, frequency, sticker, color, status, is_seed, created_at, updated_at)
SELECT NULL, s.code, s.group_code, s.name, s.category, s.data_type, s.track_mode, s.unit, s.daily_target, s.weekly_target, s.monthly_target, s.weight, s.frequency, s.sticker, s.color, s.status, 1, NOW(), NOW()
FROM (
SELECT 'ACT081' AS code,'ACT_SCH' AS group_code,'دفتر طرحواره امروز' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'📒' AS sticker,'#5B6ABF' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT082' AS code,'ACT_SCH' AS group_code,'فلش‌کارت بزرگسال سالم' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🃏' AS sticker,'#6A994E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT083' AS code,'ACT_SCH' AS group_code,'شواهد موافق و مخالف طرحواره' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,4 AS weight,'DAILY' AS frequency,'⚖️' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT084' AS code,'ACT_SCH' AS group_code,'تشخیص سبک مقابله' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'🧭' AS sticker,'#7C6CE7' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT085' AS code,'ACT_SCH' AS group_code,'مکث قبل از الگوی قدیمی' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'⏸️' AS sticker,'#5B8E7D' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT086' AS code,'ACT_SCH' AS group_code,'شکستن الگو؛ رفتار جدید' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🦋' AS sticker,'#D17A22' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT087' AS code,'ACT_SCH' AS group_code,'گفتگوی سه صندلی ذهنیت‌ها' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,25 AS weekly_target,100 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🪑' AS sticker,'#8C6BB1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT088' AS code,'ACT_SCH' AS group_code,'مرور هفتگی طرحواره و ذهنیت' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,40 AS weekly_target,160 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧾' AS sticker,'#6D75B8' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT089' AS code,'ACT_SCH' AS group_code,'مرور ماه نیازهای هیجانی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,0 AS weekly_target,60 AS monthly_target,4 AS weight,'MONTHLY' AS frequency,'📊' AS sticker,'#4F9FC4' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT090' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره رهاشدگی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🪢' AS sticker,'#C45B8A' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT091' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره بی‌اعتمادی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'👁️' AS sticker,'#5C4B6C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT092' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره محرومیت هیجانی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🤍' AS sticker,'#9A8C98' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT093' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره نقص و شرم' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🎭' AS sticker,'#8064A2' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT094' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره انزوای اجتماعی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🏝️' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT095' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره وابستگی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🛟' AS sticker,'#769FCD' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT096' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره آسیب‌پذیری' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'⚠️' AS sticker,'#BC6C25' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT097' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره گرفتارشدگی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧵' AS sticker,'#B97855' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT098' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره شکست' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'📉' AS sticker,'#6B7AA1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT099' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره استحقاق' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'👑' AS sticker,'#D9A441' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT100' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره خویشتن‌داری ناکافی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'⏳' AS sticker,'#E09F3E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT101' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره اطاعت' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🙇' AS sticker,'#739E9B' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT102' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره ایثار' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🕯️' AS sticker,'#C08497' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT103' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره تأییدطلبی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'👏' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT104' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره منفی‌گرایی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'⛈️' AS sticker,'#5964B4' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT105' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره بازداری هیجانی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🤐' AS sticker,'#547AA5' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT106' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره معیارهای سرسختانه' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'📏' AS sticker,'#7A9E7E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT107' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره تنبیه‌گری' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🔨' AS sticker,'#8D6E63' AS color,'ACTIVE' AS status
) AS s
WHERE NOT EXISTS (
  SELECT 1 FROM joma_activities a WHERE a.user_id IS NULL AND a.code = s.code
);

-- ۲) کپی برای کاربران فعلی که این کد را ندارند
INSERT INTO joma_activities
(user_id, code, group_code, name, category, data_type, track_mode, unit, daily_target, weekly_target, monthly_target, weight, frequency, sticker, color, status, is_seed, created_at, updated_at)
SELECT u.id, s.code, s.group_code, s.name, s.category, s.data_type, s.track_mode, s.unit, s.daily_target, s.weekly_target, s.monthly_target, s.weight, s.frequency, s.sticker, s.color, s.status, 1, NOW(), NOW()
FROM joma_users u
CROSS JOIN (
SELECT 'ACT081' AS code,'ACT_SCH' AS group_code,'دفتر طرحواره امروز' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,15 AS daily_target,105 AS weekly_target,450 AS monthly_target,5 AS weight,'DAILY' AS frequency,'📒' AS sticker,'#5B6ABF' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT082' AS code,'ACT_SCH' AS group_code,'فلش‌کارت بزرگسال سالم' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🃏' AS sticker,'#6A994E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT083' AS code,'ACT_SCH' AS group_code,'شواهد موافق و مخالف طرحواره' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,10 AS daily_target,70 AS weekly_target,300 AS monthly_target,4 AS weight,'DAILY' AS frequency,'⚖️' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT084' AS code,'ACT_SCH' AS group_code,'تشخیص سبک مقابله' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'🧭' AS sticker,'#7C6CE7' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT085' AS code,'ACT_SCH' AS group_code,'مکث قبل از الگوی قدیمی' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,4 AS weight,'DAILY' AS frequency,'⏸️' AS sticker,'#5B8E7D' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT086' AS code,'ACT_SCH' AS group_code,'شکستن الگو؛ رفتار جدید' AS name,'طرحواره درمانی' AS category,'BOOLEAN' AS data_type,'BOOLEAN' AS track_mode,'UNIT_NONE' AS unit,1 AS daily_target,7 AS weekly_target,30 AS monthly_target,5 AS weight,'DAILY' AS frequency,'🦋' AS sticker,'#D17A22' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT087' AS code,'ACT_SCH' AS group_code,'گفتگوی سه صندلی ذهنیت‌ها' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,25 AS weekly_target,100 AS monthly_target,5 AS weight,'WEEKLY' AS frequency,'🪑' AS sticker,'#8C6BB1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT088' AS code,'ACT_SCH' AS group_code,'مرور هفتگی طرحواره و ذهنیت' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,40 AS weekly_target,160 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧾' AS sticker,'#6D75B8' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT089' AS code,'ACT_SCH' AS group_code,'مرور ماه نیازهای هیجانی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,0 AS weekly_target,60 AS monthly_target,4 AS weight,'MONTHLY' AS frequency,'📊' AS sticker,'#4F9FC4' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT090' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره رهاشدگی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🪢' AS sticker,'#C45B8A' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT091' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره بی‌اعتمادی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'👁️' AS sticker,'#5C4B6C' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT092' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره محرومیت هیجانی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🤍' AS sticker,'#9A8C98' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT093' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره نقص و شرم' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🎭' AS sticker,'#8064A2' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT094' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره انزوای اجتماعی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🏝️' AS sticker,'#4F7CAC' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT095' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره وابستگی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🛟' AS sticker,'#769FCD' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT096' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره آسیب‌پذیری' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'⚠️' AS sticker,'#BC6C25' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT097' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره گرفتارشدگی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🧵' AS sticker,'#B97855' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT098' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره شکست' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'📉' AS sticker,'#6B7AA1' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT099' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره استحقاق' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'👑' AS sticker,'#D9A441' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT100' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره خویشتن‌داری ناکافی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'⏳' AS sticker,'#E09F3E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT101' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره اطاعت' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🙇' AS sticker,'#739E9B' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT102' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره ایثار' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🕯️' AS sticker,'#C08497' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT103' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره تأییدطلبی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'👏' AS sticker,'#D66BA0' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT104' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره منفی‌گرایی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'⛈️' AS sticker,'#5964B4' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT105' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره بازداری هیجانی' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🤐' AS sticker,'#547AA5' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT106' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره معیارهای سرسختانه' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'📏' AS sticker,'#7A9E7E' AS color,'ACTIVE' AS status
UNION ALL
SELECT 'ACT107' AS code,'ACT_SCH' AS group_code,'تمرین طرحواره تنبیه‌گری' AS name,'طرحواره درمانی' AS category,'DURATION' AS data_type,'DURATION' AS track_mode,'UNIT_MIN' AS unit,0 AS daily_target,20 AS weekly_target,80 AS monthly_target,4 AS weight,'WEEKLY' AS frequency,'🔨' AS sticker,'#8D6E63' AS color,'ACTIVE' AS status
) AS s
WHERE NOT EXISTS (
  SELECT 1 FROM joma_activities a WHERE a.user_id = u.id AND a.code = s.code
);

COMMIT;
