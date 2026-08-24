import type { ReactNode } from "react";

export function EmptyState({
  title,
  description,
  action,
}: {
  title: string;
  description: string;
  action?: ReactNode;
}) {
  return (
    <div className="rounded-2xl border border-dashed bg-card px-6 py-12 text-center">
      <h3 className="text-lg font-semibold">{title}</h3>
      <p className="mx-auto mt-2 max-w-md text-sm leading-8 text-muted-foreground">{description}</p>
      {action && <div className="mt-6">{action}</div>}
    </div>
  );
}
