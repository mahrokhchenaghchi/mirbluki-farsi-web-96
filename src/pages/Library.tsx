import { useEffect, useState } from "react";
import type { ActivityDefinition } from "@/domain/types";
import { activityLabel, listLibraryActivities } from "@/services/activityService";
import { LoadingState } from "@/components/joma/LoadingState";
import { UnspecifiedNotice } from "@/components/joma/UnspecifiedNotice";
import { toUserMessage } from "@/lib/errors";

export default function LibraryPage() {
  const [items, setItems] = useState<ActivityDefinition[]>([]);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    listLibraryActivities()
      .then(setItems)
      .catch((err) => setError(toUserMessage(err)))
      .finally(() => setLoading(false));
  }, []);

  if (loading) return <LoadingState />;

  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-black">کتابخانه فعالیت</h1>
        <p className="mt-2 text-sm text-muted-foreground">شناسه‌های اولیه ACT001 تا ACT045.</p>
      </div>
      <UnspecifiedNotice>
        فایل library.pdf در این محیط در دسترس نبود. عنوان دقیق فعالیت‌ها حدس زده نشده است.
        شناسه‌ها Seed شده‌اند تا بعداً بدون تغییر معماری تکمیل شوند.
      </UnspecifiedNotice>
      {error && <p className="text-sm text-destructive">{error}</p>}
      <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        {items.map((item) => (
          <div key={item.id} className="rounded-2xl border bg-card p-4">
            <div className="text-xs text-muted-foreground" dir="ltr">
              {item.code}
            </div>
            <div className="mt-2 font-medium">{activityLabel(item)}</div>
          </div>
        ))}
      </div>
    </div>
  );
}
