import { useEffect, useMemo, useState } from "react";
import {
  CATEGORIES,
  COLORS,
  DATA_TYPES,
  DATA_TYPE_LABEL,
  FREQUENCIES,
  FREQUENCY_LABEL,
  STICKERS,
  targetFieldFor,
} from "@/domain/catalog";
import type { ActivityCategory, ActivityDefinition, DataType, Frequency } from "@/domain/types";
import { validateActivityInput } from "@/domain/validation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { ColorPicker } from "@/components/joma/ColorPicker";
import { LoadingState } from "@/components/joma/LoadingState";
import { PageHeader } from "@/components/joma/PageHeader";
import { StickerPicker } from "@/components/joma/StickerPicker";
import { toUserMessage } from "@/lib/errors";
import {
  createLibraryActivity,
  deleteLibraryActivity,
  listLibraryActivities,
  updateLibraryActivity,
} from "@/services/activityService";

const emptyForm = {
  name: "",
  category: "سلامت جسم" as ActivityCategory,
  frequency: "DAILY" as Frequency,
  dataType: "BOOLEAN" as DataType,
  dailyTarget: 1,
  weeklyTarget: 1,
  monthlyTarget: 1,
  weight: 1,
  sticker: STICKERS[0],
  color: COLORS[0].value,
  status: "ACTIVE" as const,
};

