export class JomaError extends Error {
  code: string;

  constructor(code: string, message: string) {
    super(message);
    this.code = code;
    this.name = "JomaError";
  }
}

export function toUserMessage(error: unknown): string {
  const raw = error instanceof Error ? error.message : String(error ?? "");
  const lower = raw.toLowerCase();

  if (lower.includes("failed to fetch") || lower.includes("network") || lower.includes("internet")) {
    return "اتصال اینترنت برقرار نشد. لطفاً دوباره تلاش کنید.";
  }
  if (lower.includes("invalid login") || lower.includes("invalid credentials")) {
    return "ایمیل یا رمز عبور نادرست است.";
  }
  if (lower.includes("already registered") || lower.includes("user already")) {
    return "این ایمیل قبلاً ثبت شده است. وارد شوید.";
  }
  if (lower.includes("email not confirmed")) {
    return "ایمیل هنوز تأیید نشده است. صندوق ورودی را بررسی کنید.";
  }
  if (lower.includes("jwt") || lower.includes("session") || lower.includes("not authenticated")) {
    return "نشست شما منقضی شده است. دوباره وارد شوید.";
  }
  if (lower.includes("daily duplicate")) {
    return "برای این فعالیت روزانه، در این تاریخ قبلاً عملکرد ثبت شده است.";
  }
  if (lower.includes("plan is not running")) {
    return "فقط در دوره در حال اجرا می‌توان عملکرد ثبت کرد.";
  }
  if (lower.includes("date outside period")) {
    return "تاریخ انتخاب‌شده داخل این دوره نیست.";
  }
  if (lower.includes("schema cache") || lower.includes("does not exist") || lower.includes("joma_")) {
    return "پایگاه داده جوما هنوز راه‌اندازی نشده است. مهاجرت SQL را در Supabase اجرا کنید.";
  }
  if (raw.startsWith("JOMA:")) {
    return toUserMessage(raw.replace(/^JOMA:\s*/, ""));
  }
  if (error instanceof JomaError) {
    return error.message;
  }
  return "خطایی رخ داد. لطفاً دوباره تلاش کنید.";
}
