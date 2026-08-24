import { STICKERS } from "@/domain/catalog";
import { cn } from "@/lib/utils";

export function StickerPicker({
  value,
  onChange,
}: {
  value: string;
  onChange: (value: string) => void;
}) {
  return (
    <div className="grid max-h-40 grid-cols-10 gap-1 overflow-auto rounded-2xl bg-muted/60 p-2">
      {STICKERS.map((sticker) => (
        <button
          key={sticker}
          type="button"
          className={cn(
            "flex h-8 w-8 items-center justify-center rounded-xl text-lg",
            value === sticker ? "bg-white shadow" : "hover:bg-white/70",
          )}
          onClick={() => onChange(sticker)}
        >
          {sticker}
        </button>
      ))}
    </div>
  );
}
