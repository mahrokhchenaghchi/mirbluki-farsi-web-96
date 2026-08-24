import { isLocalMode } from "@/lib/mode";

export function LocalModeBanner() {
  if (!isLocalMode()) return null;
  return (
    <div className="bg-amber-50 px-4 py-2 text-center text-xs leading-6 text-amber-900">
      حالت آزمایشی محلی — داده در همین مرورگر ذخیره می‌شود و به پایگاه تولید وصل نیست.
    </div>
  );
}
