export function appBasename(): string {
  const base = import.meta.env.BASE_URL || "/";
  if (base === "/") return "";
  return base.replace(/\/$/, "");
}

export function assetPath(path: string): string {
  const cleaned = path.startsWith("/") ? path.slice(1) : path;
  const base = import.meta.env.BASE_URL || "/";
  return `${base}${cleaned}`;
}