export default function LibraryPage() {
  const [items, setItems] = useState<ActivityDefinition[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [query, setQuery] = useState("");
  const [category, setCategory] = useState("همه");
  const [frequency, setFrequency] = useState("همه");
  const [editing, setEditing] = useState<ActivityDefinition | null>(null);
  const [creating, setCreating] = useState(false);
  const [form, setForm] = useState(emptyForm);

  const reload = async () => setItems(await listLibraryActivities());

  useEffect(() => {
    reload().catch((err) => setError(toUserMessage(err))).finally(() => setLoading(false));
  }, []);

  const filtered = useMemo(
    () =>
      items.filter((item) => {
        const matchesQuery = !query || item.name.includes(query) || item.code.toLowerCase().includes(query.toLowerCase());
        const matchesCategory = category === "همه" || item.category === category;
        const matchesFrequency = frequency === "همه" || item.frequency === frequency;
        return matchesQuery && matchesCategory && matchesFrequency;
      }),
    [items, query, category, frequency],
  );

  const openCreate = () => {
    setEditing(null);
    setForm(emptyForm);
    setCreating(true);
  };

  const openEdit = (item: ActivityDefinition) => {
    setCreating(false);
    setEditing(item);
    setForm({
      name: item.name,
      category: item.category,
      frequency: item.frequency,
      dataType: item.dataType,
      dailyTarget: item.dailyTarget,
      weeklyTarget: item.weeklyTarget,
      monthlyTarget: item.monthlyTarget,
      weight: item.weight,
      sticker: item.sticker,
      color: item.color,
      status: item.status || "ACTIVE",
    });
  };

  const save = async (event: React.FormEvent) => {
    event.preventDefault();
    const target = form[targetFieldFor(form.frequency)];
    const invalid = validateActivityInput({ name: form.name, weight: form.weight, target });
    if (invalid) {
      setError(invalid);
      return;
    }
    setError(null);
    try {
      if (editing) await updateLibraryActivity(editing.id, form);
      else await createLibraryActivity(form);
      setCreating(false);
      setEditing(null);
      await reload();
    } catch (err) {
      setError(toUserMessage(err));
    }
  };

  if (loading) return <LoadingState />;

  return (
    <div className="space-y-6">
      <PageHeader
        title="کتابخانه فعالیت‌ها"
        description="۴۵ فعالیت اولیه جوما؛ قابل جستجو، ویرایش و غیرفعال‌سازی."
        crumbs={[{ label: "داشبورد", to: "/app" }, { label: "کتابخانه" }]}
        action={<Button onClick={openCreate}>+ فعالیت جدید</Button>}
      />

      <div className="grid gap-3 md:grid-cols-3">
        <Input placeholder="جستجو نام یا ACT..." value={query} onChange={(e) => setQuery(e.target.value)} />
        <select className="h-10 rounded-md border bg-background px-3 text-sm" value={category} onChange={(e) => setCategory(e.target.value)}>
          <option>همه</option>
          {CATEGORIES.map((item) => <option key={item}>{item}</option>)}
        </select>
        <select className="h-10 rounded-md border bg-background px-3 text-sm" value={frequency} onChange={(e) => setFrequency(e.target.value)}>
          <option>همه</option>
          {FREQUENCIES.map((item) => <option key={item} value={item}>{FREQUENCY_LABEL[item]}</option>)}
        </select>
      </div>

      {error && <p className="text-sm text-destructive">{error}</p>}

      {(creating || editing) && (
        <form className="joma-card space-y-4 p-5" onSubmit={save}>
          <h2 className="font-bold">{editing ? "ویرایش فعالیت" : "فعالیت جدید"}</h2>
          <div className="space-y-2">
            <Label>نام فعالیت</Label>
            <Input value={form.name} onChange={(e) => setForm({ ...form, name: e.target.value })} />
          </div>
          <div className="grid gap-3 md:grid-cols-2">
            <Select label="دسته‌بندی" value={form.category} onChange={(value) => setForm({ ...form, category: value as ActivityCategory })} options={CATEGORIES.map((item) => ({ value: item, label: item }))} />
            <Select label="تناوب" value={form.frequency} onChange={(value) => setForm({ ...form, frequency: value as Frequency })} options={FREQUENCIES.map((item) => ({ value: item, label: FREQUENCY_LABEL[item] }))} />
            <Select label="نوع داده" value={form.dataType} onChange={(value) => setForm({ ...form, dataType: value as DataType })} options={DATA_TYPES.map((item) => ({ value: item, label: DATA_TYPE_LABEL[item] }))} />
            <div className="space-y-2">
              <Label>وزن</Label>
              <Input type="number" min={0} dir="ltr" value={form.weight} onChange={(e) => setForm({ ...form, weight: Number(e.target.value) })} />
            </div>
          </div>
          {form.frequency === "DAILY" && <NumberField label="هدف روزانه" value={form.dailyTarget} onChange={(dailyTarget) => setForm({ ...form, dailyTarget })} />}
          {form.frequency === "WEEKLY" && <NumberField label="هدف هفتگی" value={form.weeklyTarget} onChange={(weeklyTarget) => setForm({ ...form, weeklyTarget })} />}
          {form.frequency === "MONTHLY" && <NumberField label="هدف ماهانه" value={form.monthlyTarget} onChange={(monthlyTarget) => setForm({ ...form, monthlyTarget })} />}
          <div className="space-y-2">
            <Label>استیکر</Label>
            <StickerPicker value={form.sticker} onChange={(sticker) => setForm({ ...form, sticker })} />
          </div>
          <div className="space-y-2">
            <Label>رنگ</Label>
            <ColorPicker value={form.color} onChange={(color) => setForm({ ...form, color })} />
          </div>
          <div className="flex gap-2">
            <Button type="submit">ذخیره</Button>
            <Button type="button" variant="outline" onClick={() => { setCreating(false); setEditing(null); }}>انصراف</Button>
          </div>
        </form>
      )}

      <div className="grid gap-4 md:grid-cols-2">
        {filtered.map((item) => (
          <article key={item.id} className="joma-card overflow-hidden">
            <div className="flex items-center gap-3 p-4" style={{ background: `${item.color}66` }}>
              <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-3xl">{item.sticker}</div>
              <div>
                <div className="font-black">{item.name}</div>
                <div className="text-xs text-muted-foreground" dir="ltr">{item.code}</div>
              </div>
            </div>
            <div className="space-y-1 p-4 text-sm text-muted-foreground">
              <div>{item.category}</div>
              <div>{FREQUENCY_LABEL[item.frequency]} · {DATA_TYPE_LABEL[item.dataType]}</div>
              <div>وزن {item.weight}</div>
            </div>
            <div className="flex gap-2 px-4 pb-4">
              <Button size="sm" variant="outline" onClick={() => openEdit(item)}>ویرایش</Button>
              <Button size="sm" variant="outline" onClick={async () => { await updateLibraryActivity(item.id, { status: item.status === "INACTIVE" ? "ACTIVE" : "INACTIVE" }); await reload(); }}>{item.status === "INACTIVE" ? "فعال‌سازی" : "غیرفعال"}</Button>
              <Button size="sm" variant="ghost" onClick={async () => { if (!window.confirm("حذف این فعالیت از کتابخانه؟ تصویر دوره‌های قبلی باقی می‌ماند.")) return; await deleteLibraryActivity(item.id); await reload(); }}>حذف</Button>
            </div>
          </article>
        ))}
      </div>
    </div>
  );
}

function Select({
  label,
  value,
  onChange,
  options,
}: {
  label: string;
  value: string;
  onChange: (value: string) => void;
  options: Array<{ value: string; label: string }>;
}) {
  return (
    <div className="space-y-2">
      <Label>{label}</Label>
      <select className="h-10 w-full rounded-md border bg-background px-3 text-sm" value={value} onChange={(e) => onChange(e.target.value)}>
        {options.map((option) => <option key={option.value} value={option.value}>{option.label}</option>)}
      </select>
    </div>
  );
}

function NumberField({ label, value, onChange }: { label: string; value: number; onChange: (value: number) => void }) {
  return (
    <div className="space-y-2">
      <Label>{label}</Label>
      <Input type="number" min={0.0001} step="any" dir="ltr" value={value} onChange={(e) => onChange(Number(e.target.value))} />
    </div>
  );
}
