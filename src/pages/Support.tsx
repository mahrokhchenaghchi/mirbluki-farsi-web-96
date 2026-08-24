import { UnspecifiedNotice } from "@/components/joma/UnspecifiedNotice";

export default function SupportPage() {
  return (
    <div className="space-y-6">
      <h1 className="text-3xl font-black">پشتیبانی</h1>
      <p className="text-sm leading-8 text-muted-foreground">ساختار ارتباط آماده است تا بعداً روش‌های واقعی وارد شود.</p>
      <UnspecifiedNotice>
        شناسه بله، شماره پیامک و ایمیل پشتیبانی هنوز ارائه نشده و حدس زده نشده است.
      </UnspecifiedNotice>
    </div>
  );
}
