import JomaCalendarService from "@/calendar/JomaCalendarService";
import { ACCESS_LEVEL_LABEL } from "@/domain/permissions";
import { PageHeader } from "@/components/joma/PageHeader";
import { useAuth } from "@/hooks/useAuth";

export default function ProfilePage() {
  const { user } = useAuth();
  if (!user) return null;
  const rows = [
    ["نام", user.firstName],
    ["نام خانوادگی", user.lastName],
    ["نام کاربری", user.username],
    ["موبایل", user.phone],
    ["ایمیل", user.email],
    ["شغل", user.job],
    ["سطح دسترسی", ACCESS_LEVEL_LABEL[user.accessLevel]],
    ["نقش", user.role],
    ["تاریخ عضویت", user.createdAt.slice(0, 10)],
  ];
  return (
    <div className="space-y-6">
      <PageHeader title="پروفایل من" crumbs={[{ label: "داشبورد", to: "/app" }, { label: "پروفایل" }]} />
      <div className="joma-card divide-y">
        {rows.map(([label, value]) => (
          <div key={label} className="flex items-center justify-between px-5 py-4 text-sm">
            <span className="text-muted-foreground">{label}</span>
            <span className="font-medium">{value || "—"}</span>
          </div>
        ))}
      </div>
      <p className="text-xs text-muted-foreground">امروز {JomaCalendarService.formatJalaliDisplay(JomaCalendarService.todayJalaliString())}</p>
    </div>
  );
}
