export function isValidEmail(value: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
}

export function isValidIranMobile(value: string): boolean {
  return /^09\d{9}$/.test(value.trim());
}

export function isValidUsername(value: string): boolean {
  return /^[a-zA-Z][a-zA-Z0-9._]{2,19}$/.test(value.trim());
}

export function validateRegistration(input: {
  fullName: string;
  username: string;
  phone: string;
  email: string;
  job: string;
  password: string;
  confirmPassword: string;
}): string | null {
  if (!input.fullName.trim()) return "نام و نام خانوادگی را وارد کنید.";
  if (!isValidUsername(input.username)) {
    return "نام کاربری باید با حرف انگلیسی شروع شود و ۳ تا ۲۰ نویسه باشد.";
  }
  if (!isValidIranMobile(input.phone)) return "شماره موبایل باید مانند ۰۹۱۲۳۴۵۶۷۸۹ باشد.";
  if (!isValidEmail(input.email)) return "ایمیل معتبر نیست.";
  if (!input.job.trim()) return "شغل را وارد کنید.";
  if (input.password.length < 6) return "رمز عبور باید حداقل ۶ نویسه باشد.";
  if (input.password !== input.confirmPassword) return "رمز عبور و تکرار آن یکسان نیستند.";
  return null;
}

export function validateActivityInput(input: {
  name: string;
  weight: number;
  target: number;
}): string | null {
  if (!input.name.trim()) return "نام فعالیت را وارد کنید.";
  if (!Number.isFinite(input.weight) || input.weight < 0) return "وزن باید صفر یا بزرگ‌تر باشد.";
  if (!Number.isFinite(input.target) || input.target <= 0) return "هدف باید بزرگ‌تر از صفر باشد.";
  return null;
}

export function validatePerformanceValue(dataType: string, value: number): string | null {
  if (!Number.isFinite(value)) return "مقدار معتبر نیست.";
  if (dataType === "RATING" && (value < 1 || value > 5)) return "امتیاز باید بین ۱ و ۵ باشد.";
  if (dataType === "BOOLEAN" && value !== 0 && value !== 1) return "وضعیت انجام فقط می‌تواند انجام‌شده یا نشده باشد.";
  if ((dataType === "DURATION" || dataType === "NUMERIC") && value < 0) return "مقدار نمی‌تواند منفی باشد.";
  return null;
}
