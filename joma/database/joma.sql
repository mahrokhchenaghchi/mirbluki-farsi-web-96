SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS joma_roles (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_key VARCHAR(32) NOT NULL,
  access_level TINYINT UNSIGNED NOT NULL,
  title VARCHAR(64) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_role_key (role_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_permissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  perm_key VARCHAR(64) NOT NULL,
  title VARCHAR(128) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_perm_key (perm_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_role_permissions (
  role_key VARCHAR(32) NOT NULL,
  perm_key VARCHAR(64) NOT NULL,
  PRIMARY KEY (role_key, perm_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NOT NULL,
  username VARCHAR(32) NOT NULL,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  job VARCHAR(80) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role_key VARCHAR(32) NOT NULL DEFAULT 'member',
  access_level TINYINT UNSIGNED NOT NULL DEFAULT 1,
  mobile_verified TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_username (username),
  UNIQUE KEY uq_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_user_preferences (
  user_id INT UNSIGNED NOT NULL,
  compact_cards TINYINT(1) NOT NULL DEFAULT 0,
  notifications_enabled TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_otp_codes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  mobile VARCHAR(20) NOT NULL,
  code VARCHAR(10) NOT NULL,
  purpose VARCHAR(32) NOT NULL DEFAULT 'verify_mobile',
  expires_at DATETIME NOT NULL,
  used_at DATETIME DEFAULT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_otp_mobile (mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_activities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED DEFAULT NULL,
  code VARCHAR(16) NOT NULL,
  group_code VARCHAR(32) NOT NULL,
  name VARCHAR(190) NOT NULL,
  category VARCHAR(64) NOT NULL,
  data_type VARCHAR(16) NOT NULL,
  track_mode VARCHAR(16) NOT NULL,
  unit VARCHAR(24) NOT NULL,
  daily_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  weekly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  monthly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  weight INT NOT NULL DEFAULT 1,
  frequency VARCHAR(16) NOT NULL,
  sticker VARCHAR(16) NOT NULL,
  color VARCHAR(16) NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'ACTIVE',
  is_seed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_act_user (user_id),
  KEY idx_act_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_periods (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  year SMALLINT NOT NULL,
  month TINYINT NOT NULL,
  start_date CHAR(10) NOT NULL,
  end_date CHAR(10) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_period (user_id, period_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_plans (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  period_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  status VARCHAR(16) NOT NULL DEFAULT 'DRAFT',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  finalized_at DATETIME DEFAULT NULL,
  started_at DATETIME DEFAULT NULL,
  archived_at DATETIME DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_period_plan (user_id, period_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_plan_activities (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  activity_id INT UNSIGNED NOT NULL,
  activity_code VARCHAR(16) NOT NULL,
  name VARCHAR(190) NOT NULL,
  category VARCHAR(64) NOT NULL,
  frequency VARCHAR(16) NOT NULL,
  data_type VARCHAR(16) NOT NULL,
  unit VARCHAR(24) NOT NULL,
  daily_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  weekly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  monthly_target DECIMAL(10,2) NOT NULL DEFAULT 0,
  target_value DECIMAL(10,2) NOT NULL DEFAULT 0,
  weight INT NOT NULL DEFAULT 1,
  sticker VARCHAR(16) NOT NULL,
  color VARCHAR(16) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  snapshot_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_plan_activity (plan_id, activity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_performance_events (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  plan_activity_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  frequency VARCHAR(16) NOT NULL,
  data_type VARCHAR(16) NOT NULL,
  event_type VARCHAR(32) NOT NULL DEFAULT 'PERFORMANCE_REGISTERED',
  performance_date CHAR(10) NOT NULL,
  actual_value DECIMAL(10,2) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_evt_plan (plan_id),
  KEY idx_evt_pa_date (plan_activity_id, performance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_mood_records (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  jalali_date CHAR(10) NOT NULL,
  energy TINYINT NOT NULL,
  general_mood TINYINT NOT NULL,
  focus TINYINT NOT NULL,
  sleep_quality TINYINT NOT NULL,
  stress TINYINT NOT NULL,
  note TEXT,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_mood_user_day (user_id, jalali_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_report_projections (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  plan_id INT UNSIGNED NOT NULL,
  period_key CHAR(7) NOT NULL,
  payload LONGTEXT NOT NULL,
  source_event_count INT NOT NULL DEFAULT 0,
  generated_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_proj_plan (plan_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_settings (
  setting_key VARCHAR(64) NOT NULL,
  setting_value TEXT,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS joma_notifications (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  body TEXT,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_notif_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO joma_roles (role_key, access_level, title) VALUES
('member', 1, 'عضو'),
('plus', 2, 'پلاس'),
('coach', 3, 'مربی'),
('admin', 4, 'مدیر');

INSERT INTO joma_permissions (perm_key, title) VALUES
('VIEW_DASHBOARD', 'مشاهده داشبورد'),
('CREATE_PLAN', 'ساخت برنامه'),
('EDIT_PLAN', 'ویرایش برنامه'),
('RECORD_PERFORMANCE', 'ثبت عملکرد'),
('VIEW_REPORT', 'مشاهده گزارش'),
('VIEW_HISTORY', 'مشاهده تاریخچه'),
('MANAGE_ACTIVITY_LIBRARY', 'مدیریت کتابخانه'),
('MANAGE_USERS', 'مدیریت کاربران'),
('ADMIN_ACCESS', 'دسترسی مدیر');

INSERT INTO joma_role_permissions (role_key, perm_key) VALUES
('member','VIEW_DASHBOARD'),('member','CREATE_PLAN'),('member','EDIT_PLAN'),
('member','RECORD_PERFORMANCE'),('member','VIEW_REPORT'),('member','VIEW_HISTORY'),
('member','MANAGE_ACTIVITY_LIBRARY'),
('plus','VIEW_DASHBOARD'),('plus','CREATE_PLAN'),('plus','EDIT_PLAN'),
('plus','RECORD_PERFORMANCE'),('plus','VIEW_REPORT'),('plus','VIEW_HISTORY'),
('plus','MANAGE_ACTIVITY_LIBRARY'),
('coach','VIEW_DASHBOARD'),('coach','CREATE_PLAN'),('coach','EDIT_PLAN'),
('coach','RECORD_PERFORMANCE'),('coach','VIEW_REPORT'),('coach','VIEW_HISTORY'),
('coach','MANAGE_ACTIVITY_LIBRARY'),
('admin','VIEW_DASHBOARD'),('admin','CREATE_PLAN'),('admin','EDIT_PLAN'),
('admin','RECORD_PERFORMANCE'),('admin','VIEW_REPORT'),('admin','VIEW_HISTORY'),
('admin','MANAGE_ACTIVITY_LIBRARY'),('admin','MANAGE_USERS'),('admin','ADMIN_ACCESS');

INSERT INTO joma_settings (setting_key, setting_value) VALUES
('support_contact', ''),
('product_name', 'جوما');

INSERT INTO joma_activities
(user_id, code, group_code, name, category, data_type, track_mode, unit, daily_target, weekly_target, monthly_target, weight, frequency, sticker, color, status, is_seed, created_at, updated_at) VALUES
(NULL,'ACT001','ACT_HEA','ورزش','سلامت جسم','DURATION','DURATION','UNIT_MIN',30,150,600,5,'DAILY','🏃','#E85D75','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT002','ACT_HEA','نوشیدن آب','سلامت جسم','NUMERIC','NUMERIC','UNIT_GLASS',6,42,180,4,'DAILY','💧','#4F9FC4','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT003','ACT_HEA','پیاده‌روی','سلامت جسم','DURATION','DURATION','UNIT_MIN',20,100,400,4,'DAILY','🚶','#5AA469','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT004','ACT_HEA','مصرف میوه','سلامت جسم','NUMERIC','NUMERIC','UNIT_TIMES',2,14,60,3,'DAILY','🍎','#D95D5D','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT005','ACT_SLE','خواب کافی','خواب و استراحت','DURATION','DURATION','UNIT_HOUR',7,49,210,5,'DAILY','🌙','#5964B4','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT006','ACT_SLE','ساعت خواب منظم','خواب و استراحت','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','⏰','#6D75B8','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT007','ACT_MEN','مدیتیشن','سلامت روان','DURATION','DURATION','UNIT_MIN',10,50,200,4,'DAILY','🧘','#7C6CE7','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT008','ACT_MEN','تمرین تنفس','سلامت روان','DURATION','DURATION','UNIT_MIN',5,35,150,4,'DAILY','🌬️','#769FCD','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT009','ACT_MEN','ثبت حال روزانه','سلامت روان','RATING','RATING','UNIT_SCORE',1,7,30,5,'DAILY','📓','#E9A23B','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT010','ACT_FOC','مطالعه بدون حواس‌پرتی','تمرکز و ذهن','DURATION','DURATION','UNIT_MIN',30,150,600,5,'DAILY','🎯','#4F7CAC','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT011','ACT_FOC','زمان بدون موبایل','تمرکز و ذهن','DURATION','DURATION','UNIT_MIN',30,150,600,4,'DAILY','📵','#5B8E7D','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT012','ACT_FOC','نوشتن برنامه روزانه','تمرکز و ذهن','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','📝','#7C8C6C','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT013','ACT_GRO','مطالعه','یادگیری','DURATION','DURATION','UNIT_MIN',30,150,600,4,'DAILY','📚','#D99A2B','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT014','ACT_GRO','یادگیری مهارت جدید','رشد فردی','DURATION','DURATION','UNIT_MIN',20,100,400,4,'DAILY','🛠️','#5AA469','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT015','ACT_GRO','نوشتن اهداف','رشد فردی','BOOLEAN','BOOLEAN','UNIT_NONE',1,3,4,4,'WEEKLY','⭐','#8C6BB1','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT016','ACT_REL','گفت‌وگوی باکیفیت با همسر','روابط','DURATION','DURATION','UNIT_MIN',15,105,450,5,'DAILY','💑','#D66BA0','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT017','ACT_REL','قدردانی از همسر','روابط','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','🙏','#C7D4A0','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT018','ACT_REL','قرار دونفره','روابط','DURATION','DURATION','UNIT_HOUR',0,2,8,5,'WEEKLY','☕','#C06C84','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT019','ACT_FAM','تماس با خانواده','خانواده','DURATION','DURATION','UNIT_MIN',0,30,120,3,'WEEKLY','📞','#C7D4A0','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT020','ACT_FAM','دیدار با خانواده','خانواده','DURATION','DURATION','UNIT_HOUR',0,2,8,4,'WEEKLY','🏠','#B97855','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT021','ACT_MIN','نوشتن سه نکته مثبت','ذهن‌آگاهی','NUMERIC','NUMERIC','UNIT_COUNT',3,21,90,4,'DAILY','✨','#6D8B74','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT022','ACT_MIN','تمرین شکرگزاری','ذهن‌آگاهی','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','🌸','#9A8C98','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT023','ACT_SEL','زمان شخصی','مراقبت از خود','DURATION','DURATION','UNIT_MIN',20,140,600,4,'DAILY','🛁','#C08497','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT024','ACT_SEL','انجام یک فعالیت لذت‌بخش','مراقبت از خود','BOOLEAN','BOOLEAN','UNIT_NONE',1,3,12,3,'WEEKLY','🎨','#B565A7','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT025','ACT_PRO','اولویت‌بندی کارهای روز','بهره‌وری','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','✅','#E09F3E','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT026','ACT_PRO','انجام مهم‌ترین کار روز','بهره‌وری','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,5,'DAILY','🔥','#D9A441','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT027','ACT_PRO','مرور هفتگی','بهره‌وری','DURATION','DURATION','UNIT_MIN',0,30,120,4,'WEEKLY','📅','#4F7CAC','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT028','ACT_LEA','خواندن کتاب','یادگیری','DURATION','DURATION','UNIT_MIN',20,140,600,4,'DAILY','📖','#B07D3C','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT029','ACT_LEA','تکمیل یک فصل کتاب','یادگیری','NUMERIC','NUMERIC','UNIT_COUNT',0,1,4,3,'WEEKLY','📗','#A86F52','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT030','ACT_MON','خواندن یک کتاب کامل','یادگیری','NUMERIC','NUMERIC','UNIT_COUNT',0,0,1,5,'MONTHLY','📘','#D99A2B','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT031','ACT_MON','یک روز کامل بدون شبکه اجتماعی','سبک زندگی','BOOLEAN','BOOLEAN','UNIT_NONE',0,0,1,4,'MONTHLY','📵','#5B8E7D','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT032','ACT_MON','مرور ماه و ارزیابی شخصی','رشد فردی','BOOLEAN','BOOLEAN','UNIT_NONE',0,0,1,5,'MONTHLY','🪞','#7C6CE7','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT033','ACT_MON','تعیین اهداف ماه آینده','رشد فردی','BOOLEAN','BOOLEAN','UNIT_NONE',0,0,1,5,'MONTHLY','🧭','#4F7CAC','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT034','ACT_HEA','غذای سالم','سلامت جسم','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','🥗','#6A994E','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT035','ACT_HEA','کاهش مصرف قند','سلامت جسم','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','🍬','#BC6C25','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT036','ACT_HEA','کشش بدن','سلامت جسم','DURATION','DURATION','UNIT_MIN',10,50,200,3,'DAILY','🤸','#76A5AF','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT037','ACT_MEN','نوشتن افکار و احساسات','سلامت روان','DURATION','DURATION','UNIT_MIN',10,50,200,4,'DAILY','💭','#8064A2','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT038','ACT_MEN','فاصله گرفتن آگاهانه هنگام تنش','سلامت روان','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,5,'DAILY','🌿','#739E9B','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT039','ACT_REL','گفت‌وگوی بدون موبایل','روابط','DURATION','DURATION','UNIT_MIN',15,105,450,4,'DAILY','💬','#C45B8A','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT040','ACT_FOC','یک کار در هر لحظه','تمرکز و ذهن','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,4,'DAILY','🎧','#547AA5','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT041','ACT_SLE','خاموش‌کردن صفحه‌نمایش قبل از خواب','خواب و استراحت','DURATION','DURATION','UNIT_MIN',30,210,900,4,'DAILY','🖥️','#5964B4','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT042','ACT_SEL','مراقبت شخصی','مراقبت از خود','BOOLEAN','BOOLEAN','UNIT_NONE',1,7,30,3,'DAILY','🧴','#C08497','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT043','ACT_PRO','جمع‌بندی پایان روز','بهره‌وری','DURATION','DURATION','UNIT_MIN',5,35,150,3,'DAILY','🌇','#7A9E7E','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT044','ACT_GRO','بررسی یک رفتار و یادگیری از آن','رشد فردی','DURATION','DURATION','UNIT_MIN',10,70,300,4,'DAILY','🔍','#6B7AA1','ACTIVE',1,NOW(),NOW()),
(NULL,'ACT045','ACT_MON','انجام یک تجربه جدید','رشد فردی','BOOLEAN','BOOLEAN','UNIT_NONE',0,0,1,3,'MONTHLY','🌈','#D17A22','ACTIVE',1,NOW(),NOW());

SET FOREIGN_KEY_CHECKS = 1;
