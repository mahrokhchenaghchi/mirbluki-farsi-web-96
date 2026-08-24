export function LoadingState({ label = "در حال بارگذاری..." }: { label?: string }) {
  return (
    <div className="flex min-h-[40vh] flex-col items-center justify-center gap-4">
      <div className="h-12 w-12 animate-spin rounded-full border-2 border-primary/20 border-t-primary" />
      <p className="text-sm text-muted-foreground">{label}</p>
    </div>
  );
}
