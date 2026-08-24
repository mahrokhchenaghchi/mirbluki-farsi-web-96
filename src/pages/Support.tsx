import { UnspecifiedNotice } from "@/components/joma/UnspecifiedNotice";

export default function SupportPage() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-black">پشتیبانی</h1>
        <p className="mt-2 text-sm leading-8 text-muted-foreground">
          این بخش برای ارتباط با تیم جوما در نظر گرفته شده است.
        </p>
      </div>
      <UnspecifiedNotice>
        اطلاعات تماس واقعی مانند شماره تلفن یا شناسه پیام‌رسان توسط مالک پروژه ارائه نشده و حدس زده نشده است.
        وضعیت: UNSPECIFIED
      </UnspecifiedNotice>
    </div>
  );
}
