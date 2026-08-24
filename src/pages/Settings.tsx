import { JOBS } from "@/domain/catalog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { PageHeader } from "@/components/joma/PageHeader";
import { useAuth } from "@/hooks/useAuth";
import { useState } from "react";

export default function SettingsPage() {
  const { user, updateProfile } = useAuth();
  const [saved, setSaved] = useState(false);
  if (!user) return null;

  return (
    <div className="space-y-6">
      <PageHeader
        title="تنظیمات"
        description="حساب، نمایش و اعلان‌ها از همین‌جا گسترش می‌یابند."
        crumbs={[{ label: "داشبورد", to: "/app" }, { label: "تنظیمات" }]}
      />
      <form
        className="joma-card space-y-4 p-5"
        onSubmit={(event) => {
          event.preventDefault();
          const data = new FormData(event.currentTarget);
          updateProfile({
            firstName: String(data.get("firstName") || user.firstName),
            lastName: String(data.get("lastName") || user.lastName),
            phone: String(data.get("phone") || user.phone),
            job: String(data.get("job") || user.job),
            preferences: {
              compactCards: data.get("compact") === "on",
              notificationsEnabled: data.get("notify") === "on",
            },
          });
          setSaved(true);
        }}
      >
        <h2 className="font-bold">تنظیمات حساب</h2>
        <div className="grid gap-3 sm:grid-cols-2">
          <div className="space-y-2"><Label>نام</Label><Input name="firstName" defaultValue={user.firstName} /></div>
          <div className="space-y-2"><Label>نام خانوادگی</Label><Input name="lastName" defaultValue={user.lastName} /></div>
        </div>
        <div className="space-y-2"><Label>موبایل</Label><Input name="phone" defaultValue={user.phone} dir="ltr" /></div>
        <div className="space-y-2">
          <Label>شغل</Label>
          <select name="job" defaultValue={user.job} className="h-10 w-full rounded-md border bg-background px-3 text-sm">
            {JOBS.map((job) => <option key={job}>{job}</option>)}
          </select>
        </div>
        <h2 className="pt-2 font-bold">نمایش</h2>
        <label className="flex items-center gap-2 text-sm"><input type="checkbox" name="compact" defaultChecked={user.preferences.compactCards} /> کارت‌های فشرده</label>
        <h2 className="pt-2 font-bold">اعلان‌ها</h2>
        <label className="flex items-center gap-2 text-sm"><input type="checkbox" name="notify" defaultChecked={user.preferences.notificationsEnabled} /> اعلان‌ها (فعلاً فقط ترجیح ذخیره می‌شود)</label>
        <Button type="submit">ذخیره</Button>
        {saved && <p className="text-sm text-emerald-700">ذخیره شد.</p>}
      </form>
    </div>
  );
}
