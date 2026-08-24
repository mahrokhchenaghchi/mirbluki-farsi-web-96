import { COLORS } from "@/domain/catalog";
import { cn } from "@/lib/utils";

export function ColorPicker({
  value,
  onChange,
}: {
  value: string;
  onChange: (value: string) => void;
}) {
  return (
    <div className="flex flex-wrap gap-2">
      {COLORS.map((color) => (
        <button
          key={color.id}
          type="button"
          title={color.label}
          className={cn(
            "h-8 w-8 rounded-full border-2",
            value === color.value ? "border-foreground scale-110" : "border-white",
          )}
          style={{ background: color.value }}
          onClick={() => onChange(color.value)}
        />
      ))}
    </div>
  );
}
