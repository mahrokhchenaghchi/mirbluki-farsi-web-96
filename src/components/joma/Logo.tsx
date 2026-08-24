import { cn } from "@/lib/utils";

export function Logo({ className, compact = false }: { className?: string; compact?: boolean }) {
  return (
    <div className={cn("flex items-center gap-3", className)}>
      <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-hero text-white shadow-sm">
        <span className="text-sm font-black">جو</span>
      </div>
      {!compact && (
        <div className="text-right">
          <div className="text-base font-bold leading-none">جوما</div>
          <div className="mt-1 text-[11px] text-muted-foreground">برنامه. اجرا. فهم.</div>
        </div>
      )}
    </div>
  );
}
