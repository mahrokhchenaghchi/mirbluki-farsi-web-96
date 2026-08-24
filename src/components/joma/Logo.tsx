import { assetPath } from "@/lib/base";
import { cn } from "@/lib/utils";

export function Logo({
  className,
  compact = false,
  size = 48,
}: {
  className?: string;
  compact?: boolean;
  size?: number;
}) {
  return (
    <div className={cn("flex items-center gap-3", className)}>
      <img
        src={assetPath("logo.jpg")}
        alt="لوگوی جوما"
        width={size}
        height={size}
        className="rounded-full border-2 border-white object-cover shadow-md"
        style={{ width: size, height: size }}
      />
      {!compact && (
        <div className="text-right">
          <div className="text-lg font-black leading-none">جوما</div>
          <div className="mt-1 text-[11px] text-muted-foreground">برنامه. اجرا. فهم.</div>
        </div>
      )}
    </div>
  );
}
