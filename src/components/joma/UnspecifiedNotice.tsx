import type { ReactNode } from "react";

export function UnspecifiedNotice({ children }: { children: ReactNode }) {
  return (
    <div className="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-7 text-amber-900">
      {children}
    </div>
  );
}
